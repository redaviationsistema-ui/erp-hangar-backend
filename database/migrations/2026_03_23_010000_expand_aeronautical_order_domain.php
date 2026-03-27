<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ordenes', function (Blueprint $table) {
            $this->addColumn($table, 'cliente', fn () => $table->string('cliente')->nullable()->after('fecha'));
            $this->addColumn($table, 'matricula', fn () => $table->string('matricula')->nullable()->after('cliente'));
            $this->addColumn($table, 'aeronave_modelo', fn () => $table->string('aeronave_modelo')->nullable()->after('matricula'));
            $this->addColumn($table, 'aeronave_serie', fn () => $table->string('aeronave_serie')->nullable()->after('aeronave_modelo'));
            $this->addColumn($table, 'tiempo_total', fn () => $table->decimal('tiempo_total', 12, 2)->nullable()->after('aeronave_serie'));
            $this->addColumn($table, 'ciclos_totales', fn () => $table->decimal('ciclos_totales', 12, 2)->nullable()->after('tiempo_total'));
            $this->addColumn($table, 'trabajo_descripcion', fn () => $table->text('trabajo_descripcion')->nullable()->after('descripcion'));
            $this->addColumn($table, 'componente_descripcion', fn () => $table->string('componente_descripcion')->nullable()->after('trabajo_descripcion'));
            $this->addColumn($table, 'componente_modelo', fn () => $table->string('componente_modelo')->nullable()->after('componente_descripcion'));
            $this->addColumn($table, 'componente_numero_parte', fn () => $table->string('componente_numero_parte')->nullable()->after('componente_modelo'));
            $this->addColumn($table, 'componente_numero_serie', fn () => $table->string('componente_numero_serie')->nullable()->after('componente_numero_parte'));
            $this->addColumn($table, 'componente_tiempo_total', fn () => $table->decimal('componente_tiempo_total', 12, 2)->nullable()->after('componente_numero_serie'));
            $this->addColumn($table, 'componente_ciclos_totales', fn () => $table->decimal('componente_ciclos_totales', 12, 2)->nullable()->after('componente_tiempo_total'));
            $this->addColumn($table, 'tipo_tarea', fn () => $table->string('tipo_tarea')->nullable()->after('componente_ciclos_totales'));
            $this->addColumn($table, 'intervalo', fn () => $table->string('intervalo')->nullable()->after('tipo_tarea'));
            $this->addColumn($table, 'accion_correctiva', fn () => $table->text('accion_correctiva')->nullable()->after('intervalo'));
            $this->addColumn($table, 'tecnico_responsable', fn () => $table->string('tecnico_responsable')->nullable()->after('accion_correctiva'));
            $this->addColumn($table, 'inspector', fn () => $table->string('inspector')->nullable()->after('tecnico_responsable'));
            $this->addColumn($table, 'fecha_inicio', fn () => $table->date('fecha_inicio')->nullable()->after('inspector'));
            $this->addColumn($table, 'fecha_termino', fn () => $table->date('fecha_termino')->nullable()->after('fecha_inicio'));
        });

        Schema::table('tareas', function (Blueprint $table) {
            $this->addColumn($table, 'area_id', fn () => $table->foreignId('area_id')->nullable()->after('orden_id')->constrained('areas')->nullOnDelete());
            $this->addColumn($table, 'ata_task_template_id', fn () => $table->foreignId('ata_task_template_id')->nullable()->after('area_id')->constrained('ata_task_templates')->nullOnDelete());
            $this->addColumn($table, 'tipo', fn () => $table->string('tipo')->nullable()->after('descripcion'));
            $this->addColumn($table, 'prioridad', fn () => $table->string('prioridad')->nullable()->after('tipo'));
            $this->addColumn($table, 'tiempo_estimado_min', fn () => $table->integer('tiempo_estimado_min')->nullable()->after('prioridad'));
            $this->addColumn($table, 'estado', fn () => $table->string('estado')->nullable()->after('tiempo_estimado_min'));
            $this->addColumn($table, 'tecnico', fn () => $table->string('tecnico')->nullable()->after('estado'));
        });

        Schema::table('discrepancias', function (Blueprint $table) {
            $this->addColumn($table, 'item', fn () => $table->string('item', 20)->nullable()->after('orden_id'));
            $this->addColumn($table, 'inspector', fn () => $table->string('inspector')->nullable()->after('status'));
            $this->addColumn($table, 'fecha_inicio', fn () => $table->date('fecha_inicio')->nullable()->after('inspector'));
            $this->addColumn($table, 'fecha_termino', fn () => $table->date('fecha_termino')->nullable()->after('fecha_inicio'));
            $this->addColumn($table, 'horas_hombre', fn () => $table->decimal('horas_hombre', 8, 2)->nullable()->after('fecha_termino'));
            $this->addColumn($table, 'imagen_path', fn () => $table->string('imagen_path')->nullable()->after('horas_hombre'));
            $this->addColumn($table, 'componente_numero_parte_off', fn () => $table->string('componente_numero_parte_off')->nullable()->after('imagen_path'));
            $this->addColumn($table, 'componente_numero_serie_off', fn () => $table->string('componente_numero_serie_off')->nullable()->after('componente_numero_parte_off'));
            $this->addColumn($table, 'componente_numero_parte_on', fn () => $table->string('componente_numero_parte_on')->nullable()->after('componente_numero_serie_off'));
            $this->addColumn($table, 'componente_numero_serie_on', fn () => $table->string('componente_numero_serie_on')->nullable()->after('componente_numero_parte_on'));
            $this->addColumn($table, 'observaciones', fn () => $table->text('observaciones')->nullable()->after('componente_numero_serie_on'));
        });

        foreach (['refacciones', 'consumibles', 'herramientas'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                $this->addColumn($table, 'item', fn () => $table->string('item', 20)->nullable()->after('orden_id'));
                $this->addColumn($table, 'solicitante_fecha', fn () => $table->date('solicitante_fecha')->nullable()->after('item'));
                if ($tableName !== 'refacciones') {
                    $this->addColumn($table, 'descripcion', fn () => $table->text('descripcion')->nullable()->after('nombre'));
                    $this->addColumn($table, 'cantidad', fn () => $table->integer('cantidad')->nullable()->after('descripcion'));
                    $this->addColumn($table, 'numero_parte', fn () => $table->string('numero_parte')->nullable()->after('cantidad'));
                }
                $this->addColumn($table, 'status', fn () => $table->string('status')->nullable()->after('numero_parte'));
                $this->addColumn($table, 'certificado_conformidad', fn () => $table->string('certificado_conformidad')->nullable()->after('status'));
                $this->addColumn($table, 'area_procedencia', fn () => $table->string('area_procedencia')->nullable()->after('certificado_conformidad'));
                $this->addColumn($table, 'recibe_fecha', fn () => $table->date('recibe_fecha')->nullable()->after('area_procedencia'));
                $this->addColumn($table, 'costo_total', fn () => $table->decimal('costo_total', 12, 2)->nullable()->after('recibe_fecha'));
                $this->addColumn($table, 'precio_venta', fn () => $table->decimal('precio_venta', 12, 2)->nullable()->after('costo_total'));
            });
        }

        Schema::table('ndt', function (Blueprint $table) {
            $this->addColumn($table, 'item', fn () => $table->string('item', 20)->nullable()->after('orden_id'));
            $this->addColumn($table, 'cantidad', fn () => $table->integer('cantidad')->nullable()->after('tipo_prueba'));
            $this->addColumn($table, 'sub_componente', fn () => $table->string('sub_componente')->nullable()->after('cantidad'));
            $this->addColumn($table, 'numero_parte', fn () => $table->string('numero_parte')->nullable()->after('sub_componente'));
            $this->addColumn($table, 'numero_serie', fn () => $table->string('numero_serie')->nullable()->after('numero_parte'));
            $this->addColumn($table, 'evidencia_path', fn () => $table->string('evidencia_path')->nullable()->after('numero_serie'));
            $this->addColumn($table, 'seccion_manual', fn () => $table->string('seccion_manual')->nullable()->after('evidencia_path'));
            $this->addColumn($table, 'certificado', fn () => $table->string('certificado')->nullable()->after('seccion_manual'));
            $this->addColumn($table, 'envio_a', fn () => $table->string('envio_a')->nullable()->after('certificado'));
            $this->addColumn($table, 'recepcion', fn () => $table->string('recepcion')->nullable()->after('envio_a'));
            $this->addColumn($table, 'costo_total', fn () => $table->decimal('costo_total', 12, 2)->nullable()->after('recepcion'));
            $this->addColumn($table, 'precio_venta', fn () => $table->decimal('precio_venta', 12, 2)->nullable()->after('costo_total'));
        });

        Schema::table('taller_externos', function (Blueprint $table) {
            $this->addColumn($table, 'item', fn () => $table->string('item', 20)->nullable()->after('orden_id'));
            $this->addColumn($table, 'tarea', fn () => $table->string('tarea')->nullable()->after('proveedor'));
            $this->addColumn($table, 'cantidad', fn () => $table->integer('cantidad')->nullable()->after('tarea'));
            $this->addColumn($table, 'sub_componente', fn () => $table->string('sub_componente')->nullable()->after('cantidad'));
            $this->addColumn($table, 'numero_parte', fn () => $table->string('numero_parte')->nullable()->after('sub_componente'));
            $this->addColumn($table, 'numero_serie', fn () => $table->string('numero_serie')->nullable()->after('numero_parte'));
            $this->addColumn($table, 'foto_path', fn () => $table->string('foto_path')->nullable()->after('numero_serie'));
            $this->addColumn($table, 'observaciones', fn () => $table->text('observaciones')->nullable()->after('foto_path'));
            $this->addColumn($table, 'certificado', fn () => $table->string('certificado')->nullable()->after('observaciones'));
            $this->addColumn($table, 'envio_a', fn () => $table->string('envio_a')->nullable()->after('certificado'));
            $this->addColumn($table, 'recepcion', fn () => $table->string('recepcion')->nullable()->after('envio_a'));
            $this->addColumn($table, 'precio_venta', fn () => $table->decimal('precio_venta', 12, 2)->nullable()->after('costo'));
        });

        Schema::table('mediciones', function (Blueprint $table) {
            $this->addColumn($table, 'item', fn () => $table->string('item', 20)->nullable()->after('orden_id'));
            $this->addColumn($table, 'tecnico', fn () => $table->string('tecnico')->nullable()->after('item'));
            $this->addColumn($table, 'descripcion', fn () => $table->text('descripcion')->nullable()->after('tecnico'));
            $this->addColumn($table, 'manual_od', fn () => $table->string('manual_od')->nullable()->after('descripcion'));
            $this->addColumn($table, 'manual_id', fn () => $table->string('manual_id')->nullable()->after('manual_od'));
            $this->addColumn($table, 'resultado_od', fn () => $table->string('resultado_od')->nullable()->after('manual_id'));
            $this->addColumn($table, 'resultado_id', fn () => $table->string('resultado_id')->nullable()->after('resultado_od'));
            $this->addColumn($table, 'imagen_path', fn () => $table->string('imagen_path')->nullable()->after('resultado_id'));
            $this->addColumn($table, 'observaciones', fn () => $table->text('observaciones')->nullable()->after('imagen_path'));
        });
    }

    public function down(): void
    {
        Schema::table('mediciones', function (Blueprint $table) {
            $this->dropColumnsIfExist($table, 'mediciones', ['item', 'tecnico', 'descripcion', 'manual_od', 'manual_id', 'resultado_od', 'resultado_id', 'imagen_path', 'observaciones']);
        });

        Schema::table('taller_externos', function (Blueprint $table) {
            $this->dropColumnsIfExist($table, 'taller_externos', ['item', 'tarea', 'cantidad', 'sub_componente', 'numero_parte', 'numero_serie', 'foto_path', 'observaciones', 'certificado', 'envio_a', 'recepcion', 'precio_venta']);
        });

        Schema::table('ndt', function (Blueprint $table) {
            $this->dropColumnsIfExist($table, 'ndt', ['item', 'cantidad', 'sub_componente', 'numero_parte', 'numero_serie', 'evidencia_path', 'seccion_manual', 'certificado', 'envio_a', 'recepcion', 'costo_total', 'precio_venta']);
        });

        foreach (['refacciones', 'consumibles', 'herramientas'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                $columns = ['item', 'solicitante_fecha', 'status', 'certificado_conformidad', 'area_procedencia', 'recibe_fecha', 'costo_total', 'precio_venta'];
                if ($tableName !== 'refacciones') {
                    $columns[] = 'descripcion';
                    $columns[] = 'cantidad';
                    $columns[] = 'numero_parte';
                }
                $this->dropColumnsIfExist($table, $tableName, $columns);
            });
        }

        Schema::table('discrepancias', function (Blueprint $table) {
            $this->dropColumnsIfExist($table, 'discrepancias', ['item', 'inspector', 'fecha_inicio', 'fecha_termino', 'horas_hombre', 'imagen_path', 'componente_numero_parte_off', 'componente_numero_serie_off', 'componente_numero_parte_on', 'componente_numero_serie_on', 'observaciones']);
        });

        Schema::table('tareas', function (Blueprint $table) {
            if (Schema::hasColumn('tareas', 'ata_task_template_id')) {
                $table->dropConstrainedForeignId('ata_task_template_id');
            }
            if (Schema::hasColumn('tareas', 'area_id')) {
                $table->dropConstrainedForeignId('area_id');
            }
            $this->dropColumnsIfExist($table, 'tareas', ['tipo', 'prioridad', 'tiempo_estimado_min', 'estado', 'tecnico']);
        });

        Schema::table('ordenes', function (Blueprint $table) {
            $this->dropColumnsIfExist($table, 'ordenes', [
                'cliente',
                'matricula',
                'aeronave_modelo',
                'aeronave_serie',
                'tiempo_total',
                'ciclos_totales',
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
            ]);
        });
    }

    private function addColumn(Blueprint $table, string $column, callable $callback): void
    {
        if (!Schema::hasColumn($table->getTable(), $column)) {
            $callback();
        }
    }

    private function dropColumnsIfExist(Blueprint $table, string $tableName, array $columns): void
    {
        $toDrop = array_values(array_filter($columns, fn ($column) => Schema::hasColumn($tableName, $column)));

        if ($toDrop !== []) {
            $table->dropColumn($toDrop);
        }
    }
};
