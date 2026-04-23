<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrdenRequest;
use App\Http\Requests\UpdateOrdenRequest;
use App\Http\Resources\OrdenResource;
use App\Models\Area;
use App\Models\AuditLog;
use App\Models\AtaSubchapter;
use App\Models\Motor;
use App\Models\Orden;
use App\Services\OrdenService;
use App\Support\SchemaPayload;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class OrdenController extends Controller
{
    public function __construct(private readonly OrdenService $ordenService)
    {
    }

    public function index(Request $request)
    {
        $payload = $this->cacheForeverOrFetch($this->cacheKey('index', array_merge($request->query(), $this->areaCacheContext($request))), function () use ($request) {
            $fastMode = ! $request->has('fast') || $request->boolean('fast');
            $includeAta = $request->boolean('include_ata');
            $includeCounts = $request->boolean('include_counts');
            $query = $this->applyAreaScope($request, Orden::query(), 'ordenes.area_id')
                ->leftJoin('areas as area', 'area.id', '=', 'ordenes.area_id')
                ->leftJoin('tipo_ordenes as tipo', 'tipo.id', '=', 'ordenes.tipo_id')
                ->leftJoin('users as usuario', 'usuario.id', '=', 'ordenes.user_id')
                ->select($this->summaryColumnsWithJoins($includeAta))
                ->with([
                    'area:id,codigo,numero,nombre',
                    'tipo:id,codigo,nombre',
                    'usuario:id,name,email',
                    'motor:id,aeronave_id,posicion,fabricante,modelo,numero_parte,numero_serie,tiempo_total,ciclos_totales,estado',
                    'motor.aeronave:id,cliente,matricula,fabricante,modelo,numero_serie,estado',
                ]);

            if ($includeAta) {
                $query
                    ->leftJoin('ata_chapters as ata_chapter', 'ata_chapter.id', '=', 'ordenes.ata_chapter_id')
                    ->leftJoin('ata_subchapters as ata_subchapter', 'ata_subchapter.id', '=', 'ordenes.ata_subchapter_id')
                    ->with([
                        'ataChapter:id,codigo,descripcion',
                        'ataSubchapter:id,ata_chapter_id,codigo,descripcion,tipo_mantenimiento,intervalo_horas,intervalo_ciclos,intervalo_dias',
                    ]);
            }

            if ($includeCounts) {
                $query->withCount($this->availableRelations($this->countRelations()));
            }

            $query
                ->when($request->filled('estado'), fn ($q) => $q->where('ordenes.estado', $request->string('estado')))
                ->when($request->filled('tipo_id'), fn ($q) => $q->where('ordenes.tipo_id', $request->integer('tipo_id')))
                ->when($request->filled('motor_id'), fn ($q) => $q->where('ordenes.motor_id', $request->integer('motor_id')))
                ->when($request->filled('aeronave_id'), fn ($q) => $q->whereHas('motor', fn ($motor) => $motor->where('aeronave_id', $request->integer('aeronave_id'))))
                ->when($request->filled('ata_chapter_id'), fn ($q) => $q->where('ordenes.ata_chapter_id', $request->integer('ata_chapter_id')))
                ->when($request->filled('ata_subchapter_id'), fn ($q) => $q->where('ordenes.ata_subchapter_id', $request->integer('ata_subchapter_id')))
                ->when($request->filled('folio'), fn ($q) => $this->applyIndexedPrefixSearch($q, 'ordenes.folio', $request->string('folio')))
                ->when($request->filled('matricula'), fn ($q) => $this->applyIndexedPrefixSearch($q, 'ordenes.matricula', $request->string('matricula')));

            $this->applyClientScope($request, $query, 'ordenes.cliente');

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
        $payload = $this->cacheOrFetch($this->cacheKey('examples'), now()->addMinutes(5), function () {
            $ordenes = Orden::query()
                ->select($this->summaryColumns())
                ->withCount($this->availableRelations($this->countRelations()))
                ->with($this->availableRelations($this->showRelations()))
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
        abort_if($this->isClienteUser($request), 403, 'Los clientes solo pueden consultar sus ordenes de trabajo.');
        $this->authorizeMiscelaneaAdministrativePayload($request);

        $this->authorizeNestedOperationalPayload($request, $request->validated('tareas', []), [
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
            'foto',
            'imagen',
            'image',
            'evidencia',
            'foto_base64',
            'imagen_base64',
            'evidencia_base64',
        ]);
        $this->authorizeNestedOperationalPayload($request, $request->validated('cartas', []), [
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
        ]);
        $this->authorizeNestedOperationalPayload($request, $request->validated('discrepancias', []), [
            'item',
            'descripcion',
            'accion_correctiva',
            'status',
            'inspector',
            'fecha_inicio',
            'fecha_termino',
            'horas_hombre',
            'imagen_path',
            'foto',
            'imagen',
            'image',
            'evidencia',
            'foto_base64',
            'imagen_base64',
            'evidencia_base64',
            'componente_numero_parte_off',
            'componente_numero_serie_off',
            'componente_numero_parte_on',
            'componente_numero_serie_on',
            'observaciones',
        ]);
        $this->authorizeNestedOperationalPayload($request, $request->validated('talleres_externos', []), [
            'item',
            'proveedor',
            'tarea',
            'cantidad',
            'sub_componente',
            'numero_parte',
            'numero_serie',
            'foto_path',
            'foto',
            'imagen',
            'image',
            'evidencia',
            'foto_base64',
            'imagen_base64',
            'evidencia_base64',
            'observaciones',
            'certificado',
            'envio_a',
            'recepcion',
            'trabajo_realizado',
        ]);
        $this->authorizeNestedInventoryPricing($request, $request->validated('refacciones', []));
        $this->authorizeNestedInventoryPricing($request, $request->validated('consumibles', []));
        $this->authorizeNestedInventoryPricing($request, $request->validated('herramientas', []));
        $this->authorizeNestedInventoryPricing($request, $request->validated('talleres_externos', []));

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

            return $orden;
        });

        $orden->load($this->availableRelations($this->saveResponseRelations()))->loadCount($this->availableRelations($this->saveResponseCountRelations()));

        $this->bustCache();

        return response()->json([
            'success' => true,
            'message' => 'Orden creada correctamente.',
            'data' => OrdenResource::make($orden),
        ], 201);
    }

    public function show(Orden $ordene)
    {
        $this->authorizeClientOrderAccess(request(), $ordene);
        $this->authorizeModelArea(request(), $ordene);

        $includeCounts = request()->boolean('include_counts');

        $payload = $this->cacheOrFetch($this->cacheKey('show', ['id' => $ordene->id, 'include_counts' => $includeCounts] + $this->areaCacheContext(request())), now()->addMinutes(5), function () use ($ordene, $includeCounts) {
            $ordene->load($this->availableRelations($this->showRelations()));

            if ($includeCounts) {
                $ordene->loadCount($this->availableRelations($this->countRelations()));
            }

            return [
                'success' => true,
                'message' => 'Orden encontrada.',
                'data' => OrdenResource::make($ordene)->resolve(),
                'meta' => [
                    'include_counts' => $includeCounts,
                ],
            ];
        });

        return response()->json($payload);
    }

    public function update(UpdateOrdenRequest $request, Orden $ordene)
    {
        abort_if($this->isClienteUser($request), 403, 'Los clientes solo pueden consultar sus ordenes de trabajo.');
        $this->authorizeModelArea($request, $ordene);
        $this->authorizeMiscelaneaAdministrativePayload($request);
        $this->authorizeNestedOperationalPayload($request, $request->validated('tareas', []), [
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
            'foto',
            'imagen',
            'image',
            'evidencia',
            'foto_base64',
            'imagen_base64',
            'evidencia_base64',
        ]);
        $this->authorizeNestedOperationalPayload($request, $request->validated('cartas', []), [
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
        ]);
        $this->authorizeNestedOperationalPayload($request, $request->validated('discrepancias', []), [
            'item',
            'descripcion',
            'accion_correctiva',
            'status',
            'inspector',
            'fecha_inicio',
            'fecha_termino',
            'horas_hombre',
            'imagen_path',
            'foto',
            'imagen',
            'image',
            'evidencia',
            'foto_base64',
            'imagen_base64',
            'evidencia_base64',
            'componente_numero_parte_off',
            'componente_numero_serie_off',
            'componente_numero_parte_on',
            'componente_numero_serie_on',
            'observaciones',
        ]);
        $this->authorizeNestedOperationalPayload($request, $request->validated('talleres_externos', []), [
            'item',
            'proveedor',
            'tarea',
            'cantidad',
            'sub_componente',
            'numero_parte',
            'numero_serie',
            'foto_path',
            'foto',
            'imagen',
            'image',
            'evidencia',
            'foto_base64',
            'imagen_base64',
            'evidencia_base64',
            'observaciones',
            'certificado',
            'envio_a',
            'recepcion',
            'trabajo_realizado',
        ]);
        $this->authorizeNestedInventoryPricing($request, $request->validated('refacciones', []));
        $this->authorizeNestedInventoryPricing($request, $request->validated('consumibles', []));
        $this->authorizeNestedInventoryPricing($request, $request->validated('herramientas', []));
        $this->authorizeNestedInventoryPricing($request, $request->validated('talleres_externos', []));

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

            return $ordene;
        });

        $orden->load($this->availableRelations($this->saveResponseRelations()))->loadCount($this->availableRelations($this->saveResponseCountRelations()));

        $this->bustCache();

        return response()->json([
            'success' => true,
            'message' => 'Orden actualizada correctamente.',
            'data' => OrdenResource::make($orden),
        ]);
    }

    public function destroy(Orden $ordene)
    {
        abort_if($this->isClienteUser(request()), 403, 'Los clientes solo pueden consultar sus ordenes de trabajo.');
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
        $this->authorizeClientOrderAccess(request(), $ordene);
        $this->authorizeModelArea(request(), $ordene);

        $payload = $this->cacheOrFetch($this->cacheKey('completo', ['id' => $ordene->id] + $this->areaCacheContext(request())), now()->addMinutes(5), function () use ($ordene) {
            return [
                'success' => true,
                'message' => 'Orden completa obtenida.',
                'data' => OrdenResource::make($ordene->load($this->availableRelations($this->detailRelations())))->resolve(),
            ];
        });

        return response()->json($payload);
    }

    public function showTraceability(Orden $ordene)
    {
        $this->authorizeClientOrderAccess(request(), $ordene);
        $this->authorizeModelArea(request(), $ordene);

        $payload = $this->cacheOrFetch(
            $this->cacheKey('trazabilidad', ['id' => $ordene->id] + $this->areaCacheContext(request())),
            now()->addMinutes(5),
            function () use ($ordene) {
                $ordene->load($this->availableRelations($this->detailRelations()))->loadCount($this->availableRelations($this->countRelations()));

                return [
                    'success' => true,
                    'message' => 'Trazabilidad obtenida correctamente.',
                    'data' => [
                        'orden' => $this->traceabilityOrderSummary($ordene),
                        'vinculos' => $this->traceabilityLinks($ordene),
                        'historial_componente' => $this->componentHistory($ordene),
                        'evidencias' => $this->evidenceTimeline($ordene),
                        'documentos_oficiales' => $this->officialDocuments($ordene),
                        'materiales' => $this->materialTraceability($ordene),
                        'auditoria' => $this->auditTrail($ordene),
                    ],
                ];
            }
        );

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
        if ($this->cartasTableAvailable()) {
            $this->replaceCollection($orden, 'cartas', $data['cartas'] ?? null);
        }
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
            $item = $this->normalizeCollectionItem($relation, $item);
            $orden->{$relation}()->create(SchemaPayload::forModel($related, $item));
        }
    }

    private function normalizeCollectionItem(string $relation, mixed $item): array
    {
        if (! is_array($item)) {
            return [];
        }

        $imageMappings = [
            'tareas' => ['foto_path', 'tareas', ['foto', 'imagen', 'image', 'evidencia', 'foto_base64', 'imagen_base64', 'evidencia_base64']],
            'discrepancias' => ['imagen_path', 'discrepancias', ['foto', 'imagen', 'image', 'evidencia', 'foto_base64', 'imagen_base64', 'evidencia_base64']],
            'refacciones' => ['certificado_conformidad_imagen', 'refacciones', ['certificado_conformidad_imagen', 'certificado_conformidad_imagen_base64', 'certificado_conformidad_foto', 'certificado_conformidad_imagen_file']],
            'ndt' => ['evidencia_path', 'ndt', ['foto', 'imagen', 'image', 'evidencia', 'foto_base64', 'imagen_base64', 'evidencia_base64']],
            'talleresExternos' => ['foto_path', 'talleres-externos', ['foto', 'imagen', 'image', 'evidencia', 'foto_base64', 'imagen_base64', 'evidencia_base64']],
            'mediciones' => ['imagen_path', 'mediciones', ['foto', 'imagen', 'image', 'evidencia', 'foto_base64', 'imagen_base64', 'evidencia_base64']],
        ];

        if (array_key_exists($relation, $imageMappings)) {
            [$targetKey, $directory, $aliases] = $imageMappings[$relation];
            $this->storeIncomingImageFromData(
                $item,
                $targetKey,
                $directory,
                $aliases,
                requireCloudinary: in_array($relation, ['ndt', 'refacciones'], true)
            );
        }

        if ($relation === 'discrepancias') {
            $tecnico = trim((string) ($item['tecnico'] ?? ''));
            if ($tecnico === '') {
                $userName = trim((string) (request()->user()?->name ?? ''));
                if ($userName !== '') {
                    $item['tecnico'] = $userName;
                }
            } else {
                $item['tecnico'] = $tecnico;
            }
        }

        return $item;
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
            'cartas',
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
            'cartas',
            'motor:id,aeronave_id,posicion,fabricante,modelo,numero_parte,numero_serie,tiempo_total,ciclos_totales,estado',
            'motor.aeronave:id,cliente,matricula,fabricante,modelo,numero_serie,estado',
        ];
    }

    private function saveResponseRelations(): array
    {
        return [
            ...$this->showRelations(),
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
            'cartas',
            'discrepancias',
            'refacciones',
            'consumibles',
            'herramientas',
            'ndt',
            'talleresExternos',
        ];
    }

    private function countRelations(): array
    {
        return [
            'tareas',
            'cartas',
            'discrepancias',
            'refacciones',
            'consumibles',
            'herramientas',
            'ndt',
            'talleresExternos',
            'mediciones',
        ];
    }

    private function saveResponseCountRelations(): array
    {
        return [
            'tareas',
            'cartas',
            'discrepancias',
            'refacciones',
            'consumibles',
            'herramientas',
            'ndt',
            'talleresExternos',
        ];
    }

    private function availableRelations(array $relations): array
    {
        $available = [];

        foreach ($relations as $key => $definition) {
            if ($definition === 'cartas' || $key === 'cartas') {
                if (! $this->cartasTableAvailable()) {
                    continue;
                }
            }

            if (is_int($key)) {
                $available[] = $definition;

                continue;
            }

            $available[$key] = $definition;
        }

        return $available;
    }

    private function cartasTableAvailable(): bool
    {
        return Schema::hasTable('cartas');
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
            'area' => $orden->area ? [
                'id' => $orden->area->id,
                'codigo' => $orden->area->codigo,
                'numero' => $orden->area->numero,
                'nombre' => $orden->area->nombre,
            ] : null,
            'tipo' => $orden->tipo ? [
                'id' => $orden->tipo->id,
                'codigo' => $orden->tipo->codigo,
                'nombre' => $orden->tipo->nombre,
            ] : null,
            'usuario' => $orden->usuario ? [
                'id' => $orden->usuario->id,
                'nombre' => $orden->usuario->name,
                'email' => $orden->usuario->email,
            ] : null,
            'motor' => $orden->motor ? [
                'id' => $orden->motor->id,
                'posicion' => $orden->motor->posicion,
                'fabricante' => $orden->motor->fabricante,
                'modelo' => $orden->motor->modelo,
                'numero_parte' => $orden->motor->numero_parte,
                'numero_serie' => $orden->motor->numero_serie,
                'tiempo_total' => $orden->motor->tiempo_total,
                'ciclos_totales' => $orden->motor->ciclos_totales,
                'estado' => $orden->motor->estado,
                'aeronave' => $orden->motor->aeronave ? [
                    'id' => $orden->motor->aeronave->id,
                    'cliente' => $orden->motor->aeronave->cliente,
                    'matricula' => $orden->motor->aeronave->matricula,
                    'fabricante' => $orden->motor->aeronave->fabricante,
                    'modelo' => $orden->motor->aeronave->modelo,
                    'numero_serie' => $orden->motor->aeronave->numero_serie,
                    'estado' => $orden->motor->aeronave->estado,
                ] : null,
            ] : null,
            'created_at' => $orden->created_at,
            'updated_at' => $orden->updated_at,
            'cartas_enabled' => $this->cartasTableAvailable(),
        ];

        if ($includeAta) {
            $payload['ata'] = [
                'chapter' => $orden->ataChapter ? [
                    'id' => $orden->ataChapter->id,
                    'codigo' => $orden->ataChapter->codigo,
                    'descripcion' => $orden->ataChapter->descripcion,
                ] : null,
                'subchapter' => $orden->ataSubchapter ? [
                    'id' => $orden->ataSubchapter->id,
                    'codigo' => $orden->ataSubchapter->codigo,
                    'descripcion' => $orden->ataSubchapter->descripcion,
                    'tipo_mantenimiento' => $orden->ataSubchapter->tipo_mantenimiento,
                    'intervalo_horas' => $orden->ataSubchapter->intervalo_horas,
                    'intervalo_ciclos' => $orden->ataSubchapter->intervalo_ciclos,
                    'intervalo_dias' => $orden->ataSubchapter->intervalo_dias,
                ] : null,
            ];
        }

        if ($includeCounts) {
            $payload['tareas_count'] = $orden->tareas_count;
            $payload['cartas_count'] = $orden->cartas_count;
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
        return is_object($value) && method_exists($value, 'toDateString')
            ? $value->toDateString()
            : null;
    }

    private function authorizeMiscelaneaAdministrativePayload(Request $request): void
    {
        foreach ([
            'miscelanea_costo_total',
            'miscelanea_precio_venta',
            'miscelanea_observaciones_admin',
        ] as $key) {
            if (! $request->exists($key)) {
                continue;
            }

            $value = $request->input($key);
            $hasValue = is_string($value) ? trim($value) !== '' : $value !== null;

            if ($hasValue) {
                $this->authorizeExclusiveAdministracionEmail(
                    $request,
                    'Solo administracion@redaviation.com puede capturar costos y observaciones administrativas de miscelanea.',
                );

                return;
            }
        }
    }

    private function traceabilityOrderSummary(Orden $orden): array
    {
        return [
            'id' => $orden->id,
            'folio' => $orden->folio,
            'estado' => $orden->estado,
            'fecha' => $this->dateToString($orden->fecha),
            'fecha_inicio' => $this->dateToString($orden->fecha_inicio),
            'fecha_termino' => $this->dateToString($orden->fecha_termino),
            'descripcion' => $orden->descripcion,
            'trabajo_descripcion' => $orden->trabajo_descripcion,
            'accion_correctiva' => $orden->accion_correctiva,
            'tecnico_responsable' => $orden->tecnico_responsable,
            'inspector' => $orden->inspector,
            'cliente' => $orden->cliente,
            'matricula' => $orden->matricula,
            'aeronave_modelo' => $orden->aeronave_modelo,
            'aeronave_serie' => $orden->aeronave_serie,
            'componente_descripcion' => $orden->componente_descripcion,
            'componente_modelo' => $orden->componente_modelo,
            'componente_numero_parte' => $orden->componente_numero_parte,
            'componente_numero_serie' => $orden->componente_numero_serie,
            'tareas_count' => $orden->tareas_count,
            'discrepancias_count' => $orden->discrepancias_count,
            'refacciones_count' => $orden->refacciones_count,
            'consumibles_count' => $orden->consumibles_count,
            'herramientas_count' => $orden->herramientas_count,
            'ndt_count' => $orden->ndt_count,
            'talleres_externos_count' => $orden->talleres_externos_count,
            'mediciones_count' => $orden->mediciones_count,
        ];
    }

    private function traceabilityLinks(Orden $orden): array
    {
        return [
            'area' => $orden->area ? [
                'id' => $orden->area->id,
                'codigo' => $orden->area->codigo,
                'nombre' => $orden->area->nombre,
            ] : null,
            'usuario' => $orden->usuario ? [
                'id' => $orden->usuario->id,
                'nombre' => $orden->usuario->name,
                'email' => $orden->usuario->email,
            ] : null,
            'motor' => $orden->motor ? [
                'id' => $orden->motor->id,
                'posicion' => $orden->motor->posicion,
                'fabricante' => $orden->motor->fabricante,
                'modelo' => $orden->motor->modelo,
                'numero_parte' => $orden->motor->numero_parte,
                'numero_serie' => $orden->motor->numero_serie,
                'estado' => $orden->motor->estado,
            ] : null,
            'aeronave' => $orden->motor?->aeronave ? [
                'id' => $orden->motor->aeronave->id,
                'cliente' => $orden->motor->aeronave->cliente,
                'matricula' => $orden->motor->aeronave->matricula,
                'fabricante' => $orden->motor->aeronave->fabricante,
                'modelo' => $orden->motor->aeronave->modelo,
                'numero_serie' => $orden->motor->aeronave->numero_serie,
                'estado' => $orden->motor->aeronave->estado,
            ] : null,
        ];
    }

    private function componentHistory(Orden $orden): array
    {
        $query = Orden::query()
            ->with(['area:id,codigo,nombre', 'usuario:id,name,email'])
            ->whereKeyNot($orden->id)
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->limit(12);

        if ($orden->motor_id) {
            $query->where('motor_id', $orden->motor_id);
        } elseif ($orden->componente_numero_serie || $orden->componente_numero_parte) {
            $query->where(function ($nested) use ($orden) {
                if ($orden->componente_numero_serie) {
                    $nested->orWhere('componente_numero_serie', $orden->componente_numero_serie);
                }
                if ($orden->componente_numero_parte) {
                    $nested->orWhere('componente_numero_parte', $orden->componente_numero_parte);
                }
            });
        } elseif ($orden->matricula) {
            $query->where('matricula', $orden->matricula);
        } else {
            return [];
        }

        return $query->get()->map(function (Orden $item) {
            return [
                'id' => $item->id,
                'folio' => $item->folio,
                'estado' => $item->estado,
                'fecha' => $this->dateToString($item->fecha),
                'descripcion' => $item->descripcion,
                'area' => $item->area ? $item->area->nombre : null,
                'usuario' => $item->usuario ? $item->usuario->name : null,
                'componente_numero_parte' => $item->componente_numero_parte,
                'componente_numero_serie' => $item->componente_numero_serie,
            ];
        })->values()->all();
    }

    private function evidenceTimeline(Orden $orden): array
    {
        $entries = [];

        foreach ($orden->tareas as $item) {
            $url = $this->publicFileUrl($item->foto_path);
            if ($url) {
                $entries[] = [
                    'tipo' => 'tarea',
                    'titulo' => $item->titulo ?: 'Tarea sin titulo',
                    'detalle' => $item->descripcion,
                    'url' => $url,
                    'fecha' => optional($item->updated_at)->toIso8601String(),
                ];
            }
        }

        foreach ($orden->discrepancias as $item) {
            $url = $this->publicFileUrl($item->imagen_path);
            if ($url) {
                $entries[] = [
                    'tipo' => 'discrepancia',
                    'titulo' => $item->item ?: 'Discrepancia',
                    'detalle' => $item->descripcion,
                    'url' => $url,
                    'fecha' => optional($item->updated_at)->toIso8601String(),
                ];
            }
        }

        foreach ($orden->ndt as $item) {
            $url = $this->publicFileUrl($item->evidencia_path);
            if ($url) {
                $entries[] = [
                    'tipo' => 'ndt',
                    'titulo' => $item->tipo_prueba ?: 'NDT',
                    'detalle' => $item->resultado,
                    'url' => $url,
                    'fecha' => optional($item->updated_at)->toIso8601String(),
                ];
            }
        }

        foreach ($orden->talleresExternos as $item) {
            $url = $this->publicFileUrl($item->foto_path);
            if ($url) {
                $entries[] = [
                    'tipo' => 'taller_externo',
                    'titulo' => $item->proveedor ?: 'Taller externo',
                    'detalle' => $item->trabajo_realizado ?: $item->observaciones,
                    'url' => $url,
                    'fecha' => optional($item->updated_at)->toIso8601String(),
                ];
            }
        }

        foreach ($orden->mediciones as $item) {
            $url = $this->publicFileUrl($item->imagen_path);
            if ($url) {
                $entries[] = [
                    'tipo' => 'medicion',
                    'titulo' => $item->parametro ?: 'Medicion',
                    'detalle' => $item->descripcion,
                    'url' => $url,
                    'fecha' => optional($item->updated_at)->toIso8601String(),
                ];
            }
        }

        usort($entries, fn (array $a, array $b) => strcmp((string) ($b['fecha'] ?? ''), (string) ($a['fecha'] ?? '')));

        return $entries;
    }

    private function officialDocuments(Orden $orden): array
    {
        $documents = [];

        foreach ($orden->refacciones as $item) {
            $url = $this->publicFileUrl($item->certificado_conformidad_imagen);
            if (($item->certificado_conformidad ?? null) || $url) {
                $documents[] = [
                    'tipo' => 'refaccion',
                    'titulo' => $item->certificado_conformidad ?: 'Certificado de conformidad',
                    'referencia' => $item->numero_parte ?: $item->descripcion,
                    'url' => $url,
                ];
            }
        }

        foreach ($orden->ndt as $item) {
            if (($item->certificado ?? null) || ($item->seccion_manual ?? null)) {
                $documents[] = [
                    'tipo' => 'ndt',
                    'titulo' => $item->certificado ?: 'Certificado NDT',
                    'referencia' => $item->seccion_manual ?: $item->tipo_prueba,
                    'url' => $this->publicFileUrl($item->evidencia_path),
                ];
            }
        }

        foreach ($orden->talleresExternos as $item) {
            if (($item->certificado ?? null) || ($item->proveedor ?? null)) {
                $documents[] = [
                    'tipo' => 'taller_externo',
                    'titulo' => $item->certificado ?: 'Certificado de taller externo',
                    'referencia' => $item->proveedor ?: $item->trabajo_realizado,
                    'url' => $this->publicFileUrl($item->foto_path),
                ];
            }
        }

        foreach ($orden->mediciones as $item) {
            if (($item->manual_od ?? null) || ($item->manual_id ?? null)) {
                $documents[] = [
                    'tipo' => 'medicion',
                    'titulo' => 'Referencia de medicion',
                    'referencia' => trim(implode(' | ', array_filter([
                        $item->manual_od ? 'Manual OD ' . $item->manual_od : null,
                        $item->manual_id ? 'Manual ID ' . $item->manual_id : null,
                    ]))),
                    'url' => $this->publicFileUrl($item->imagen_path),
                ];
            }
        }

        return $documents;
    }

    private function materialTraceability(Orden $orden): array
    {
        return [
            'refacciones' => $orden->refacciones->map(function ($item) {
                return [
                    'descripcion' => $item->descripcion,
                    'nombre' => $item->nombre,
                    'numero_parte' => $item->numero_parte,
                    'cantidad' => $item->cantidad,
                    'certificado' => $item->certificado_conformidad,
                    'certificado_url' => $this->publicFileUrl($item->certificado_conformidad_imagen),
                    'status' => $item->status,
                ];
            })->values()->all(),
            'consumibles' => $orden->consumibles->map(function ($item) {
                return [
                    'descripcion' => $item->descripcion,
                    'nombre' => $item->nombre,
                    'numero_parte' => $item->numero_parte,
                    'cantidad' => $item->cantidad,
                    'certificado' => $item->certificado_conformidad,
                    'status' => $item->status,
                ];
            })->values()->all(),
            'herramientas' => $orden->herramientas->map(function ($item) {
                return [
                    'descripcion' => $item->descripcion,
                    'nombre' => $item->nombre,
                    'numero_parte' => $item->numero_parte,
                    'cantidad' => $item->cantidad,
                    'certificado' => $item->certificado_conformidad,
                    'status' => $item->status,
                ];
            })->values()->all(),
        ];
    }

    private function auditTrail(Orden $orden): array
    {
        return AuditLog::query()
            ->with('user:id,name,email')
            ->where(function ($query) use ($orden) {
                $query->where('order_id', $orden->id)
                    ->orWhere(function ($nested) use ($orden) {
                        $nested->where('entity_type', 'orden')
                            ->where('entity_id', $orden->id);
                    });
            })
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->limit(20)
            ->get()
            ->map(function (AuditLog $log) {
                return [
                    'id' => $log->id,
                    'action' => $log->action,
                    'description' => $log->description,
                    'entity_type' => $log->entity_type,
                    'entity_label' => $log->entity_label,
                    'occurred_at' => optional($log->occurred_at)->toIso8601String(),
                    'user' => $log->user ? [
                        'id' => $log->user->id,
                        'name' => $log->user->name,
                        'email' => $log->user->email,
                    ] : null,
                ];
            })->values()->all();
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

    private function applyClientScope(Request $request, \Illuminate\Database\Eloquent\Builder $query, string $column): void
    {
        if (! $this->isClienteUser($request)) {
            return;
        }

        $names = $this->currentClientNames($request);

        if ($names === []) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->where(function ($builder) use ($column, $names) {
            foreach ($names as $name) {
                $builder->orWhere($column, $name);
            }
        });
    }

    private function authorizeClientOrderAccess(Request $request, Orden $ordene): void
    {
        if (! $this->isClienteUser($request)) {
            return;
        }

        $names = array_map('mb_strtolower', $this->currentClientNames($request));
        $orderClient = mb_strtolower(trim((string) $ordene->cliente));

        abort_unless($orderClient !== '' && in_array($orderClient, $names, true), 403, 'No autorizado para consultar esta orden.');
    }

    private function nestedKeys(): array
    {
        return [
            'generar_tareas_ata',
            'tareas',
            'cartas',
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

        return 'ordenes:' . Cache::get('ordenes_cache_version', 2) . ':' . $action . ':' . md5(json_encode($params));
    }

    private function bustCache(): void
    {
        Cache::forever('ordenes_cache_version', (int) Cache::get('ordenes_cache_version', 1) + 1);
        Cache::forever('motores_cache_version', (int) Cache::get('motores_cache_version', 1) + 1);
        Cache::forever('dashboard_cache_version', (int) Cache::get('dashboard_cache_version', 1) + 1);
    }
}


