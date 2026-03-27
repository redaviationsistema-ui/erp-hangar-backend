<?php

namespace App\Http\Controllers;

use App\Models\Motor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MotorController extends Controller
{
    public function index(Request $request)
    {
        $payload = Cache::remember($this->cacheKey('index', $request->query()), now()->addMinutes(5), function () use ($request) {
            $includeCounts = $request->boolean('include_counts');
            $all = $request->boolean('all');
            $perPage = max(1, min($request->integer('per_page', 50), 100));

            $query = Motor::query()
                ->leftJoin('aeronaves as aeronave', 'aeronave.id', '=', 'motores.aeronave_id')
                ->select([
                    'motores.id',
                    'motores.aeronave_id',
                    'motores.posicion',
                    'motores.fabricante',
                    'motores.modelo',
                    'motores.numero_parte',
                    'motores.numero_serie',
                    'motores.tiempo_total',
                    'motores.ciclos_totales',
                    'motores.estado',
                    'aeronave.cliente as aeronave_cliente',
                    'aeronave.matricula as aeronave_matricula',
                    'aeronave.fabricante as aeronave_fabricante',
                    'aeronave.modelo as aeronave_modelo',
                    'aeronave.numero_serie as aeronave_numero_serie',
                    'aeronave.estado as aeronave_estado',
                ])
                ->when($request->filled('aeronave_id'), fn ($query) => $query->where('motores.aeronave_id', $request->integer('aeronave_id')))
                ->when($request->filled('numero_serie'), fn ($query) => $this->applyIndexedPrefixSearch($query, 'motores.numero_serie', $request->string('numero_serie')))
                ->orderBy('motores.numero_serie');

            if ($includeCounts) {
                $query->withCount('ordenes');
            }

            $motores = $all
                ? $query->get()
                : $query->simplePaginate($perPage);

            return [
                'success' => true,
                'meta' => [
                    'include_counts' => $includeCounts,
                    'all' => $all,
                    'per_page' => $all ? null : $motores->perPage(),
                    'current_page' => $all ? 1 : $motores->currentPage(),
                    'has_more_pages' => $all ? false : $motores->hasMorePages(),
                    'next_page_url' => $all ? null : $motores->nextPageUrl(),
                    'prev_page_url' => $all ? null : $motores->previousPageUrl(),
                ],
                'data' => ($all ? $motores : $motores->getCollection())
                    ->map(fn (Motor $motor) => $this->serializeIndexMotor($motor, $includeCounts))
                    ->values()
                    ->all(),
            ];
        });

        return response()->json($payload);
    }

    public function show(Motor $motor)
    {
        $payload = Cache::remember($this->cacheKey('show', ['id' => $motor->id]), now()->addMinutes(5), function () use ($motor) {
            $motor->load([
                'aeronave:id,cliente,matricula,fabricante,modelo,numero_serie,estado',
                'ordenes' => fn ($query) => $query
                    ->select([
                        'id',
                        'area_id',
                        'tipo_id',
                        'user_id',
                        'motor_id',
                        'folio',
                        'fecha',
                        'estado',
                        'cliente',
                        'matricula',
                        'descripcion',
                    ])
                    ->with([
                        'area:id,codigo,numero,nombre',
                        'tipo:id,codigo,nombre',
                        'usuario:id,name,email',
                    ]),
            ]);

            return [
                'success' => true,
                'data' => $motor->toArray(),
            ];
        });

        return response()->json($payload);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'aeronave_id' => 'required|exists:aeronaves,id',
            'posicion' => 'nullable|string|max:255',
            'fabricante' => 'nullable|string|max:255',
            'modelo' => 'nullable|string|max:255',
            'numero_parte' => 'nullable|string|max:255',
            'numero_serie' => 'required|string|max:255|unique:motores,numero_serie',
            'tiempo_total' => 'nullable|numeric',
            'ciclos_totales' => 'nullable|numeric',
            'estado' => 'nullable|string|max:100',
            'notas' => 'nullable|string',
        ]);

        $motor = Motor::create($data);
        $this->bustCache();

        return response()->json([
            'success' => true,
            'message' => 'Motor creado correctamente.',
            'data' => $motor->load('aeronave'),
        ], 201);
    }

    public function update(Request $request, Motor $motor)
    {
        $data = $request->validate([
            'aeronave_id' => 'sometimes|exists:aeronaves,id',
            'posicion' => 'sometimes|nullable|string|max:255',
            'fabricante' => 'sometimes|nullable|string|max:255',
            'modelo' => 'sometimes|nullable|string|max:255',
            'numero_parte' => 'sometimes|nullable|string|max:255',
            'numero_serie' => 'sometimes|string|max:255|unique:motores,numero_serie,' . $motor->id,
            'tiempo_total' => 'sometimes|nullable|numeric',
            'ciclos_totales' => 'sometimes|nullable|numeric',
            'estado' => 'sometimes|nullable|string|max:100',
            'notas' => 'sometimes|nullable|string',
        ]);

        $motor->update($data);
        $this->bustCache();

        return response()->json([
            'success' => true,
            'message' => 'Motor actualizado correctamente.',
            'data' => $motor->fresh('aeronave'),
        ]);
    }

    public function destroy(Motor $motor)
    {
        $motor->delete();
        $this->bustCache();

        return response()->json([
            'success' => true,
            'message' => 'Motor eliminado correctamente.',
        ]);
    }

    private function cacheKey(string $action, array $params = []): string
    {
        ksort($params);

        return 'motores:' . Cache::get('motores_cache_version', 1) . ':' . $action . ':' . md5(json_encode($params));
    }

    private function serializeIndexMotor(Motor $motor, bool $includeCounts): array
    {
        $payload = [
            'id' => $motor->id,
            'aeronave_id' => $motor->aeronave_id,
            'posicion' => $motor->posicion,
            'fabricante' => $motor->fabricante,
            'modelo' => $motor->modelo,
            'numero_parte' => $motor->numero_parte,
            'numero_serie' => $motor->numero_serie,
            'tiempo_total' => $motor->tiempo_total,
            'ciclos_totales' => $motor->ciclos_totales,
            'estado' => $motor->estado,
            'aeronave' => $motor->aeronave_id ? [
                'id' => $motor->aeronave_id,
                'cliente' => $motor->aeronave_cliente,
                'matricula' => $motor->aeronave_matricula,
                'fabricante' => $motor->aeronave_fabricante,
                'modelo' => $motor->aeronave_modelo,
                'numero_serie' => $motor->aeronave_numero_serie,
                'estado' => $motor->aeronave_estado,
            ] : null,
        ];

        if ($includeCounts) {
            $payload['ordenes_count'] = $motor->ordenes_count;
        }

        return $payload;
    }

    private function bustCache(): void
    {
        Cache::forever('motores_cache_version', (int) Cache::get('motores_cache_version', 1) + 1);
        Cache::forever('aeronaves_cache_version', (int) Cache::get('aeronaves_cache_version', 1) + 1);
        Cache::forever('ordenes_cache_version', (int) Cache::get('ordenes_cache_version', 1) + 1);
    }
}
