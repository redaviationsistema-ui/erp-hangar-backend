<?php

namespace App\Http\Controllers;

use App\Http\Resources\DiscrepanciaResource;
use App\Models\Discrepancia;
use App\Support\SchemaPayload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DiscrepanciaController extends Controller
{
    public function index(Request $request)
    {
        $payload = Cache::remember($this->cacheKey('index', array_merge($request->query(), $this->areaCacheContext($request))), now()->addMinutes(5), function () use ($request) {
            $items = $this->applyOrderAreaScope($request, Discrepancia::query())
                ->select([
                    'id',
                    'orden_id',
                    'item',
                    'descripcion',
                    'accion_correctiva',
                    'status',
                    'inspector',
                    'fecha_inicio',
                    'fecha_termino',
                    'horas_hombre',
                    'imagen_path',
                    'componente_numero_parte_off',
                    'componente_numero_serie_off',
                    'componente_numero_parte_on',
                    'componente_numero_serie_on',
                    'observaciones',
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
                'data' => DiscrepanciaResource::collection($items)->resolve($request),
            ];
        });

        return response()->json($payload);
    }

    public function store(Request $request)
    {
        $data = $this->validatePayload($request, false);
        $this->storeIncomingImage($request, $data, 'imagen_path', 'discrepancias', [
            'foto',
            'imagen',
            'image',
            'evidencia',
            'foto_base64',
            'imagen_base64',
            'evidencia_base64',
        ]);
        $this->authorizeAreaId($request, \App\Models\Orden::findOrFail($data['orden_id'])->area_id);
        $this->bustCache();

        $discrepancia = Discrepancia::create(SchemaPayload::forModel(new Discrepancia(), $data))->load('orden');

        return response()->json([
            'success' => true,
            'data' => DiscrepanciaResource::make($discrepancia)->resolve($request),
        ], 201);
    }

    public function show(Discrepancia $discrepancia)
    {
        $this->authorizeOrderArea(request(), $discrepancia);
        $discrepancia->load('orden');

        return response()->json([
            'success' => true,
            'data' => DiscrepanciaResource::make($discrepancia)->resolve(request()),
        ]);
    }

    public function update(Request $request, Discrepancia $discrepancia)
    {
        $this->authorizeOrderArea($request, $discrepancia);
        $data = $this->validatePayload($request, true);
        $this->storeIncomingImage($request, $data, 'imagen_path', 'discrepancias', [
            'foto',
            'imagen',
            'image',
            'evidencia',
            'foto_base64',
            'imagen_base64',
            'evidencia_base64',
        ]);
        if (array_key_exists('orden_id', $data)) {
            $this->authorizeAreaId($request, \App\Models\Orden::findOrFail($data['orden_id'])->area_id);
        }

        $this->replaceStoredImage($discrepancia->imagen_path, $data['imagen_path'] ?? null);
        $discrepancia->update(SchemaPayload::forModel($discrepancia, $data));
        $this->bustCache();

        $discrepancia->load('orden');

        return response()->json([
            'success' => true,
            'data' => DiscrepanciaResource::make($discrepancia)->resolve($request),
        ]);
    }

    public function destroy(Discrepancia $discrepancia)
    {
        $this->authorizeOrderArea(request(), $discrepancia);
        $this->deleteStoredImage($discrepancia->imagen_path);
        $discrepancia->delete();
        $this->bustCache();

        return response()->json(['success' => true, 'message' => 'Discrepancia eliminada correctamente.']);
    }

    private function validatePayload(Request $request, bool $partial): array
    {
        $required = $partial ? 'sometimes' : 'required';

        return $request->validate([
            'orden_id' => ($partial ? 'sometimes' : 'required') . '|exists:ordenes,id',
            'item' => 'sometimes|nullable|string|max:20',
            'descripcion' => $required . '|string',
            'accion_correctiva' => 'sometimes|nullable|string',
            'status' => 'sometimes|nullable|string|max:100',
            'inspector' => 'sometimes|nullable|string|max:255',
            'fecha_inicio' => 'sometimes|nullable|date',
            'fecha_termino' => 'sometimes|nullable|date',
            'horas_hombre' => 'sometimes|nullable|numeric',
            'imagen_path' => 'sometimes|nullable|string|max:2048',
            'foto' => 'sometimes|nullable|image|max:5120',
            'imagen' => 'sometimes|nullable|image|max:5120',
            'image' => 'sometimes|nullable|image|max:5120',
            'evidencia' => 'sometimes|nullable|image|max:5120',
            'componente_numero_parte_off' => 'sometimes|nullable|string|max:255',
            'componente_numero_serie_off' => 'sometimes|nullable|string|max:255',
            'componente_numero_parte_on' => 'sometimes|nullable|string|max:255',
            'componente_numero_serie_on' => 'sometimes|nullable|string|max:255',
            'observaciones' => 'sometimes|nullable|string',
        ]);
    }

    private function cacheKey(string $action, array $params = []): string
    {
        ksort($params);

        return 'discrepancias:' . Cache::get('discrepancias_cache_version', 1) . ':' . Cache::get('ordenes_cache_version', 1) . ':' . $action . ':' . md5(json_encode($params));
    }

    private function bustCache(): void
    {
        Cache::forever('discrepancias_cache_version', (int) Cache::get('discrepancias_cache_version', 1) + 1);
        Cache::forever('ordenes_cache_version', (int) Cache::get('ordenes_cache_version', 1) + 1);
    }
}
