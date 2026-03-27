<?php

namespace App\Http\Controllers;

use App\Models\Aeronave;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AeronaveController extends Controller
{
    public function index(Request $request)
    {
        $payload = Cache::remember($this->cacheKey('index', $request->query()), now()->addMinutes(5), function () use ($request) {
            $aeronaves = Aeronave::query()
                ->select(['id', 'cliente', 'matricula', 'fabricante', 'modelo', 'numero_serie', 'estado'])
                ->withCount('motores')
                ->when($request->filled('matricula'), fn ($query) => $this->applyIndexedPrefixSearch($query, 'matricula', $request->string('matricula')))
                ->orderBy('matricula')
                ->get();

            return [
                'success' => true,
                'data' => $aeronaves->toArray(),
            ];
        });

        return response()->json($payload);
    }

    public function show(Aeronave $aeronave)
    {
        $payload = Cache::remember($this->cacheKey('show', ['id' => $aeronave->id]), now()->addMinutes(5), function () use ($aeronave) {
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
        });

        return response()->json($payload);
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

        return 'aeronaves:' . Cache::get('aeronaves_cache_version', 1) . ':' . $action . ':' . md5(json_encode($params));
    }

    private function bustCache(): void
    {
        Cache::forever('aeronaves_cache_version', (int) Cache::get('aeronaves_cache_version', 1) + 1);
        Cache::forever('motores_cache_version', (int) Cache::get('motores_cache_version', 1) + 1);
    }
}
