<?php

namespace App\Http\Controllers;

use App\Models\Orden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminDashboardController extends Controller
{
    private const CACHE_SCHEMA_VERSION = 7;

    public function resumen(Request $request)
    {
        $payload = $this->cacheForeverOrFetch(
            $this->cacheKey($request),
            fn () => $this->buildResumenPayload($request)
        );

        return response()->json($payload);
    }

    private function buildResumenPayload(Request $request): array
    {
        $hasMiscCostColumn = Schema::hasColumn('ordenes', 'miscelanea_costo_total');
        $hasMiscSaleColumn = Schema::hasColumn('ordenes', 'miscelanea_precio_venta');

        $ordenes = $this->applyAreaScope($request, Orden::query(), 'ordenes.area_id')
            ->leftJoin('areas as area', 'area.id', '=', 'ordenes.area_id')
            ->select([
                'ordenes.id',
                'ordenes.area_id',
                'ordenes.folio',
                'ordenes.cliente',
                'ordenes.matricula',
                'ordenes.fecha_inicio',
                'ordenes.estado',
                'ordenes.created_at',
                DB::raw(
                    $hasMiscCostColumn
                        ? 'ordenes.miscelanea_costo_total'
                        : '0 as miscelanea_costo_total'
                ),
                DB::raw(
                    $hasMiscSaleColumn
                        ? 'ordenes.miscelanea_precio_venta'
                        : '0 as miscelanea_precio_venta'
                ),
                DB::raw("COALESCE(NULLIF(TRIM(area.codigo), ''), NULLIF(TRIM(area.nombre), ''), 'Sin area') as area_label"),
            ])
            ->get();

        $ordenIdsQuery = $this->dashboardOrdersIdsQuery($request);
        $refacciones = $this->aggregateItemsByOrder('refacciones', $ordenIdsQuery, true, true, true, true);
        $consumibles = $this->aggregateItemsByOrder('consumibles', $ordenIdsQuery, true, true, true, true);
        $talleres = $this->aggregateItemsByOrder('taller_externos', $ordenIdsQuery, false, false, true, false, 'costo');
        $ndt = $this->aggregateItemsByOrder('ndt', $ordenIdsQuery, false, false, true, false);
        $discrepancias = DB::table('discrepancias')
            ->joinSub($ordenIdsQuery, 'ordenes', fn ($join) => $join->on('discrepancias.orden_id', '=', 'ordenes.id'))
            ->select([
                'discrepancias.orden_id',
                'discrepancias.id',
                'discrepancias.tecnico',
                'discrepancias.horas_hombre',
            ])
            ->orderBy('discrepancias.orden_id')
            ->orderBy('discrepancias.id')
            ->get()
            ->groupBy('orden_id')
            ->map(fn (Collection $rows) => (object) [
                'horas_hombre_sum' => $rows->sum(fn ($row) => (float) ($row->horas_hombre ?? 0)),
                'tecnico_resumen' => $rows
                    ->map(fn ($row) => trim((string) ($row->tecnico ?? '')))
                    ->filter()
                    ->unique()
                    ->implode(' | '),
            ]);
        $tareas = DB::table('tareas')
            ->joinSub($ordenIdsQuery, 'ordenes', fn ($join) => $join->on('tareas.orden_id', '=', 'ordenes.id'))
            ->select('tareas.orden_id', DB::raw('COALESCE(SUM(tiempo_estimado_min), 0) as minutos_sum'))
            ->groupBy('tareas.orden_id')
            ->get()
            ->keyBy('orden_id');
        $tareasTecnicos = DB::table('tareas')
            ->joinSub($ordenIdsQuery, 'ordenes', fn ($join) => $join->on('tareas.orden_id', '=', 'ordenes.id'))
            ->select([
                'tareas.orden_id',
                'tareas.tecnico',
                DB::raw('COALESCE(SUM(tiempo_estimado_min), 0) as minutos_sum'),
            ])
            ->whereRaw("TRIM(COALESCE(tareas.tecnico, '')) <> ''")
            ->groupBy('tareas.orden_id', 'tareas.tecnico')
            ->orderBy('tareas.orden_id')
            ->orderByDesc('minutos_sum')
            ->get()
            ->groupBy('orden_id');
        $herramientas = DB::table('herramientas')
            ->joinSub($ordenIdsQuery, 'ordenes', fn ($join) => $join->on('herramientas.orden_id', '=', 'ordenes.id'))
            ->select('herramientas.orden_id', DB::raw('COUNT(*) as records_count'))
            ->groupBy('herramientas.orden_id')
            ->get()
            ->keyBy('orden_id');
        $proveedores = (int) DB::table('taller_externos')
            ->joinSub($ordenIdsQuery, 'ordenes', fn ($join) => $join->on('taller_externos.orden_id', '=', 'ordenes.id'))
            ->whereRaw("TRIM(COALESCE(proveedor, '')) <> ''")
            ->distinct('proveedor')
            ->count('proveedor');

        $now = now();
        $metrics = [
            'existencias' => 0,
            'refacciones_registros' => 0,
            'consumibles_registros' => 0,
            'talleres_registros' => 0,
            'ndt_registros' => 0,
            'reservas' => 0,
            'entradas' => 0,
            'salidas' => 0,
            'transferencias' => 0,
            'requisiciones' => 0,
            'recepciones' => 0,
            'ot_con_compra' => 0,
            'cotizadas' => 0,
            'en_aprobacion' => 0,
            'por_facturar' => 0,
            'viaticos' => 0,
            'horas_hombre' => 0.0,
            'costo_refacciones' => 0.0,
            'costo_consumibles' => 0.0,
            'costo_talleres' => 0.0,
            'costo_miscelanea' => 0.0,
            'venta_total' => 0.0,
            'por_cobrar_monto' => 0.0,
            'costo_periodo' => 0.0,
        ];
        $porCliente = [];
        $porMatricula = [];
        $porArea = [];
        $porOt = [];
        $topOtRows = [];

        foreach ($ordenes as $orden) {
            $activa = in_array(strtolower(trim((string) $orden->estado)), ['abierta', 'proceso'], true);
            $refaccion = $refacciones->get($orden->id);
            $consumible = $consumibles->get($orden->id);
            $taller = $talleres->get($orden->id);
            $ndtItem = $ndt->get($orden->id);
            $discrepancia = $discrepancias->get($orden->id);
            $tarea = $tareas->get($orden->id);
            $tareaTecnicos = $tareasTecnicos->get($orden->id, collect());
            $herramienta = $herramientas->get($orden->id);

            $refCount = (int) ($refaccion->records_count ?? 0);
            $consCount = (int) ($consumible->records_count ?? 0);
            $tallCount = (int) ($taller->records_count ?? 0);
            $ndtCount = (int) ($ndtItem->records_count ?? 0);
            $herrCount = (int) ($herramienta->records_count ?? 0);

            $metrics['refacciones_registros'] += $refCount;
            $metrics['consumibles_registros'] += $consCount;
            $metrics['talleres_registros'] += $tallCount;
            $metrics['ndt_registros'] += $ndtCount;

            $costoRef = (float) ($refaccion->cost_sum ?? 0);
            $costoCon = (float) ($consumible->cost_sum ?? 0);
            $costoTall = (float) ($taller->cost_sum ?? 0);
            $costoNdt = (float) ($ndtItem->cost_sum ?? 0);
            $costoMiscelanea = (float) ($orden->miscelanea_costo_total ?? 0);
            $ventaOt = (float) ($refaccion->sale_sum ?? 0)
                + (float) ($consumible->sale_sum ?? 0)
                + (float) ($taller->sale_sum ?? 0)
                + (float) ($ndtItem->sale_sum ?? 0)
                + (float) ($orden->miscelanea_precio_venta ?? 0);
            $costoOt = $costoRef + $costoCon + $costoTall + $costoNdt + $costoMiscelanea;

            $metrics['existencias'] += $refCount + $consCount + $herrCount;
            $metrics['reservas'] += $activa ? $refCount + $consCount : 0;
            $metrics['entradas'] += (int) ($refaccion->received_count ?? 0)
                + (int) ($consumible->received_count ?? 0)
                + (int) ($taller->received_count ?? 0)
                + (int) ($ndtItem->received_count ?? 0);
            $metrics['recepciones'] += (int) ($refaccion->received_count ?? 0)
                + (int) ($consumible->received_count ?? 0)
                + (int) ($taller->received_count ?? 0)
                + (int) ($ndtItem->received_count ?? 0);
            $metrics['salidas'] += (int) ($refaccion->quantity_sum ?? 0) + (int) ($consumible->quantity_sum ?? 0);
            $metrics['transferencias'] += (int) ($refaccion->transfer_count ?? 0)
                + (int) ($consumible->transfer_count ?? 0);
            $metrics['requisiciones'] += (int) ($refaccion->request_count ?? 0)
                + (int) ($consumible->request_count ?? 0);
            if ($refCount > 0 || $consCount > 0 || $tallCount > 0 || $ndtCount > 0) {
                $metrics['ot_con_compra']++;
            }

            $metrics['horas_hombre'] += (float) ($discrepancia->horas_hombre_sum ?? 0);
            $metrics['costo_refacciones'] += $costoRef;
            $metrics['costo_consumibles'] += $costoCon;
            $metrics['costo_talleres'] += $costoTall;
            $metrics['costo_miscelanea'] += $costoMiscelanea;
            $metrics['venta_total'] += $ventaOt;

            if ($ventaOt > 0) {
                $metrics['cotizadas']++;
                if (strtolower(trim((string) $orden->estado)) === 'cerrada') {
                    $metrics['por_facturar']++;
                    $metrics['por_cobrar_monto'] += $ventaOt;
                } else {
                    $metrics['en_aprobacion']++;
                }
            }

            $cliente = $this->safeText($orden->cliente, 'Sin cliente');
            $matricula = $this->safeText($orden->matricula, 'Sin matricula');
            $area = $this->safeText($orden->area_label, 'Sin area');
            $folio = $this->safeText($orden->folio, 'Sin OT');
            $margenOt = $ventaOt - $costoOt;
            $horasTrabajadas = round(((float) ($tarea->minutos_sum ?? 0)) / 60, 2);
            $tecnicoPrincipal = $this->safeText(
                $tareaTecnicos->first()->tecnico ?? $discrepancia->tecnico_resumen ?? null,
                'Sin tecnico'
            );

            $porCliente[$cliente] = ($porCliente[$cliente] ?? 0) + $costoOt;
            $porMatricula[$matricula] = ($porMatricula[$matricula] ?? 0) + $costoOt;
            $porArea[$area] = ($porArea[$area] ?? 0) + $costoOt;
            $porOt[$folio] = ($porOt[$folio] ?? 0) + $costoOt;
            $topOtRows[] = [
                'id' => $orden->id,
                'orden_id' => $orden->id,
                'folio' => $folio,
                'cliente' => $cliente,
                'matricula' => $matricula,
                'area' => $area,
                'estado' => $this->safeText($orden->estado, 'Sin estado'),
                'tecnico' => $tecnicoPrincipal,
                'horas_trabajadas' => $horasTrabajadas,
                'horas_labor_discrepancias' => round((float) ($discrepancia->horas_hombre_sum ?? 0), 2),
                'costo' => round($costoOt, 2),
                'venta' => round($ventaOt, 2),
                'margen' => round($margenOt, 2),
            ];

            $fecha = $orden->fecha_inicio ?? $orden->created_at;
            if ($fecha && $fecha->year === $now->year && $fecha->month === $now->month) {
                $metrics['costo_periodo'] += $costoOt;
            }
        }

        $costoTotal = $metrics['costo_refacciones']
            + $metrics['costo_consumibles']
            + $metrics['costo_talleres']
            + $metrics['costo_miscelanea'];

        [$topCliente, $topClienteCosto] = $this->topEntry($porCliente);
        [$topMatricula, $topMatriculaCosto] = $this->topEntry($porMatricula);
        [$topArea, $topAreaCosto] = $this->topEntry($porArea);
        [$topOt, $topOtCosto] = $this->topEntry($porOt);

        return [
            'success' => true,
            'message' => 'Resumen administrativo obtenido correctamente.',
            'data' => [
                'total_ordenes' => $ordenes->count(),
                'existencias' => $metrics['existencias'],
                'refacciones_registros' => $metrics['refacciones_registros'],
                'consumibles_registros' => $metrics['consumibles_registros'],
                'talleres_registros' => $metrics['talleres_registros'],
                'ndt_registros' => $metrics['ndt_registros'],
                'reservas' => $metrics['reservas'],
                'entradas' => $metrics['entradas'],
                'salidas' => $metrics['salidas'],
                'transferencias' => $metrics['transferencias'],
                'kardex' => $metrics['entradas'] + $metrics['salidas'] + $metrics['transferencias'],
                'requisiciones' => $metrics['requisiciones'],
                'recepciones' => $metrics['recepciones'],
                'proveedores' => $proveedores,
                'ot_con_compra' => $metrics['ot_con_compra'],
                'cotizadas' => $metrics['cotizadas'],
                'en_aprobacion' => $metrics['en_aprobacion'],
                'por_facturar' => $metrics['por_facturar'],
                'viaticos' => $metrics['viaticos'],
                'horas_hombre' => round($metrics['horas_hombre'], 2),
                'costo_refacciones' => round($metrics['costo_refacciones'], 2),
                'costo_consumibles' => round($metrics['costo_consumibles'], 2),
                'costo_talleres' => round($metrics['costo_talleres'], 2),
                'costo_miscelanea' => round($metrics['costo_miscelanea'], 2),
                'costo_total' => round($costoTotal, 2),
                'venta_total' => round($metrics['venta_total'], 2),
                'margen' => round($metrics['venta_total'] - $costoTotal, 2),
                'por_cobrar_monto' => round($metrics['por_cobrar_monto'], 2),
                'top_cliente' => $topCliente,
                'top_cliente_costo' => round($topClienteCosto, 2),
                'top_matricula' => $topMatricula,
                'top_matricula_costo' => round($topMatriculaCosto, 2),
                'top_area' => $topArea,
                'top_area_costo' => round($topAreaCosto, 2),
                'top_ot' => $topOt,
                'top_ot_costo' => round($topOtCosto, 2),
                'top_ots' => collect($topOtRows)
                    ->sortByDesc('costo')
                    ->take(5)
                    ->values()
                    ->all(),
                'periodo' => $this->periodLabel($now->month, $now->year),
                'costo_periodo' => round($metrics['costo_periodo'], 2),
            ],
        ];
    }

    private function dashboardOrdersIdsQuery(Request $request): Builder
    {
        return $this->applyAreaScope($request, Orden::query(), 'ordenes.area_id')
            ->select('ordenes.id');
    }

    private function cacheKey(Request $request): string
    {
        $context = $this->areaCacheContext($request);
        ksort($context);

        return 'dashboard_resumen:v' . self::CACHE_SCHEMA_VERSION . ':' . Cache::get('dashboard_cache_version', 1) . ':' . md5(json_encode($context));
    }

    private function aggregateItemsByOrder(
        string $table,
        Builder $orderIdsQuery,
        bool $includeQuantity = false,
        bool $includeRequests = false,
        bool $includeReceipts = false,
        bool $includeTransfers = false,
        string $costColumn = 'costo_total',
    ): Collection {
        $select = [
            $table . '.orden_id as orden_id',
            DB::raw('COUNT(*) as records_count'),
            DB::raw('COALESCE(SUM(' . $costColumn . '), 0) as cost_sum'),
            DB::raw('COALESCE(SUM(precio_venta), 0) as sale_sum'),
        ];

        if ($includeQuantity) {
            $select[] = DB::raw("COALESCE(SUM(CASE WHEN COALESCE(cantidad, 0) > 0 THEN ROUND(cantidad) ELSE 1 END), 0) as quantity_sum");
        }

        if ($includeRequests) {
            $select[] = DB::raw("COALESCE(SUM(CASE WHEN solicitante_fecha IS NOT NULL THEN 1 ELSE 0 END), 0) as request_count");
        }

        if ($includeReceipts) {
            $receiptColumn = match ($table) {
                'refacciones', 'consumibles' => 'recibe_fecha',
                default => 'recepcion',
            };
            $select[] = DB::raw("COALESCE(SUM(CASE WHEN {$receiptColumn} IS NOT NULL THEN 1 ELSE 0 END), 0) as received_count");
        }

        if ($includeTransfers) {
            $select[] = DB::raw("COALESCE(SUM(CASE WHEN TRIM(COALESCE(area_procedencia, '')) <> '' THEN 1 ELSE 0 END), 0) as transfer_count");
        }

        return DB::table($table)
            ->joinSub($orderIdsQuery, 'ordenes', fn ($join) => $join->on($table . '.orden_id', '=', 'ordenes.id'))
            ->select($select)
            ->groupBy($table . '.orden_id')
            ->get()
            ->keyBy('orden_id');
    }

    private function sumDecimal(Collection $items, string $key): float
    {
        return (float) $items->sum(fn ($item) => (float) ($item->{$key} ?? 0));
    }

    private function countNotEmpty(Collection $items, string $key): int
    {
        return $items->filter(fn ($item) => filled($item->{$key} ?? null))->count();
    }

    private function sumQuantity(Collection $items): int
    {
        return $items->sum(function ($item) {
            $quantity = (float) ($item->cantidad ?? 0);

            return $quantity > 0 ? (int) round($quantity) : 1;
        });
    }

    private function safeText(?string $value, string $fallback): string
    {
        $trimmed = trim((string) $value);

        return $trimmed === '' ? $fallback : $trimmed;
    }

    private function topEntry(array $items): array
    {
        if ($items === []) {
            return ['Sin dato', 0.0];
        }

        arsort($items);
        $key = array_key_first($items);

        return [$key ?? 'Sin dato', (float) ($items[$key] ?? 0)];
    }

    private function periodLabel(int $month, int $year): string
    {
        $months = [
            1 => 'enero',
            2 => 'febrero',
            3 => 'marzo',
            4 => 'abril',
            5 => 'mayo',
            6 => 'junio',
            7 => 'julio',
            8 => 'agosto',
            9 => 'septiembre',
            10 => 'octubre',
            11 => 'noviembre',
            12 => 'diciembre',
        ];

        return ($months[$month] ?? 'sin periodo') . ' ' . $year;
    }
}
