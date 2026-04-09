<?php

namespace App\Http\Controllers;

use App\Models\AtaChapter;
use App\Models\AtaSubchapter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

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

            $chapters->each(fn (AtaChapter $chapter) => $this->appendSearchTermsToChapter($chapter));

            return [
                'success' => true,
                'data' => $chapters->map(fn (AtaChapter $chapter) => $chapter->toArray())->values()->all(),
            ];
        });

        return response()->json($payload);
    }

    public function subchapters(AtaChapter $chapter)
    {
        $request = request();
        $areaId = $this->effectiveAreaId($request);

        $payload = Cache::remember($this->cacheKey('subchapters', ['chapter' => $chapter->id] + $this->areaCacheContext($request)), now()->addMinutes(5), function () use ($chapter, $areaId) {
            $subchapters = $chapter->subchapters()
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
                ->get();

            $subchapters->each(fn (AtaSubchapter $subchapter) => $this->appendSearchTermsToSubchapter($subchapter, $chapter));

            return [
                'success' => true,
                'data' => $subchapters->map(fn (AtaSubchapter $subchapter) => $subchapter->toArray())->values()->all(),
            ];
        });

        return response()->json($payload);
    }

    public function showSubchapter(AtaSubchapter $subchapter)
    {
        $request = request();
        $areaId = $this->effectiveAreaId($request);

        $payload = Cache::remember($this->cacheKey('show', ['subchapter' => $subchapter->id] + $this->areaCacheContext($request)), now()->addMinutes(5), function () use ($subchapter, $areaId) {
            $subchapter->load([
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
            ]);

            $this->appendSearchTermsToSubchapter($subchapter, $subchapter->chapter);

            return [
                'success' => true,
                'data' => $subchapter->toArray(),
            ];
        });

        return response()->json($payload);
    }

    private function appendSearchTermsToChapter(AtaChapter $chapter): void
    {
        if ($chapter->relationLoaded('subchapters')) {
            $chapter->subchapters->each(fn (AtaSubchapter $subchapter) => $this->appendSearchTermsToSubchapter($subchapter, $chapter));
        }

        $chapterTerms = collect([
            $chapter->codigo,
            $chapter->descripcion,
        ])->merge(
            $chapter->relationLoaded('subchapters')
                ? $chapter->subchapters->flatMap(fn (AtaSubchapter $subchapter) => $subchapter->search_terms ?? [])
                : []
        );

        $chapter->setAttribute('search_terms', $this->normalizeSearchTerms($chapterTerms->all()));
    }

    private function appendSearchTermsToSubchapter(AtaSubchapter $subchapter, ?AtaChapter $chapter = null): void
    {
        $chapter ??= $subchapter->relationLoaded('chapter') ? $subchapter->chapter : null;

        if ($subchapter->relationLoaded('tasks')) {
            $subchapter->tasks->each(function ($task) use ($chapter, $subchapter) {
                $task->setAttribute('search_terms', $this->normalizeSearchTerms([
                    $task->titulo,
                    $task->descripcion,
                    $subchapter->codigo,
                    $subchapter->descripcion,
                    $chapter?->codigo,
                    $chapter?->descripcion,
                ]));
            });
        }

        $taskTerms = $subchapter->relationLoaded('tasks')
            ? $subchapter->tasks->flatMap(fn ($task) => [
                $task->titulo,
                $task->descripcion,
            ])
            : collect();

        $subchapter->setAttribute('search_terms', $this->normalizeSearchTerms([
            $subchapter->codigo,
            $subchapter->descripcion,
            $chapter?->codigo,
            $chapter?->descripcion,
            ...$taskTerms->all(),
        ]));
    }

    private function normalizeSearchTerms(array $terms): array
    {
        return collect($terms)
            ->filter(fn ($term) => filled($term))
            ->flatMap(function ($term) {
                $raw = trim((string) $term);
                if ($raw === '') {
                    return [];
                }

                $normalized = Str::of($raw)
                    ->ascii()
                    ->lower()
                    ->replaceMatches('/[^a-z0-9]+/', ' ')
                    ->squish()
                    ->value();

                $tokens = preg_split('/\s+/', $normalized) ?: [];

                return collect([$raw, $normalized])
                    ->merge($tokens)
                    ->filter(fn (string $value) => strlen($value) >= 3 || preg_match('/^\d{2}(?:-\d{2})?$/', $value));
            })
            ->map(fn ($term) => trim((string) $term))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function cacheKey(string $action, array $params = []): string
    {
        ksort($params);

        return 'ata:' . $action . ':' . md5(json_encode($params));
    }
}
