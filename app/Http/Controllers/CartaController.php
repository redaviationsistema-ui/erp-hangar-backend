<?php

namespace App\Http\Controllers;

use App\Http\Resources\CartaResource;
use App\Models\Carta;
use App\Models\Orden;
use App\Support\SchemaPayload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CartaController extends Controller
{
    public function index(Request $request)
    {
        $payload = $this->cacheOrFetch($this->cacheKey('index', array_merge($request->query(), $this->areaCacheContext($request))), now()->addMinutes(5), function () use ($request) {
            $items = $this->applyOrderAreaScope($request, Carta::query())
                ->select([
                    'id',
                    'orden_id',
                    'item',
                    'tarea',
                    'titulo',
                    'remanente',
                    'completado',
                    'siguiente',
                    'notas',
                    'accion_correctiva',
                    'descripcion_componente',
                    'cantidad',
                    'numero_parte',
                    'numero_serie_removido',
                    'numero_serie_instalado',
                    'observaciones',
                    'fecha_termino',
                    'horas_labor',
                    'auxiliar',
                    'tecnico',
                    'inspector',
                    'created_at',
                    'updated_at',
                ])
                ->with([
                    'orden:id,folio,fecha,estado,cliente,matricula,descripcion',
                ])
                ->when($request->filled('orden_id'), fn ($q) => $q->where('orden_id', $request->integer('orden_id')))
                ->orderBy('id')
                ->get();

            return [
                'success' => true,
                'data' => CartaResource::collection($items)->resolve($request),
            ];
        });

        return response()->json($payload);
    }

    public function store(Request $request)
    {
        $this->authorizeTecnicoOnly($request);
        $data = $this->validatePayload($request, false);
        $this->authorizeAreaId($request, Orden::findOrFail($data['orden_id'])->area_id);
        $this->bustCache();

        $carta = Carta::create(SchemaPayload::forModel(new Carta(), $data))->load('orden');

        return response()->json([
            'success' => true,
            'data' => CartaResource::make($carta)->resolve($request),
        ], 201);
    }

    public function show(Carta $carta)
    {
        $this->authorizeOrderArea(request(), $carta);
        $carta->load('orden');

        return response()->json([
            'success' => true,
            'data' => CartaResource::make($carta)->resolve(request()),
        ]);
    }

    public function update(Request $request, Carta $carta)
    {
        $this->authorizeOrderArea($request, $carta);
        $this->authorizeTecnicoOnly($request);
        $data = $this->validatePayload($request, true);
        if (array_key_exists('orden_id', $data)) {
            $this->authorizeAreaId($request, Orden::findOrFail($data['orden_id'])->area_id);
        }

        $carta->update(SchemaPayload::forModel($carta, $data));
        $this->bustCache();
        $carta->load('orden');

        return response()->json([
            'success' => true,
            'data' => CartaResource::make($carta)->resolve($request),
        ]);
    }

    public function destroy(Carta $carta)
    {
        $this->authorizeOrderArea(request(), $carta);
        $this->authorizeTecnicoOnly(request());
        $carta->delete();
        $this->bustCache();

        return response()->json(['success' => true, 'message' => 'Carta eliminada correctamente.']);
    }

    private function validatePayload(Request $request, bool $partial): array
    {
        $required = $partial ? 'sometimes' : 'required';

        return $request->validate([
            'orden_id' => ($partial ? 'sometimes' : 'required') . '|exists:ordenes,id',
            'item' => 'sometimes|nullable|string|max:20',
            'tarea' => 'sometimes|nullable|string|max:255',
            'titulo' => $required . '|string|max:255',
            'remanente' => 'sometimes|nullable|string|max:255',
            'completado' => 'sometimes|nullable|string|max:255',
            'siguiente' => 'sometimes|nullable|string|max:255',
            'notas' => 'sometimes|nullable|string',
            'accion_correctiva' => 'sometimes|nullable|string',
            'descripcion_componente' => 'sometimes|nullable|string|max:255',
            'cantidad' => 'sometimes|nullable|integer|min:1',
            'numero_parte' => 'sometimes|nullable|string|max:255',
            'numero_serie_removido' => 'sometimes|nullable|string|max:255',
            'numero_serie_instalado' => 'sometimes|nullable|string|max:255',
            'observaciones' => 'sometimes|nullable|string',
            'fecha_termino' => 'sometimes|nullable|date',
            'horas_labor' => 'sometimes|nullable|numeric',
            'auxiliar' => 'sometimes|nullable|string|max:255',
            'tecnico' => 'sometimes|nullable|string|max:255',
            'inspector' => 'sometimes|nullable|string|max:255',
        ]);
    }

    private function cacheKey(string $action, array $params = []): string
    {
        ksort($params);

        return 'cartas:' . Cache::get('cartas_cache_version', 1) . ':' . Cache::get('ordenes_cache_version', 1) . ':' . $action . ':' . md5(json_encode($params));
    }

    private function bustCache(): void
    {
        Cache::forever('cartas_cache_version', (int) Cache::get('cartas_cache_version', 1) + 1);
        Cache::forever('ordenes_cache_version', (int) Cache::get('ordenes_cache_version', 1) + 1);
    }
}

