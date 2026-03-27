<?php

namespace App\Http\Controllers;

use App\Models\Orden;
use App\Models\Tarea;
use App\Support\SchemaPayload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TareaController extends Controller
{
    public function index(Request $request)
    {
        $payload = Cache::remember($this->cacheKey('index', array_merge($request->query(), $this->areaCacheContext($request))), now()->addSeconds(30), function () use ($request) {
            $query = $this->applyAreaScope($request, Tarea::query())
                ->select([
                    'id',
                    'orden_id',
                    'area_id',
                    'ata_task_template_id',
                    'titulo',
                    'descripcion',
                    'orden',
                    'tipo',
                    'prioridad',
                    'tiempo_estimado_min',
                    'estado',
                    'tecnico',
                    'foto_path',
                    'created_at',
                    'updated_at',
                ])
                ->with([
                    'orden:id,folio,fecha,estado,cliente,matricula,descripcion',
                    'area:id,codigo,numero,nombre',
                    'plantillaAta:id,ata_subchapter_id,area_id,titulo,descripcion,tipo,tiempo_estimado_min,prioridad',
                ])
                ->when($request->filled('orden_id'), fn ($q) => $q->where('orden_id', $request->integer('orden_id')))
                ->orderBy('orden')
                ->orderBy('id');

            if ($request->filled('per_page')) {
                $tareas = $query->paginate(max(1, min($request->integer('per_page', 25), 100)));

                return [
                    'success' => true,
                    'data' => $tareas->items(),
                    'meta' => [
                        'current_page' => $tareas->currentPage(),
                        'last_page' => $tareas->lastPage(),
                        'per_page' => $tareas->perPage(),
                        'total' => $tareas->total(),
                    ],
                ];
            }

            return ['success' => true, 'data' => $query->get()];
        });

        return response()->json($payload);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'orden_id' => 'required|exists:ordenes,id',
            'area_id' => 'nullable|exists:areas,id',
            'ata_task_template_id' => 'nullable|exists:ata_task_templates,id',
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'orden' => 'nullable|integer',
            'tipo' => 'nullable|string|max:100',
            'prioridad' => 'nullable|string|max:100',
            'tiempo_estimado_min' => 'nullable|integer',
            'estado' => 'nullable|string|max:100',
            'tecnico' => 'nullable|string|max:255',
            'foto_path' => 'nullable|string|max:255',
        ]);
        $this->storeIncomingImage($request, $data, 'foto_path', 'tareas', [
            'foto',
            'imagen',
            'image',
            'evidencia',
            'foto_base64',
            'imagen_base64',
            'evidencia_base64',
        ]);
        $orden = Orden::findOrFail($data['orden_id']);
        $this->authorizeAreaId($request, $orden->area_id);
        $data['area_id'] = $data['area_id'] ?? $orden->area_id ?? $this->effectiveAreaId($request);
        $this->authorizeAreaId($request, $data['area_id']);

        $this->bustCache();

        return response()->json([
            'success' => true,
            'data' => Tarea::create(SchemaPayload::forModel(new Tarea(), $data))->load(['orden', 'area', 'plantillaAta']),
        ], 201);
    }

    public function show(Tarea $tarea)
    {
        $this->authorizeModelArea(request(), $tarea);

        return response()->json([
            'success' => true,
            'data' => $tarea->load(['orden', 'area', 'plantillaAta']),
        ]);
    }

    public function update(Request $request, Tarea $tarea)
    {
        $this->authorizeModelArea($request, $tarea);

        $data = $request->validate([
            'area_id' => 'sometimes|nullable|exists:areas,id',
            'ata_task_template_id' => 'sometimes|nullable|exists:ata_task_templates,id',
            'titulo' => 'sometimes|string|max:255',
            'descripcion' => 'sometimes|nullable|string',
            'orden' => 'sometimes|nullable|integer',
            'tipo' => 'sometimes|nullable|string|max:100',
            'prioridad' => 'sometimes|nullable|string|max:100',
            'tiempo_estimado_min' => 'sometimes|nullable|integer',
            'estado' => 'sometimes|nullable|string|max:100',
            'tecnico' => 'sometimes|nullable|string|max:255',
            'foto_path' => 'sometimes|nullable|string|max:255',
        ]);
        $this->storeIncomingImage($request, $data, 'foto_path', 'tareas', [
            'foto',
            'imagen',
            'image',
            'evidencia',
            'foto_base64',
            'imagen_base64',
            'evidencia_base64',
        ]);
        if (array_key_exists('area_id', $data)) {
            $this->authorizeAreaId($request, $data['area_id']);
        }

        $this->replaceStoredImage($tarea->foto_path, $data['foto_path'] ?? null);
        $tarea->update(SchemaPayload::forModel($tarea, $data));
        $this->bustCache();

        return response()->json(['success' => true, 'data' => $tarea->load(['orden', 'area', 'plantillaAta'])]);
    }

    public function destroy(Tarea $tarea)
    {
        $this->authorizeModelArea(request(), $tarea);
        $this->deleteStoredImage($tarea->foto_path);
        $tarea->delete();
        $this->bustCache();

        return response()->json(['success' => true, 'message' => 'Tarea eliminada correctamente.']);
    }

    private function cacheKey(string $action, array $params = []): string
    {
        ksort($params);

        return 'tareas:' . Cache::get('tareas_cache_version', 1) . ':' . $action . ':' . md5(json_encode($params));
    }

    private function bustCache(): void
    {
        Cache::forever('tareas_cache_version', (int) Cache::get('tareas_cache_version', 1) + 1);
    }
}
