<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrdenRequest;
use App\Http\Requests\UpdateOrdenRequest;
use App\Http\Resources\OrdenResource;
use App\Models\Area;
use App\Models\AtaSubchapter;
use App\Models\Motor;
use App\Models\Orden;
use App\Services\OrdenService;
use App\Support\SchemaPayload;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class OrdenController extends Controller
{
    public function __construct(private readonly OrdenService $ordenService)
    {
    }

    public function index(Request $request)
    {
        $payload = Cache::remember($this->cacheKey('index', array_merge($request->query(), $this->areaCacheContext($request))), now()->addMinutes(5), function () use ($request) {
            $fastMode = ! $request->has('fast') || $request->boolean('fast');
            $includeAta = $request->boolean('include_ata');
            $includeCounts = $request->boolean('include_counts');
            $query = $this->applyAreaScope($request, Orden::query(), 'ordenes.area_id')
                ->leftJoin('areas as area', 'area.id', '=', 'ordenes.area_id')
                ->leftJoin('tipo_ordenes as tipo', 'tipo.id', '=', 'ordenes.tipo_id')
                ->leftJoin('users as usuario', 'usuario.id', '=', 'ordenes.user_id')
                ->leftJoin('motores as motor', 'motor.id', '=', 'ordenes.motor_id')
                ->leftJoin('aeronaves as aeronave', 'aeronave.id', '=', 'motor.aeronave_id')
                ->select($this->summaryColumnsWithJoins($includeAta));

            if ($includeAta) {
                $query
                    ->leftJoin('ata_chapters as ata_chapter', 'ata_chapter.id', '=', 'ordenes.ata_chapter_id')
                    ->leftJoin('ata_subchapters as ata_subchapter', 'ata_subchapter.id', '=', 'ordenes.ata_subchapter_id');
            }

            if ($includeCounts) {
                $query->withCount($this->countRelations());
            }

            $query
                ->when($request->filled('estado'), fn ($q) => $q->where('ordenes.estado', $request->string('estado')))
                ->when($request->filled('tipo_id'), fn ($q) => $q->where('ordenes.tipo_id', $request->integer('tipo_id')))
                ->when($request->filled('motor_id'), fn ($q) => $q->where('ordenes.motor_id', $request->integer('motor_id')))
                ->when($request->filled('aeronave_id'), fn ($q) => $q->where('motor.aeronave_id', $request->integer('aeronave_id')))
                ->when($request->filled('ata_chapter_id'), fn ($q) => $q->where('ordenes.ata_chapter_id', $request->integer('ata_chapter_id')))
                ->when($request->filled('ata_subchapter_id'), fn ($q) => $q->where('ordenes.ata_subchapter_id', $request->integer('ata_subchapter_id')))
                ->when($request->filled('folio'), fn ($q) => $this->applyIndexedPrefixSearch($q, 'ordenes.folio', $request->string('folio')))
                ->when($request->filled('matricula'), fn ($q) => $this->applyIndexedPrefixSearch($q, 'ordenes.matricula', $request->string('matricula')));

            $perPage = max(1, min($request->integer('per_page', 10), 100));
            $ordenes = $fastMode
                ? $query->orderByDesc('ordenes.fecha')->orderByDesc('ordenes.id')->simplePaginate($perPage)
                : $query->orderByDesc('ordenes.fecha')->orderByDesc('ordenes.id')->paginate($perPage);

            return [
                'success' => true,
                'message' => 'Ordenes obtenidas correctamente.',
                'data' => $ordenes->getCollection()
                    ->map(fn (Orden $orden) => $this->serializeIndexOrder($orden, $includeAta, $includeCounts))
                    ->values()
                    ->all(),
                'meta' => [
                    'current_page' => $ordenes->currentPage(),
                    'per_page' => $ordenes->perPage(),
                    'fast' => $fastMode,
                    'include_ata' => $includeAta,
                    'include_counts' => $includeCounts,
                    'has_more_pages' => $ordenes->hasMorePages(),
                    'next_page_url' => $ordenes->nextPageUrl(),
                    'prev_page_url' => $ordenes->previousPageUrl(),
                ],
            ];
        });

        return response()->json($payload);
    }

    public function examples()
    {
        $payload = Cache::remember($this->cacheKey('examples'), now()->addMinutes(5), function () {
            $ordenes = Orden::query()
                ->select($this->summaryColumns())
                ->withCount($this->countRelations())
                ->with($this->showRelations())
                ->whereIn('folio', [
                    'CESA-TREN25-033',
                    'CESA-HANG26-016',
                ])
                ->orderBy('fecha')
                ->orderBy('folio')
                ->get();

            return [
                'success' => true,
                'message' => 'Ejemplos de orden obtenidos correctamente.',
                'data' => OrdenResource::collection($ordenes)->resolve(),
            ];
        });

        return response()->json($payload);
    }

    public function store(StoreOrdenRequest $request)
    {
        $this->authorizeNestedInventoryPricing($request, $request->validated('refacciones', []));
        $this->authorizeNestedInventoryPricing($request, $request->validated('consumibles', []));
        $this->authorizeNestedInventoryPricing($request, $request->validated('herramientas', []));

        $orden = DB::transaction(function () use ($request) {
            $data = $request->validated();
            $data['area_id'] = $this->effectiveAreaId($request) ?? $data['area_id'];
            $this->authorizeAreaId($request, $data['area_id']);
            $area = Area::findOrFail($data['area_id']);
            $folio = $this->ordenService->generarFolio($area);
            $this->mergeMotorContext($data);

            if (!empty($data['ata_subchapter_id']) && empty($data['ata_chapter_id'])) {
                $data['ata_chapter_id'] = AtaSubchapter::findOrFail($data['ata_subchapter_id'])->ata_chapter_id;
            }

            $orden = Orden::create(SchemaPayload::forModel(new Orden(), array_merge(
                Arr::except($data, $this->nestedKeys()),
                $folio,
                [
                    'fecha' => $data['fecha'] ?? now()->toDateString(),
                    'estado' => $data['estado'] ?? 'abierta',
                ]
            )));

            $this->syncRelatedCollections($orden, $data, (bool) ($data['generar_tareas_ata'] ?? true));

            return $orden->load($this->detailRelations());
        });

        $this->bustCache();

        return response()->json([
            'success' => true,
            'message' => 'Orden creada correctamente.',
            'data' => new OrdenResource($orden),
        ], 201);
    }

    public function show(Orden $ordene)
    {
        $this->authorizeModelArea(request(), $ordene);

        $payload = Cache::remember($this->cacheKey('show', ['id' => $ordene->id] + $this->areaCacheContext(request())), now()->addMinutes(5), function () use ($ordene) {
            $ordene->load($this->showRelations())->loadCount($this->countRelations());

            return [
                'success' => true,
                'message' => 'Orden encontrada.',
                'data' => (new OrdenResource($ordene))->resolve(),
            ];
        });

        return response()->json($payload);
    }

    public function update(UpdateOrdenRequest $request, Orden $ordene)
    {
        $this->authorizeModelArea($request, $ordene);
        $this->authorizeNestedInventoryPricing($request, $request->validated('refacciones', []));
        $this->authorizeNestedInventoryPricing($request, $request->validated('consumibles', []));
        $this->authorizeNestedInventoryPricing($request, $request->validated('herramientas', []));

        $orden = DB::transaction(function () use ($request, $ordene) {
            $data = $request->validated();
            if (array_key_exists('area_id', $data)) {
                $this->authorizeAreaId($request, $data['area_id']);
            } else {
                $data['area_id'] = $ordene->area_id;
            }
            $this->mergeMotorContext($data);

            if (!empty($data['ata_subchapter_id']) && empty($data['ata_chapter_id'])) {
                $data['ata_chapter_id'] = AtaSubchapter::findOrFail($data['ata_subchapter_id'])->ata_chapter_id;
            }

            $ordene->update(SchemaPayload::forModel($ordene, Arr::except($data, $this->nestedKeys())));
            $this->syncRelatedCollections($ordene, $data, false);

            return $ordene->load($this->detailRelations());
        });

        $this->bustCache();

        return response()->json([
            'success' => true,
            'message' => 'Orden actualizada correctamente.',
            'data' => new OrdenResource($orden),
        ]);
    }

    public function destroy(Orden $ordene)
    {
        $this->authorizeModelArea(request(), $ordene);
        $ordene->delete();
        $this->bustCache();

        return response()->json([
            'success' => true,
            'message' => 'Orden eliminada correctamente.',
        ]);
    }

    public function showCompleto(Orden $ordene)
    {
        $this->authorizeModelArea(request(), $ordene);

        $payload = Cache::remember($this->cacheKey('completo', ['id' => $ordene->id] + $this->areaCacheContext(request())), now()->addMinutes(5), function () use ($ordene) {
            return [
                'success' => true,
                'message' => 'Orden completa obtenida.',
                'data' => (new OrdenResource($ordene->load($this->detailRelations())))->resolve(),
            ];
        });

        return response()->json($payload);
    }

    private function syncRelatedCollections(Orden $orden, array $data, bool $generarTareasAta): void
    {
        if ($generarTareasAta && $orden->ata_subchapter_id) {
            $templates = AtaSubchapter::query()
                ->select('id')
                ->with(['tasks' => fn ($query) => $query->select([
                    'id',
                    'ata_subchapter_id',
                    'area_id',
                    'titulo',
                    'descripcion',
                    'tipo',
                    'tiempo_estimado_min',
                    'prioridad',
                ])])
                ->find($orden->ata_subchapter_id)?->tasks ?? collect();

            foreach ($templates as $index => $template) {
                $orden->tareas()->create([
                    'area_id' => $template->area_id,
                    'ata_task_template_id' => $template->id,
                    'titulo' => $template->titulo,
                    'descripcion' => $template->descripcion,
                    'orden' => $index + 1,
                    'tipo' => $template->tipo,
                    'prioridad' => $template->prioridad,
                    'tiempo_estimado_min' => $template->tiempo_estimado_min,
                    'estado' => 'pendiente',
                ]);
            }
        }

        $this->replaceCollection($orden, 'tareas', $data['tareas'] ?? null);
        $this->replaceCollection($orden, 'discrepancias', $data['discrepancias'] ?? null);
        $this->replaceCollection($orden, 'refacciones', $data['refacciones'] ?? null);
        $this->replaceCollection($orden, 'consumibles', $data['consumibles'] ?? null);
        $this->replaceCollection($orden, 'herramientas', $data['herramientas'] ?? null);
        $this->replaceCollection($orden, 'ndt', $data['ndt'] ?? null);
        $this->replaceCollection($orden, 'talleresExternos', $data['talleres_externos'] ?? null);
        $this->replaceCollection($orden, 'mediciones', $data['mediciones'] ?? null);
    }

    private function replaceCollection(Orden $orden, string $relation, ?array $items): void
    {
        if ($items === null) {
            return;
        }

        $orden->{$relation}()->delete();

        foreach ($items as $item) {
            $related = $orden->{$relation}()->getRelated();
            $orden->{$relation}()->create(SchemaPayload::forModel($related, $item));
        }
    }

    private function summaryColumnsWithJoins(bool $withAta): array
    {
        $columns = [
            'ordenes.id',
            'ordenes.area_id',
            'ordenes.tipo_id',
            'ordenes.user_id',
            'ordenes.ata_chapter_id',
            'ordenes.ata_subchapter_id',
            'ordenes.motor_id',
            'ordenes.folio',
            'ordenes.fecha',
            'ordenes.cliente',
            'ordenes.matricula',
            'ordenes.aeronave_modelo',
            'ordenes.aeronave_serie',
            'ordenes.tiempo_total',
            'ordenes.ciclos_totales',
            'ordenes.descripcion',
            'ordenes.trabajo_descripcion',
            'ordenes.componente_descripcion',
            'ordenes.componente_modelo',
            'ordenes.componente_numero_parte',
            'ordenes.componente_numero_serie',
            'ordenes.componente_tiempo_total',
            'ordenes.componente_ciclos_totales',
            'ordenes.tipo_tarea',
            'ordenes.intervalo',
            'ordenes.accion_correctiva',
            'ordenes.tecnico_responsable',
            'ordenes.inspector',
            'ordenes.fecha_inicio',
            'ordenes.fecha_termino',
            'ordenes.estado',
            'ordenes.created_at',
            'ordenes.updated_at',
            'area.codigo as area_codigo',
            'area.numero as area_numero',
            'area.nombre as area_nombre',
            'tipo.codigo as tipo_codigo',
            'tipo.nombre as tipo_nombre',
            'usuario.name as usuario_nombre',
            'usuario.email as usuario_email',
            'motor.aeronave_id as motor_aeronave_id',
            'motor.posicion as motor_posicion',
            'motor.fabricante as motor_fabricante',
            'motor.modelo as motor_modelo',
            'motor.numero_parte as motor_numero_parte',
            'motor.numero_serie as motor_numero_serie',
            'motor.tiempo_total as motor_tiempo_total',
            'motor.ciclos_totales as motor_ciclos_totales',
            'motor.estado as motor_estado',
            'aeronave.cliente as aeronave_cliente',
            'aeronave.matricula as aeronave_matricula',
            'aeronave.fabricante as aeronave_fabricante',
            'aeronave.modelo as aeronave_modelo_rel',
            'aeronave.numero_serie as aeronave_numero_serie_rel',
            'aeronave.estado as aeronave_estado',
        ];

        if (! $withAta) {
            return $columns;
        }

        return [
            ...$columns,
            'ata_chapter.codigo as ata_chapter_codigo',
            'ata_chapter.descripcion as ata_chapter_descripcion',
            'ata_subchapter.codigo as ata_subchapter_codigo',
            'ata_subchapter.descripcion as ata_subchapter_descripcion',
            'ata_subchapter.tipo_mantenimiento as ata_subchapter_tipo_mantenimiento',
            'ata_subchapter.intervalo_horas as ata_subchapter_intervalo_horas',
            'ata_subchapter.intervalo_ciclos as ata_subchapter_intervalo_ciclos',
            'ata_subchapter.intervalo_dias as ata_subchapter_intervalo_dias',
        ];
    }

    private function summaryColumns(): array
    {
        return [
            'id',
            'area_id',
            'tipo_id',
            'user_id',
            'ata_chapter_id',
            'ata_subchapter_id',
            'motor_id',
            'folio',
            'fecha',
            'cliente',
            'matricula',
            'aeronave_modelo',
            'aeronave_serie',
            'tiempo_total',
            'ciclos_totales',
            'descripcion',
            'trabajo_descripcion',
            'componente_descripcion',
            'componente_modelo',
            'componente_numero_parte',
            'componente_numero_serie',
            'componente_tiempo_total',
            'componente_ciclos_totales',
            'tipo_tarea',
            'intervalo',
            'accion_correctiva',
            'tecnico_responsable',
            'inspector',
            'fecha_inicio',
            'fecha_termino',
            'estado',
            'created_at',
            'updated_at',
        ];
    }

    private function detailRelations(): array
    {
        return [
            'area:id,codigo,numero,nombre',
            'tipo:id,codigo,nombre',
            'usuario:id,name,email',
            'ataChapter:id,codigo,descripcion',
            'ataSubchapter:id,ata_chapter_id,codigo,descripcion,tipo_mantenimiento,intervalo_horas,intervalo_ciclos,intervalo_dias',
            'motor:id,aeronave_id,posicion,fabricante,modelo,numero_parte,numero_serie,tiempo_total,ciclos_totales,estado',
            'motor.aeronave:id,cliente,matricula,fabricante,modelo,numero_serie,estado',
            'tareas' => fn ($query) => $query
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
                ->orderBy('orden')
                ->orderBy('id'),
            'tareas.plantillaAta:id,ata_subchapter_id,area_id,titulo,descripcion,tipo,tiempo_estimado_min,prioridad',
            'tareas.area:id,codigo,numero,nombre',
            'discrepancias',
            'refacciones',
            'consumibles',
            'herramientas',
            'ndt',
            'talleresExternos',
            'mediciones',
        ];
    }

    private function showRelations(): array
    {
        return [
            'area:id,codigo,numero,nombre',
            'tipo:id,codigo,nombre',
            'usuario:id,name,email',
            'ataChapter:id,codigo,descripcion',
            'ataSubchapter:id,ata_chapter_id,codigo,descripcion,tipo_mantenimiento,intervalo_horas,intervalo_ciclos,intervalo_dias',
            'motor:id,aeronave_id,posicion,fabricante,modelo,numero_parte,numero_serie,tiempo_total,ciclos_totales,estado',
            'motor.aeronave:id,cliente,matricula,fabricante,modelo,numero_serie,estado',
        ];
    }

    private function countRelations(): array
    {
        return [
            'tareas',
            'discrepancias',
            'refacciones',
            'consumibles',
            'herramientas',
            'ndt',
            'talleresExternos',
            'mediciones',
        ];
    }

    private function serializeIndexOrder(Orden $orden, bool $includeAta, bool $includeCounts): array
    {
        $payload = [
            'id' => $orden->id,
            'folio' => $orden->folio,
            'estado' => $orden->estado,
            'fecha' => $this->dateToString($orden->fecha),
            'cliente' => $orden->cliente,
            'matricula' => $orden->matricula,
            'aeronave_modelo' => $orden->aeronave_modelo,
            'aeronave_serie' => $orden->aeronave_serie,
            'tiempo_total' => $orden->tiempo_total,
            'ciclos_totales' => $orden->ciclos_totales,
            'descripcion' => $orden->descripcion,
            'trabajo_descripcion' => $orden->trabajo_descripcion,
            'componente_descripcion' => $orden->componente_descripcion,
            'componente_modelo' => $orden->componente_modelo,
            'componente_numero_parte' => $orden->componente_numero_parte,
            'componente_numero_serie' => $orden->componente_numero_serie,
            'componente_tiempo_total' => $orden->componente_tiempo_total,
            'componente_ciclos_totales' => $orden->componente_ciclos_totales,
            'tipo_tarea' => $orden->tipo_tarea,
            'intervalo' => $orden->intervalo,
            'accion_correctiva' => $orden->accion_correctiva,
            'tecnico_responsable' => $orden->tecnico_responsable,
            'inspector' => $orden->inspector,
            'fecha_inicio' => $this->dateToString($orden->fecha_inicio),
            'fecha_termino' => $this->dateToString($orden->fecha_termino),
            'area' => $orden->area_id ? [
                'id' => $orden->area_id,
                'codigo' => $orden->area_codigo,
                'numero' => $orden->area_numero,
                'nombre' => $orden->area_nombre,
            ] : null,
            'tipo' => $orden->tipo_id ? [
                'id' => $orden->tipo_id,
                'codigo' => $orden->tipo_codigo,
                'nombre' => $orden->tipo_nombre,
            ] : null,
            'usuario' => $orden->user_id ? [
                'id' => $orden->user_id,
                'nombre' => $orden->usuario_nombre,
                'email' => $orden->usuario_email,
            ] : null,
            'motor' => $orden->motor_id ? [
                'id' => $orden->motor_id,
                'posicion' => $orden->motor_posicion,
                'fabricante' => $orden->motor_fabricante,
                'modelo' => $orden->motor_modelo,
                'numero_parte' => $orden->motor_numero_parte,
                'numero_serie' => $orden->motor_numero_serie,
                'tiempo_total' => $orden->motor_tiempo_total,
                'ciclos_totales' => $orden->motor_ciclos_totales,
                'estado' => $orden->motor_estado,
                'aeronave' => $orden->motor_aeronave_id ? [
                    'id' => $orden->motor_aeronave_id,
                    'cliente' => $orden->aeronave_cliente,
                    'matricula' => $orden->aeronave_matricula,
                    'fabricante' => $orden->aeronave_fabricante,
                    'modelo' => $orden->aeronave_modelo_rel,
                    'numero_serie' => $orden->aeronave_numero_serie_rel,
                    'estado' => $orden->aeronave_estado,
                ] : null,
            ] : null,
            'created_at' => $orden->created_at,
            'updated_at' => $orden->updated_at,
        ];

        if ($includeAta) {
            $payload['ata'] = [
                'chapter' => $orden->ata_chapter_id ? [
                    'id' => $orden->ata_chapter_id,
                    'codigo' => $orden->ata_chapter_codigo,
                    'descripcion' => $orden->ata_chapter_descripcion,
                ] : null,
                'subchapter' => $orden->ata_subchapter_id ? [
                    'id' => $orden->ata_subchapter_id,
                    'codigo' => $orden->ata_subchapter_codigo,
                    'descripcion' => $orden->ata_subchapter_descripcion,
                    'tipo_mantenimiento' => $orden->ata_subchapter_tipo_mantenimiento,
                    'intervalo_horas' => $orden->ata_subchapter_intervalo_horas,
                    'intervalo_ciclos' => $orden->ata_subchapter_intervalo_ciclos,
                    'intervalo_dias' => $orden->ata_subchapter_intervalo_dias,
                ] : null,
            ];
        }

        if ($includeCounts) {
            $payload['tareas_count'] = $orden->tareas_count;
            $payload['discrepancias_count'] = $orden->discrepancias_count;
            $payload['refacciones_count'] = $orden->refacciones_count;
            $payload['consumibles_count'] = $orden->consumibles_count;
            $payload['herramientas_count'] = $orden->herramientas_count;
            $payload['ndt_count'] = $orden->ndt_count;
            $payload['talleres_externos_count'] = $orden->talleres_externos_count;
            $payload['mediciones_count'] = $orden->mediciones_count;
        }

        return $payload;
    }

    private function dateToString(mixed $value): ?string
    {
        return method_exists($value, 'toDateString') ? $value->toDateString() : null;
    }

    private function mergeMotorContext(array &$data): void
    {
        if (empty($data['motor_id'])) {
            return;
        }

        $motor = Motor::with('aeronave')->findOrFail($data['motor_id']);

        $data['componente_descripcion'] ??= 'Motor';
        $data['componente_modelo'] ??= $motor->modelo;
        $data['componente_numero_parte'] ??= $motor->numero_parte;
        $data['componente_numero_serie'] ??= $motor->numero_serie;
        $data['componente_tiempo_total'] ??= $motor->tiempo_total;
        $data['componente_ciclos_totales'] ??= $motor->ciclos_totales;

        if ($motor->aeronave) {
            $data['cliente'] ??= $motor->aeronave->cliente;
            $data['matricula'] ??= $motor->aeronave->matricula;
            $data['aeronave_modelo'] ??= $motor->aeronave->modelo;
            $data['aeronave_serie'] ??= $motor->aeronave->numero_serie;
        }
    }

    private function nestedKeys(): array
    {
        return [
            'generar_tareas_ata',
            'tareas',
            'discrepancias',
            'refacciones',
            'consumibles',
            'herramientas',
            'ndt',
            'talleres_externos',
            'mediciones',
        ];
    }

    private function cacheKey(string $action, array $params = []): string
    {
        ksort($params);

        return 'ordenes:' . Cache::get('ordenes_cache_version', 1) . ':' . $action . ':' . md5(json_encode($params));
    }

    private function bustCache(): void
    {
        Cache::forever('ordenes_cache_version', (int) Cache::get('ordenes_cache_version', 1) + 1);
        Cache::forever('motores_cache_version', (int) Cache::get('motores_cache_version', 1) + 1);
    }
}
