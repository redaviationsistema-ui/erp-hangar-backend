<?php

namespace App\Http\Controllers;

use App\Models\Aeronave;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AeronaveController extends Controller
{
    public function index(Request $request)
    {
        return response()->json($this->getAeronavesData($request));
    }

    private function getAeronavesData(Request $request)
    {
        $all = $request->boolean('all');
        $includeCounts = $request->boolean('include_counts');
        $perPage = max(1, min($request->integer('per_page', 25), 100));

        $query = Aeronave::query()
            ->select(['id', 'cliente', 'matricula', 'fabricante', 'modelo', 'numero_serie', 'estado'])
            ->when($request->filled('matricula'), fn ($query) => $this->applyIndexedPrefixSearch($query, 'matricula', $request->string('matricula')))
            ->orderBy('matricula');

        if ($includeCounts) {
            $query->withCount('motores');
        }

        $aeronaves = $all
            ? $query->get()
            : $query->simplePaginate($perPage);

        return [
            'success' => true,
            'meta' => [
                'include_counts' => $includeCounts,
                'all' => $all,
                'per_page' => $all ? null : $aeronaves->perPage(),
                'current_page' => $all ? 1 : $aeronaves->currentPage(),
                'has_more_pages' => $all ? false : $aeronaves->hasMorePages(),
                'next_page_url' => $all ? null : $aeronaves->nextPageUrl(),
                'prev_page_url' => $all ? null : $aeronaves->previousPageUrl(),
            ],
            'data' => ($all ? $aeronaves : $aeronaves->getCollection())
                ->map(fn (Aeronave $aeronave) => [
                    'id' => $aeronave->id,
                    'cliente' => $aeronave->cliente,
                    'matricula' => $aeronave->matricula,
                    'fabricante' => $aeronave->fabricante,
                    'modelo' => $aeronave->modelo,
                    'numero_serie' => $aeronave->numero_serie,
                    'estado' => $aeronave->estado,
                    'motores_count' => $includeCounts ? $aeronave->motores_count : null,
                ])
                ->values()
                ->all(),
        ];
    }

    public function show(Aeronave $aeronave)
    {
        return response()->json($this->getAeronaveData($aeronave));
    }

    private function getAeronaveData(Aeronave $aeronave)
    {
        $aeronave->load([
            'motores' => fn ($query) => $query
                ->select([
                    'id',
                    'aeronave_id',
                    'posicion',
                    'fabricante',
                    'modelo',
                    'numero_parte',
                    'numero_serie',
                    'tiempo_total',
                    'ciclos_totales',
                    'estado',
                ])
                ->orderBy('posicion')
                ->orderBy('numero_serie'),
        ]);

        return [
            'success' => true,
            'data' => $aeronave->toArray(),
        ];
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'cliente' => 'nullable|string|max:255',
            'matricula' => 'required|string|max:255|unique:aeronaves,matricula',
            'fabricante' => 'nullable|string|max:255',
            'modelo' => 'nullable|string|max:255',
            'numero_serie' => 'nullable|string|max:255',
            'estado' => 'nullable|string|max:100',
            'notas' => 'nullable|string',
        ]);

        $aeronave = Aeronave::create($data);
        $this->bustCache();

        return response()->json([
            'success' => true,
            'message' => 'Aeronave creada correctamente.',
            'data' => $aeronave,
        ], 201);
    }

    public function update(Request $request, Aeronave $aeronave)
    {
        $data = $request->validate([
            'cliente' => 'sometimes|nullable|string|max:255',
            'matricula' => 'sometimes|string|max:255|unique:aeronaves,matricula,' . $aeronave->id,
            'fabricante' => 'sometimes|nullable|string|max:255',
            'modelo' => 'sometimes|nullable|string|max:255',
            'numero_serie' => 'sometimes|nullable|string|max:255',
            'estado' => 'sometimes|nullable|string|max:100',
            'notas' => 'sometimes|nullable|string',
        ]);

        $aeronave->update($data);
        $this->bustCache();

        return response()->json([
            'success' => true,
            'message' => 'Aeronave actualizada correctamente.',
            'data' => $aeronave->fresh('motores'),
        ]);
    }

    public function destroy(Aeronave $aeronave)
    {
        $aeronave->delete();
        $this->bustCache();

        return response()->json([
            'success' => true,
            'message' => 'Aeronave eliminada correctamente.',
        ]);
    }

    private function cacheKey(string $action, array $params = []): string
    {
        ksort($params);

        return 'aeronaves:' . Cache::get('aeronaves_cache_version', 2) . ':' . $action . ':' . md5(json_encode($params));
    }

    private function bustCache(): void
    {
        Cache::forever('aeronaves_cache_version', (int) Cache::get('aeronaves_cache_version', 1) + 1);
        Cache::forever('motores_cache_version', (int) Cache::get('motores_cache_version', 1) + 1);
        Cache::forever('dashboard_cache_version', (int) Cache::get('dashboard_cache_version', 1) + 1);
    }
}
