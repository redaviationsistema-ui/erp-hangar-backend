<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Support\AreaOtFormSchema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AreaController extends Controller
{
    public function index()
    {
        $request = request();

        $payload = $this->cacheOrFetch($this->cacheKey('index', $this->areaCacheContext($request)), now()->addMinutes(5), function () use ($request) {
            $query = Area::query()
                ->select(['id', 'nombre', 'codigo', 'numero', 'created_at', 'updated_at'])
                ->withCount('ordenes')
                ->orderBy('numero');

            if (! $this->hasGlobalAreaAccess($request)) {
                $query->whereKey($this->currentAreaId($request));
            }

            return [
                'success' => true,
                'data' => $query->get()->map(function (Area $area) {
                    $payload = $area->toArray();
                    $payload['ot_form'] = AreaOtFormSchema::for($area);

                    return $payload;
                })->values(),
            ];
        });

        return response()->json($payload);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'codigo' => 'required|string|max:20|unique:areas,codigo',
            'numero' => 'required|string|max:10',
        ]);

        $area = Area::create($data);
        $this->bustCache();

        return response()->json([
            'success' => true,
            'data' => $area,
        ], 201);
    }

    public function show(Area $area)
    {
        $this->authorizeAreaId(request(), $area->id);

        $payload = $this->cacheOrFetch($this->cacheKey('show', ['id' => $area->id] + $this->areaCacheContext(request())), now()->addMinutes(5), function () use ($area) {
            return [
                'success' => true,
                'data' => tap($area->load([
                    'ordenes' => fn ($query) => $query
                        ->select([
                            'id',
                            'area_id',
                            'tipo_id',
                            'user_id',
                            'folio',
                            'fecha',
                            'cliente',
                            'matricula',
                            'descripcion',
                            'estado',
                            'created_at',
                            'updated_at',
                        ])
                        ->with([
                            'tipo:id,codigo,nombre',
                            'usuario:id,name,email',
                        ])
                        ->latest('fecha')
                        ->latest('id'),
                ])->loadCount('ordenes'), function (Area $area) {
                    $area->setAttribute('ot_form', AreaOtFormSchema::for($area));
                }),
            ];
        });

        return response()->json($payload);
    }

    public function update(Request $request, Area $area)
    {
        $data = $request->validate([
            'nombre' => 'sometimes|string|max:255',
            'codigo' => 'sometimes|string|max:20|unique:areas,codigo,' . $area->id,
            'numero' => 'sometimes|string|max:10',
        ]);

        $area->update($data);
        $this->bustCache();

        return response()->json([
            'success' => true,
            'data' => $area,
        ]);
    }

    public function destroy(Area $area)
    {
        $area->delete();
        $this->bustCache();

        return response()->json([
            'success' => true,
            'message' => 'Area eliminada correctamente.',
        ]);
    }

    private function cacheKey(string $action, array $params = []): string
    {
        ksort($params);

        return 'areas:' . Cache::get('areas_cache_version', 1) . ':' . Cache::get('ordenes_cache_version', 1) . ':' . $action . ':' . md5(json_encode($params));
    }

    private function bustCache(): void
    {
        Cache::forever('areas_cache_version', (int) Cache::get('areas_cache_version', 1) + 1);
    }
}
