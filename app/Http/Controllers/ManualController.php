<?php

namespace App\Http\Controllers;

use App\Models\Manual;
use App\Models\ManualChunk;
use App\Models\AtaChapter;
use App\Models\AtaSubchapter;
use App\Services\ManualProcessingService;
use App\Services\ManualSourceService;
use App\Support\SchemaPayload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ManualController extends Controller
{
    public function sourceFiles(ManualSourceService $sourceService)
    {
        return response()->json([
            'success' => true,
            'source_path' => $sourceService->sourcePath(),
            'data' => $sourceService->listFiles(),
        ]);
    }

    public function importFromSource(Request $request, ManualSourceService $sourceService)
    {
        $data = $request->validate([
            'filename' => 'required|string|max:255',
            'aeronave_id' => 'sometimes|nullable|exists:aeronaves,id',
            'nombre' => 'sometimes|nullable|string|max:255',
            'tipo_manual' => 'sometimes|nullable|string|max:100',
            'fabricante' => 'sometimes|nullable|string|max:255',
            'aeronave_modelo' => 'sometimes|nullable|string|max:255',
            'revision' => 'sometimes|nullable|string|max:100',
            'idioma' => 'sometimes|nullable|string|max:10',
            'estado' => 'sometimes|nullable|string|max:50',
            'vigente_desde' => 'sometimes|nullable|date',
            'vigente_hasta' => 'sometimes|nullable|date',
            'descripcion' => 'sometimes|nullable|string',
            'auto_process' => 'sometimes|boolean',
            'raw_text' => 'sometimes|nullable|string',
        ]);

        try {
            $file = $sourceService->resolveFile($data['filename']);
        } catch (RuntimeException $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 422);
        }

        $manual = Manual::create([
            'aeronave_id' => $data['aeronave_id'] ?? null,
            'nombre' => $data['nombre'] ?? pathinfo($file['nombre_archivo'], PATHINFO_FILENAME),
            'tipo_manual' => $data['tipo_manual'] ?? 'PDF',
            'fabricante' => $data['fabricante'] ?? null,
            'aeronave_modelo' => $data['aeronave_modelo'] ?? null,
            'revision' => $data['revision'] ?? null,
            'idioma' => $data['idioma'] ?? 'es',
            'estado' => $data['estado'] ?? 'vigente',
            'archivo_path' => $file['ruta_absoluta'],
            'vigente_desde' => $data['vigente_desde'] ?? null,
            'vigente_hasta' => $data['vigente_hasta'] ?? null,
            'descripcion' => $data['descripcion'] ?? null,
        ]);

        $processed = null;

        if (($data['auto_process'] ?? false) === true) {
            $processed = app(ManualProcessingService::class)->process($manual, [
                'replace_chunks' => true,
                'raw_text' => $data['raw_text'] ?? null,
            ]);
        }

        $this->bustCache();

        return response()->json([
            'success' => true,
            'message' => 'Manual importado desde carpeta fuente.',
            'data' => $manual->fresh()->loadCount('chunks')->load('aeronave:id,matricula,modelo'),
            'processing' => $processed,
        ], 201);
    }

    public function processSource(Request $request, Manual $manuale, ManualProcessingService $processingService)
    {
        $data = $request->validate([
            'replace_chunks' => 'sometimes|boolean',
            'raw_text' => 'sometimes|nullable|string',
        ]);

        $result = $processingService->process($manuale, [
            'replace_chunks' => $data['replace_chunks'] ?? true,
            'raw_text' => $data['raw_text'] ?? null,
        ]);

        $this->bustCache();

        return response()->json([
            'success' => true,
            'message' => 'Manual procesado correctamente.',
            'data' => $manuale->fresh()->loadCount('chunks'),
            'processing' => $result,
        ]);
    }

    public function index(Request $request)
    {
        $payload = Cache::remember($this->cacheKey('index', $request->query()), now()->addMinutes(5), function () use ($request) {
            $manuales = Manual::query()
                ->select([
                    'id',
                    'aeronave_id',
                    'nombre',
                    'tipo_manual',
                    'fabricante',
                    'aeronave_modelo',
                    'revision',
                    'idioma',
                    'estado',
                    'archivo_path',
                    'vigente_desde',
                    'vigente_hasta',
                    'descripcion',
                ])
                ->with('aeronave:id,matricula,modelo')
                ->withCount('chunks')
                ->when($request->filled('aeronave_id'), fn ($q) => $q->where('aeronave_id', $request->integer('aeronave_id')))
                ->when($request->filled('aeronave_modelo'), fn ($q) => $this->applyIndexedPrefixSearch($q, 'aeronave_modelo', $request->string('aeronave_modelo')))
                ->when($request->filled('tipo_manual'), fn ($q) => $q->where('tipo_manual', $request->string('tipo_manual')))
                ->when($request->filled('revision'), fn ($q) => $q->where('revision', $request->string('revision')))
                ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->string('estado')))
                ->orderBy('aeronave_modelo')
                ->orderBy('tipo_manual')
                ->orderBy('nombre')
                ->get();

            return [
                'success' => true,
                'data' => $manuales->toArray(),
            ];
        });

        return response()->json($payload);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'aeronave_id' => 'sometimes|nullable|exists:aeronaves,id',
            'nombre' => 'required|string|max:255',
            'tipo_manual' => 'required|string|max:100',
            'fabricante' => 'sometimes|nullable|string|max:255',
            'aeronave_modelo' => 'sometimes|nullable|string|max:255',
            'revision' => 'sometimes|nullable|string|max:100',
            'idioma' => 'sometimes|nullable|string|max:10',
            'estado' => 'sometimes|nullable|string|max:50',
            'archivo_path' => 'sometimes|nullable|string|max:255',
            'vigente_desde' => 'sometimes|nullable|date',
            'vigente_hasta' => 'sometimes|nullable|date',
            'descripcion' => 'sometimes|nullable|string',
            'chunks' => 'sometimes|array',
            'chunks.*.ata_chapter_id' => 'sometimes|nullable|exists:ata_chapters,id',
            'chunks.*.ata_subchapter_id' => 'sometimes|nullable|exists:ata_subchapters,id',
            'chunks.*.codigo_seccion' => 'sometimes|nullable|string|max:255',
            'chunks.*.titulo' => 'sometimes|nullable|string|max:255',
            'chunks.*.tipo_contenido' => 'sometimes|nullable|string|max:100',
            'chunks.*.pagina_inicio' => 'sometimes|nullable|integer|min:1',
            'chunks.*.pagina_fin' => 'sometimes|nullable|integer|min:1',
            'chunks.*.orden' => 'sometimes|nullable|integer|min:0',
            'chunks.*.resumen' => 'sometimes|nullable|string',
            'chunks.*.keywords' => 'sometimes|nullable|array',
            'chunks.*.keywords.*' => 'string|max:100',
            'chunks.*.embedding' => 'sometimes|nullable|array',
            'chunks.*.texto' => 'required_with:chunks|string',
            'chunks.*.referencias' => 'sometimes|nullable|array',
            'chunks.*.referencias.*.tipo' => 'sometimes|nullable|string|max:100',
            'chunks.*.referencias.*.valor' => 'required_with:chunks.*.referencias|string|max:255',
        ]);

        $manual = DB::transaction(function () use ($data) {
            $manual = Manual::create(SchemaPayload::forModel(new Manual(), $data));
            $this->syncChunks($manual, $data['chunks'] ?? []);

            return $manual->loadCount('chunks')->load('aeronave:id,matricula,modelo');
        });

        $this->bustCache();

        return response()->json([
            'success' => true,
            'message' => 'Manual creado correctamente.',
            'data' => $manual,
        ], 201);
    }

    public function show(Manual $manuale)
    {
        $payload = Cache::remember($this->cacheKey('show', ['id' => $manuale->id]), now()->addMinutes(5), function () use ($manuale) {
            $manuale->load([
                'aeronave:id,matricula,modelo',
                'chunks' => fn ($query) => $query
                    ->with([
                        'ataChapter:id,codigo,descripcion',
                        'ataSubchapter:id,ata_chapter_id,codigo,descripcion',
                        'referencias:id,manual_chunk_id,tipo,valor',
                    ])
                    ->orderBy('orden')
                    ->orderBy('id'),
            ]);

            return [
                'success' => true,
                'data' => $manuale->toArray(),
            ];
        });

        return response()->json($payload);
    }

    public function update(Request $request, Manual $manuale)
    {
        $data = $request->validate([
            'aeronave_id' => 'sometimes|nullable|exists:aeronaves,id',
            'nombre' => 'sometimes|string|max:255',
            'tipo_manual' => 'sometimes|string|max:100',
            'fabricante' => 'sometimes|nullable|string|max:255',
            'aeronave_modelo' => 'sometimes|nullable|string|max:255',
            'revision' => 'sometimes|nullable|string|max:100',
            'idioma' => 'sometimes|nullable|string|max:10',
            'estado' => 'sometimes|nullable|string|max:50',
            'archivo_path' => 'sometimes|nullable|string|max:255',
            'vigente_desde' => 'sometimes|nullable|date',
            'vigente_hasta' => 'sometimes|nullable|date',
            'descripcion' => 'sometimes|nullable|string',
            'replace_chunks' => 'sometimes|boolean',
            'chunks' => 'sometimes|array',
            'chunks.*.ata_chapter_id' => 'sometimes|nullable|exists:ata_chapters,id',
            'chunks.*.ata_subchapter_id' => 'sometimes|nullable|exists:ata_subchapters,id',
            'chunks.*.codigo_seccion' => 'sometimes|nullable|string|max:255',
            'chunks.*.titulo' => 'sometimes|nullable|string|max:255',
            'chunks.*.tipo_contenido' => 'sometimes|nullable|string|max:100',
            'chunks.*.pagina_inicio' => 'sometimes|nullable|integer|min:1',
            'chunks.*.pagina_fin' => 'sometimes|nullable|integer|min:1',
            'chunks.*.orden' => 'sometimes|nullable|integer|min:0',
            'chunks.*.resumen' => 'sometimes|nullable|string',
            'chunks.*.keywords' => 'sometimes|nullable|array',
            'chunks.*.keywords.*' => 'string|max:100',
            'chunks.*.embedding' => 'sometimes|nullable|array',
            'chunks.*.texto' => 'required_with:chunks|string',
            'chunks.*.referencias' => 'sometimes|nullable|array',
            'chunks.*.referencias.*.tipo' => 'sometimes|nullable|string|max:100',
            'chunks.*.referencias.*.valor' => 'required_with:chunks.*.referencias|string|max:255',
        ]);

        DB::transaction(function () use ($manuale, $data) {
            $manuale->update(SchemaPayload::forModel($manuale, $data));

            if (($data['replace_chunks'] ?? false) === true) {
                $manuale->chunks()->delete();
            }

            if (array_key_exists('chunks', $data)) {
                $this->syncChunks($manuale, $data['chunks']);
            }
        });

        $this->bustCache();

        return response()->json([
            'success' => true,
            'message' => 'Manual actualizado correctamente.',
            'data' => $manuale->fresh()->loadCount('chunks')->load('aeronave:id,matricula,modelo'),
        ]);
    }

    public function destroy(Manual $manuale)
    {
        $manuale->delete();
        $this->bustCache();

        return response()->json([
            'success' => true,
            'message' => 'Manual eliminado correctamente.',
        ]);
    }

    private function syncChunks(Manual $manual, array $chunks): void
    {
        [$defaultAtaChapterId, $defaultAtaSubchapterId] = $this->defaultAtaIds();

        foreach ($chunks as $chunkData) {
            $chunkData['ata_chapter_id'] = $chunkData['ata_chapter_id'] ?? $defaultAtaChapterId;
            $chunkData['ata_subchapter_id'] = $chunkData['ata_subchapter_id'] ?? $defaultAtaSubchapterId;

            $chunk = $manual->chunks()->create(SchemaPayload::forModel(new ManualChunk(), $chunkData));

            foreach ($chunkData['referencias'] ?? [] as $referencia) {
                $chunk->referencias()->create([
                    'tipo' => $referencia['tipo'] ?? 'keyword',
                    'valor' => $referencia['valor'],
                ]);
            }
        }
    }

    private function defaultAtaIds(): array
    {
        $chapterCode = (string) config('manuals.default_ata_chapter_code', '100');
        $subchapterCode = (string) config('manuals.default_ata_subchapter_code', '100-10');

        $chapterId = AtaChapter::query()
            ->where('codigo', $chapterCode)
            ->value('id');

        $subchapterId = AtaSubchapter::query()
            ->where('codigo', $subchapterCode)
            ->value('id');

        return [$chapterId, $subchapterId];
    }

    private function cacheKey(string $action, array $params = []): string
    {
        ksort($params);

        return 'manuales:' . Cache::get('manuales_cache_version', 1) . ':' . $action . ':' . md5(json_encode($params));
    }

    private function bustCache(): void
    {
        Cache::forever('manuales_cache_version', (int) Cache::get('manuales_cache_version', 1) + 1);
    }
}
