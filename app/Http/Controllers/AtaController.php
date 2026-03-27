<?php

namespace App\Http\Controllers;

use App\Models\AtaChapter;
use App\Models\AtaSubchapter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AtaController extends Controller
{
    public function index(Request $request)
    {
        $payload = Cache::remember($this->cacheKey('index', array_merge($request->query(), $this->areaCacheContext($request))), now()->addMinutes(5), function () use ($request) {
            $areaId = $this->effectiveAreaId($request);
            $chapters = AtaChapter::query()
                ->select(['id', 'codigo', 'descripcion'])
                ->with([
                    'subchapters' => fn ($query) => $query
                        ->select([
                            'id',
                            'ata_chapter_id',
                            'codigo',
                            'descripcion',
                            'intervalo_horas',
                            'intervalo_ciclos',
                            'intervalo_dias',
                            'tipo_mantenimiento',
                        ])
                        ->when($areaId, fn ($sub) => $sub->whereHas(
                            'tasks',
                            fn ($task) => $task->where('area_id', $areaId)
                        ))
                        ->with(['tasks' => fn ($task) => $task
                            ->select([
                                'id',
                                'ata_subchapter_id',
                                'area_id',
                                'titulo',
                                'descripcion',
                                'tipo',
                                'tiempo_estimado_min',
                                'prioridad',
                            ])
                            ->when($areaId, fn ($q) => $q->where('area_id', $areaId))
                            ->with('area:id,codigo,numero,nombre')
                            ->orderBy('titulo')])
                        ->withCount('tasks')
                        ->orderBy('codigo'),
                ])
                ->orderBy('codigo')
                ->get();

            return [
                'success' => true,
                'data' => $chapters,
            ];
        });

        return response()->json($payload);
    }

    public function subchapters(AtaChapter $chapter)
    {
        $request = request();
        $areaId = $this->effectiveAreaId($request);

        $payload = Cache::remember($this->cacheKey('subchapters', ['chapter' => $chapter->id] + $this->areaCacheContext($request)), now()->addMinutes(5), function () use ($chapter, $areaId) {
            return [
                'success' => true,
                'data' => $chapter->subchapters()
                    ->select([
                        'id',
                        'ata_chapter_id',
                        'codigo',
                        'descripcion',
                        'intervalo_horas',
                        'intervalo_ciclos',
                        'intervalo_dias',
                        'tipo_mantenimiento',
                    ])
                    ->with([
                        'tasks' => fn ($query) => $query
                            ->select([
                                'id',
                                'ata_subchapter_id',
                                'area_id',
                                'titulo',
                                'descripcion',
                                'tipo',
                                'tiempo_estimado_min',
                                'prioridad',
                            ])
                            ->when($areaId, fn ($task) => $task->where('area_id', $areaId))
                            ->with('area:id,codigo,numero,nombre')
                            ->orderBy('titulo'),
                    ])
                    ->withCount('tasks')
                    ->orderBy('codigo')
                    ->get(),
            ];
        });

        return response()->json($payload);
    }

    public function showSubchapter(AtaSubchapter $subchapter)
    {
        $request = request();
        $areaId = $this->effectiveAreaId($request);

        $payload = Cache::remember($this->cacheKey('show', ['subchapter' => $subchapter->id] + $this->areaCacheContext($request)), now()->addMinutes(5), function () use ($subchapter, $areaId) {
            return [
                'success' => true,
                'data' => $subchapter->load([
                    'chapter:id,codigo,descripcion',
                    'tasks' => fn ($query) => $query
                        ->select([
                            'id',
                            'ata_subchapter_id',
                            'area_id',
                            'titulo',
                            'descripcion',
                            'tipo',
                            'tiempo_estimado_min',
                            'prioridad',
                        ])
                        ->when($areaId, fn ($task) => $task->where('area_id', $areaId))
                        ->with('area:id,codigo,numero,nombre')
                        ->orderBy('titulo'),
                ]),
            ];
        });

        return response()->json($payload);
    }

    private function cacheKey(string $action, array $params = []): string
    {
        ksort($params);

        return 'ata:' . $action . ':' . md5(json_encode($params));
    }
}
