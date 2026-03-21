<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Orden;
use App\Models\Tarea;
use App\Models\Discrepancia;
use App\Models\Refaccion;
use App\Models\Consumible;
use App\Models\Herramienta;
use App\Models\Ndt;
use App\Models\TallerExterno;
use App\Models\Medicion;
use App\Models\TipoOrden;

class OrdenSeeder extends Seeder
{
    public function run(): void
    {

        $tipo = TipoOrden::where('codigo', 'AVCS')->first();

        $anio = date('Y');

        // 🔥 OBTENER ÚLTIMO CONSECUTIVO
        $ultimo = Orden::where('tipo_id', $tipo->id)
            ->where('anio', $anio)
            ->max('consecutivo');

        $consecutivo = ($ultimo ?? 0) + 1;

        // 🔥 GENERAR FOLIO ÚNICO
        $folio = 'CESA-' . $tipo->codigo . '-' . $anio . '-' . str_pad($consecutivo, 3, '0', STR_PAD_LEFT);

        $orden = Orden::create([
            'tipo_id' => $tipo->id,
            'user_id' => 1,
            'consecutivo' => $consecutivo,
            'anio' => $anio,
            'fecha' => now(),
            'folio' => $folio,
            'descripcion' => 'Mantenimiento preventivo sistema eléctrico',
            'estado' => 'proceso',
        ]);

        // 🔧 TAREAS
        Tarea::insert([
            [
                'orden_id' => $orden->id,
                'titulo' => 'Inspección de cableado',
                'descripcion' => 'Inspección visual de cableado'
            ],
            [
                'orden_id' => $orden->id,
                'titulo' => 'Revisión de conectores',
                'descripcion' => 'Verificación de conectores'
            ],
            [
                'orden_id' => $orden->id,
                'titulo' => 'Prueba eléctrica',
                'descripcion' => 'Prueba de continuidad'
            ],
        ]);

        // ⚠️ DISCREPANCIAS
        Discrepancia::insert([
            ['orden_id' => $orden->id, 'descripcion' => 'Cable con desgaste en ala izquierda'],
            ['orden_id' => $orden->id, 'descripcion' => 'Conector flojo en panel principal'],
        ]);

        // 🔩 REFACCIONES
        Refaccion::insert([
            [
                'orden_id' => $orden->id,
                'nombre' => 'Cable AWG 12',
                'descripcion' => 'Cable AWG 12', // 🔥 IMPORTANTE
                'cantidad' => 5
            ],
            [
                'orden_id' => $orden->id,
                'nombre' => 'Conector tipo MIL',
                'descripcion' => 'Conector tipo MIL', // 🔥 IMPORTANTE
                'cantidad' => 2
            ],
        ]);

        // 🧴 CONSUMIBLES
        Consumible::insert([
            ['orden_id' => $orden->id, 'nombre' => 'Cinta aislante', 'cantidad' => 3],
            ['orden_id' => $orden->id, 'nombre' => 'Limpiador eléctrico', 'cantidad' => 1],
        ]);

        // 🧰 HERRAMIENTAS
        Herramienta::insert([
            ['orden_id' => $orden->id, 'nombre' => 'Multímetro'],
            ['orden_id' => $orden->id, 'nombre' => 'Pinza de corte'],
        ]);

        // 🔬 NDT
        Ndt::insert([
            [
                'orden_id' => $orden->id,
                'tipo_prueba' => 'Inspección visual',
                'resultado' => 'Sin anomalías'
            ],
            [
                'orden_id' => $orden->id,
                'tipo_prueba' => 'Ultrasonido',
                'resultado' => 'Sin grietas'
            ],
        ]);

        // 🏭 TALLER EXTERNO
        TallerExterno::insert([
            [
                'orden_id' => $orden->id,
                'proveedor' => 'Taller Aeronáutico MX',
                'trabajo_realizado' => 'Revisión y reparación de sistema eléctrico',
                'costo' => 15000.00
            ],
        ]);

        // 📏 MEDICIONES
        Medicion::insert([
            [
                'orden_id' => $orden->id,
                'parametro' => 'Voltaje',
                'valor' => '24.5',
                'unidad' => 'V'
            ],
            [
                'orden_id' => $orden->id,
                'parametro' => 'Voltaje',
                'valor' => '25.1',
                'unidad' => 'V'
            ],
        ]);
    }
}