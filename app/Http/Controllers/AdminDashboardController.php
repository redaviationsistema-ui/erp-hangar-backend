<?php

namespace App\Http\Controllers;

use App\Models\Orden;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class AdminDashboardController extends Controller
{
    public function resumen(Request $request)
    {
        $ordenes = $this->applyAreaScope(
            $request,
            Orden::query()->with([
                'area:id,codigo,nombre',
                'discrepancias:id,orden_id,horas_hombre',
                'refacciones:id,orden_id,cantidad,solicitante_fecha,area_procedencia,recibe_fecha,costo_total,precio_venta',
                'consumibles:id,orden_id,cantidad,solicitante_fecha,area_procedencia,recibe_fecha,costo_total,precio_venta',
                'talleresExternos:id,orden_id,proveedor,recepcion,costo,precio_venta',
                'ndt:id,orden_id,recepcion,costo_total,precio_venta',
            ])
        )
            ->withCount('herramientas')
            ->select([
                'id',
                'area_id',
                'folio',
                'cliente',
                'matricula',
                'fecha_inicio',
                'estado',
                'created_at',
            ])
            ->get();

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
        $proveedores = [];
        $porCliente = [];
        $porMatricula = [];
        $porArea = [];
        $porOt = [];

        foreach ($ordenes as $orden) {
            $activa = in_array(strtolower(trim((string) $orden->estado)), ['abierta', 'proceso'], true);

            $refacciones = $orden->refacciones;
            $consumibles = $orden->consumibles;
            $talleres = $orden->talleresExternos;
            $ndt = $orden->ndt;

            $metrics['refacciones_registros'] += $refacciones->count();
            $metrics['consumibles_registros'] += $consumibles->count();
            $metrics['talleres_registros'] += $talleres->count();
            $metrics['ndt_registros'] += $ndt->count();

            $costoRef = $this->sumDecimal($refacciones, 'costo_total');
            $costoCon = $this->sumDecimal($consumibles, 'costo_total');
            $costoTall = $this->sumDecimal($talleres, 'costo');
            $costoNdt = $this->sumDecimal($ndt, 'costo_total');
            $costoMiscelanea = 0.0;
            $ventaOt = $this->sumDecimal($refacciones, 'precio_venta')
                + $this->sumDecimal($consumibles, 'precio_venta')
                + $this->sumDecimal($talleres, 'precio_venta')
                + $this->sumDecimal($ndt, 'precio_venta');
            $costoOt = $costoRef + $costoCon + $costoTall + $costoNdt + $costoMiscelanea;

            $metrics['existencias'] += $refacciones->count() + $consumibles->count() + (int) $orden->herramientas_count;
            $metrics['reservas'] += $activa ? $refacciones->count() + $consumibles->count() : 0;
            $metrics['entradas'] += $this->countNotEmpty($refacciones, 'recibe_fecha')
                + $this->countNotEmpty($talleres, 'recepcion')
                + $this->countNotEmpty($ndt, 'recepcion');
            $metrics['recepciones'] += $this->countNotEmpty($refacciones, 'recibe_fecha')
                + $this->countNotEmpty($talleres, 'recepcion')
                + $this->countNotEmpty($ndt, 'recepcion');
            $metrics['salidas'] += $this->sumQuantity($refacciones) + $this->sumQuantity($consumibles);
            $metrics['transferencias'] += $this->countNotEmpty($refacciones, 'area_procedencia')
                + $this->countNotEmpty($consumibles, 'area_procedencia');
            $metrics['requisiciones'] += $this->countNotEmpty($refacciones, 'solicitante_fecha')
                + $this->countNotEmpty($consumibles, 'solicitante_fecha');
            if ($refacciones->isNotEmpty() || $consumibles->isNotEmpty() || $talleres->isNotEmpty() || $ndt->isNotEmpty()) {
                $metrics['ot_con_compra']++;
            }

            foreach ($talleres as $taller) {
                $proveedor = trim((string) $taller->proveedor);
                if ($proveedor !== '') {
                    $proveedores[$proveedor] = true;
                }
            }

            $metrics['horas_hombre'] += (float) $orden->discrepancias->sum('horas_hombre');
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
            $area = $this->safeText($orden->area?->codigo ?? $orden->area?->nombre, 'Sin area');
            $folio = $this->safeText($orden->folio, 'Sin OT');

            $porCliente[$cliente] = ($porCliente[$cliente] ?? 0) + $costoOt;
            $porMatricula[$matricula] = ($porMatricula[$matricula] ?? 0) + $costoOt;
            $porArea[$area] = ($porArea[$area] ?? 0) + $costoOt;
            $porOt[$folio] = ($porOt[$folio] ?? 0) + $costoOt;

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

        return response()->json([
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
                'proveedores' => count($proveedores),
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
                'periodo' => $this->periodLabel($now->month, $now->year),
                'costo_periodo' => round($metrics['costo_periodo'], 2),
            ],
        ]);
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
