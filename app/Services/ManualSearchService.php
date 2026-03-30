<?php

namespace App\Services;

use App\Models\AtaChapter;
use App\Models\Manual;
use App\Models\ManualChunk;
use Illuminate\Support\Str;
use Throwable;

class ManualSearchService
{
    private const STOP_WORDS = [
        'de', 'la', 'el', 'los', 'las', 'un', 'una', 'unos', 'unas', 'y', 'o', 'con', 'sin',
        'por', 'para', 'del', 'al', 'en', 'se', 'que', 'es', 'son', 'lo', 'le', 'izquierdo',
        'derecho', 'left', 'right', 'during', 'from', 'this', 'that',
    ];

    public function search(array $filters, ?string $query = null, int $limit = 10): array
    {
        $keywords = $this->extractKeywords($query);
        $ataCodes = $this->inferAtaCandidates($query);
        $ataIds = AtaChapter::query()
            ->whereIn('codigo', array_map(fn (int $code) => str_pad((string) $code, 2, '0', STR_PAD_LEFT), $ataCodes))
            ->pluck('id')
            ->all();

        $chunks = $this->performSearch($filters, $keywords, $ataIds, $limit);
        $autoProcessed = [];

        if ($chunks->isEmpty()) {
            $autoProcessed = $this->ensureChunksAvailable($filters);

            if (! empty($autoProcessed)) {
                $chunks = $this->performSearch($filters, $keywords, $ataIds, $limit);
            }
        }

        if ($chunks->isEmpty() && ! empty($filters['aeronave_modelo'])) {
            $relaxedFilters = $filters;
            unset($relaxedFilters['aeronave_modelo']);

            $chunks = $this->performSearch($relaxedFilters, $keywords, $ataIds, $limit);
        }

        return [
            'keywords' => $keywords,
            'ata_candidates' => $ataCodes,
            'auto_processed_manual_ids' => $autoProcessed,
            'chunks' => $chunks->map(fn (array $item) => $this->serializeChunk($item['chunk'], $item['score']))->all(),
        ];
    }

    private function performSearch(array $filters, array $keywords, array $ataIds, int $limit)
    {
        return $this->baseQuery($filters, $ataIds)
            ->with([
                'manual:id,aeronave_id,nombre,tipo_manual,aeronave_modelo,revision,estado',
                'ataChapter:id,codigo,descripcion',
                'ataSubchapter:id,ata_chapter_id,codigo,descripcion',
                'referencias:id,manual_chunk_id,tipo,valor',
            ])
            ->get()
            ->map(fn (ManualChunk $chunk) => [
                'chunk' => $chunk,
                'score' => $this->scoreChunk($chunk, $keywords, $ataIds),
            ])
            ->filter(fn (array $item) => $item['score'] > 0 || empty($keywords))
            ->sortByDesc('score')
            ->take($limit)
            ->values();
    }

    public function contextualizeDiscrepancy(string $descripcion, array $filters = [], int $limit = 5): array
    {
        $search = $this->search($filters, $descripcion, $limit);
        $chunks = collect($search['chunks']);

        return [
            'discrepancia' => $descripcion,
            'keywords' => $search['keywords'],
            'ata_candidates' => $search['ata_candidates'],
            'manuales_relacionados' => $chunks->pluck('manual.nombre')->unique()->values()->all(),
            'procedimientos' => $chunks
                ->whereIn('tipo_contenido', ['procedimiento', 'task', 'inspeccion'])
                ->pluck('titulo')
                ->filter()
                ->unique()
                ->values()
                ->all(),
            'warnings' => $chunks
                ->where('tipo_contenido', 'warning')
                ->pluck('texto')
                ->filter()
                ->take(3)
                ->values()
                ->all(),
            'chunks' => $search['chunks'],
        ];
    }

    private function baseQuery(array $filters, array $ataHints)
    {
        return ManualChunk::query()
            ->whereHas('manual', function ($manualQuery) use ($filters) {
                $manualQuery
                    ->when(! empty($filters['manual_id']), fn ($q) => $q->where('id', $filters['manual_id']))
                    ->when(! empty($filters['aeronave_id']), fn ($q) => $q->where('aeronave_id', $filters['aeronave_id']))
                    ->when(! empty($filters['aeronave_modelo']), function ($q) use ($filters) {
                        $modelo = trim((string) $filters['aeronave_modelo']);

                        $q->where(function ($manual) use ($modelo) {
                            $manual
                                ->where('aeronave_modelo', $modelo)
                                ->orWhere('aeronave_modelo', 'like', $modelo . '%')
                                ->orWhere('aeronave_modelo', 'like', '%' . $modelo . '%');
                        });
                    })
                    ->when(! empty($filters['revision']), fn ($q) => $q->where('revision', $filters['revision']))
                    ->when(! empty($filters['tipo_manual']), fn ($q) => $q->where('tipo_manual', $filters['tipo_manual']))
                    ->when(! empty($filters['estado']), fn ($q) => $q->where('estado', $filters['estado']), fn ($q) => $q->where('estado', 'vigente'));
            })
            ->when(! empty($filters['ata_subchapter_id']), fn ($q) => $q->where('ata_subchapter_id', $filters['ata_subchapter_id']))
            ->when(! empty($filters['ata_chapter_id']), fn ($q) => $q->where('ata_chapter_id', $filters['ata_chapter_id']))
            ->when(
                empty($filters['ata_chapter_id']) && empty($filters['ata_subchapter_id']) && ! empty($ataHints),
                fn ($q) => $q->whereIn('ata_chapter_id', $ataHints)
            );
    }

    private function ensureChunksAvailable(array $filters): array
    {
        $candidates = Manual::query()
            ->select(['id', 'aeronave_id', 'nombre', 'tipo_manual', 'aeronave_modelo', 'revision', 'estado', 'archivo_path'])
            ->withCount('chunks')
            ->when(! empty($filters['manual_id']), fn ($q) => $q->where('id', $filters['manual_id']))
            ->when(! empty($filters['aeronave_id']), fn ($q) => $q->where('aeronave_id', $filters['aeronave_id']))
            ->when(! empty($filters['aeronave_modelo']), function ($q) use ($filters) {
                $modelo = trim((string) $filters['aeronave_modelo']);

                $q->where(function ($manual) use ($modelo) {
                    $manual
                        ->where('aeronave_modelo', $modelo)
                        ->orWhere('aeronave_modelo', 'like', $modelo . '%')
                        ->orWhere('aeronave_modelo', 'like', '%' . $modelo . '%')
                        ->orWhereNull('aeronave_modelo');
                });
            })
            ->when(! empty($filters['revision']), fn ($q) => $q->where('revision', $filters['revision']))
            ->when(! empty($filters['estado']), fn ($q) => $q->where('estado', $filters['estado']), fn ($q) => $q->where('estado', 'vigente'))
            ->whereNotNull('archivo_path')
            ->orderByDesc('chunks_count')
            ->orderBy('id')
            ->limit(3)
            ->get();

        $processed = [];

        foreach ($candidates as $manual) {
            if ($manual->chunks_count > 0) {
                continue;
            }

            try {
                app(ManualProcessingService::class)->process($manual, [
                    'replace_chunks' => true,
                ]);
                $processed[] = $manual->id;
            } catch (Throwable) {
                // Keep search resilient even if a source PDF cannot be processed.
            }
        }

        return $processed;
    }

    private function scoreChunk(ManualChunk $chunk, array $keywords, array $ataHints): int
    {
        $score = 0;
        $haystack = Str::lower(Str::ascii(implode(' ', [
            $chunk->titulo,
            $chunk->resumen,
            $chunk->texto,
            implode(' ', $chunk->keywords ?? []),
            $chunk->referencias->pluck('valor')->implode(' '),
            $chunk->manual?->nombre,
            $chunk->manual?->tipo_manual,
        ])));

        foreach ($keywords as $keyword) {
            if (str_contains($haystack, $keyword)) {
                $score += 10;
            }
        }

        if ($chunk->ata_chapter_id && in_array($chunk->ata_chapter_id, $ataHints, true)) {
            $score += 25;
        }

        if ($chunk->tipo_contenido === 'warning') {
            $score += 3;
        }

        if ($chunk->tipo_contenido === 'procedimiento') {
            $score += 5;
        }

        return $score;
    }

    private function serializeChunk(ManualChunk $chunk, int $score): array
    {
        return [
            'id' => $chunk->id,
            'score' => $score,
            'codigo_seccion' => $chunk->codigo_seccion,
            'titulo' => $chunk->titulo,
            'tipo_contenido' => $chunk->tipo_contenido,
            'pagina_inicio' => $chunk->pagina_inicio,
            'pagina_fin' => $chunk->pagina_fin,
            'resumen' => $chunk->resumen,
            'texto' => $chunk->texto,
            'keywords' => $chunk->keywords ?? [],
            'manual' => [
                'id' => $chunk->manual?->id,
                'nombre' => $chunk->manual?->nombre,
                'tipo_manual' => $chunk->manual?->tipo_manual,
                'aeronave_modelo' => $chunk->manual?->aeronave_modelo,
                'revision' => $chunk->manual?->revision,
                'estado' => $chunk->manual?->estado,
            ],
            'ata' => [
                'chapter' => $chunk->ataChapter ? [
                    'id' => $chunk->ataChapter->id,
                    'codigo' => $chunk->ataChapter->codigo,
                    'descripcion' => $chunk->ataChapter->descripcion,
                ] : null,
                'subchapter' => $chunk->ataSubchapter ? [
                    'id' => $chunk->ataSubchapter->id,
                    'codigo' => $chunk->ataSubchapter->codigo,
                    'descripcion' => $chunk->ataSubchapter->descripcion,
                ] : null,
            ],
            'referencias' => $chunk->referencias
                ->map(fn ($ref) => ['tipo' => $ref->tipo, 'valor' => $ref->valor])
                ->values()
                ->all(),
        ];
    }

    private function extractKeywords(?string $query): array
    {
        $normalized = Str::lower(Str::ascii((string) $query));

        return collect(preg_split('/[^a-z0-9]+/', $normalized) ?: [])
            ->filter(fn (string $token) => strlen($token) >= 3 && ! in_array($token, self::STOP_WORDS, true))
            ->unique()
            ->values()
            ->all();
    }

    private function inferAtaCandidates(?string $query): array
    {
        $normalized = Str::lower(Str::ascii((string) $query));
        $ata = [];

        $map = [
            24 => ['electrico', 'electrica', 'electrical', 'generator', 'generador', 'battery', 'bateria'],
            27 => ['flight control', 'aileron', 'rudder', 'elevator', 'trim'],
            29 => ['hidraulico', 'hidraulica', 'hydraulic'],
            32 => ['brake', 'brakes', 'freno', 'frenos', 'wheel', 'wheels', 'llanta', 'landing gear', 'tren'],
            49 => ['apu'],
            71 => ['powerplant'],
            72 => ['engine', 'motor', 'turbina', 'compressor'],
            79 => ['oil', 'aceite', 'lubrication', 'lubricacion'],
        ];

        foreach ($map as $chapter => $terms) {
            foreach ($terms as $term) {
                if (str_contains($normalized, $term)) {
                    $ata[] = $chapter;
                    break;
                }
            }
        }

        return array_values(array_unique($ata));
    }
}
