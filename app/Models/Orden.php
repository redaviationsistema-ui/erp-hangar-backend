<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $area_id
 * @property int $tipo_id
 * @property int $user_id
 * @property int $ata_chapter_id
 * @property int $ata_subchapter_id
 * @property int $motor_id
 * @property string $folio
 * @property int $consecutivo
 * @property int $anio
 * @property string $fecha
 * @property string $cliente
 * @property string $matricula
 * @property string $aeronave_modelo
 * @property string $aeronave_serie
 * @property string $tiempo_total
 * @property string $ciclos_totales
 * @property string $descripcion
 * @property string $trabajo_descripcion
 * @property string $componente_descripcion
 * @property string $componente_modelo
 * @property string $componente_numero_parte
 * @property string $componente_numero_serie
 * @property string $componente_tiempo_total
 * @property string $componente_ciclos_totales
 * @property string $tipo_tarea
 * @property string $intervalo
 * @property string $accion_correctiva
 * @property string $tecnico_responsable
 * @property string $inspector
 * @property string $fecha_inicio
 * @property string $fecha_termino
 * @property string $estado
 * @property string|null $area_codigo
 * @property string|null $area_numero
 * @property string|null $area_nombre
 * @property string|null $tipo_codigo
 * @property string|null $tipo_nombre
 * @property string|null $usuario_nombre
 * @property string|null $usuario_email
 * @property int|null $motor_aeronave_id
 * @property string|null $motor_posicion
 * @property string|null $motor_fabricante
 * @property string|null $motor_modelo
 * @property string|null $motor_numero_parte
 * @property string|null $motor_numero_serie
 * @property float|null $motor_tiempo_total
 * @property float|null $motor_ciclos_totales
 * @property string|null $motor_estado
 * @property string|null $aeronave_cliente
 * @property string|null $aeronave_matricula
 * @property string|null $aeronave_fabricante
 * @property string|null $aeronave_modelo_rel
 * @property string|null $aeronave_numero_serie_rel
 * @property string|null $aeronave_estado
 * @property string|null $ata_chapter_codigo
 * @property string|null $ata_chapter_descripcion
 * @property string|null $ata_subchapter_codigo
 * @property string|null $ata_subchapter_descripcion
 * @property string|null $ata_subchapter_tipo_mantenimiento
 * @property float|null $ata_subchapter_intervalo_horas
 * @property float|null $ata_subchapter_intervalo_ciclos
 * @property int|null $ata_subchapter_intervalo_dias
 * @property int|null $tareas_count
 * @property int|null $discrepancias_count
 * @property int|null $refacciones_count
 * @property int|null $consumibles_count
 * @property int|null $herramientas_count
 * @property int|null $ndt_count
 * @property int|null $talleres_externos_count
 * @property int|null $mediciones_count
 * @property Area|null $area
 * @property TipoOrden|null $tipo
 * @property User|null $usuario
 * @property AtaChapter|null $ataChapter
 * @property AtaSubchapter|null $ataSubchapter
 * @property Motor|null $motor
 */
class Orden extends Model
{
    protected $table = 'ordenes';

    protected $fillable = [
        'area_id',
        'tipo_id',
        'user_id',
        'ata_chapter_id',
        'ata_subchapter_id',
        'motor_id',
        'folio',
        'consecutivo',
        'anio',
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
        'horas_labor',
        'tecnico_responsable',
        'inspector',
        'fecha_inicio',
        'fecha_termino',
        'estado',
        'miscelanea_costo_total',
        'miscelanea_precio_venta',
        'miscelanea_observaciones_admin',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'fecha_inicio' => 'date',
            'fecha_termino' => 'date',
            'tiempo_total' => 'decimal:2',
            'ciclos_totales' => 'decimal:2',
            'componente_tiempo_total' => 'decimal:2',
            'componente_ciclos_totales' => 'decimal:2',
            'miscelanea_costo_total' => 'decimal:2',
            'miscelanea_precio_venta' => 'decimal:2',
        ];
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function clientes(): BelongsToMany
    {
        return $this->belongsToMany(Cliente::class, 'cliente_orden', 'orden_id', 'cliente_id');
    }

    public function tipo(): BelongsTo
    {
        return $this->belongsTo(TipoOrden::class, 'tipo_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(OrdenDetalle::class, 'orden_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function ataChapter(): BelongsTo
    {
        return $this->belongsTo(AtaChapter::class, 'ata_chapter_id');
    }

    public function ataSubchapter(): BelongsTo
    {
        return $this->belongsTo(AtaSubchapter::class, 'ata_subchapter_id');
    }

    public function motor(): BelongsTo
    {
        return $this->belongsTo(Motor::class, 'motor_id');
    }

    public function tareas(): HasMany
    {
        return $this->hasMany(Tarea::class, 'orden_id');
    }

    public function discrepancias(): HasMany
    {
        return $this->hasMany(Discrepancia::class, 'orden_id');
    }

    public function cartas(): HasMany
    {
        return $this->hasMany(Carta::class, 'orden_id');
    }

    public function refacciones(): HasMany
    {
        return $this->hasMany(Refaccion::class, 'orden_id');
    }

    public function consumibles(): HasMany
    {
        return $this->hasMany(Consumible::class, 'orden_id');
    }

    public function herramientas(): HasMany
    {
        return $this->hasMany(Herramienta::class, 'orden_id');
    }

    public function ndt(): HasMany
    {
        return $this->hasMany(Ndt::class, 'orden_id');
    }

    public function talleresExternos(): HasMany
    {
        return $this->hasMany(TallerExterno::class, 'orden_id');
    }

    public function mediciones(): HasMany
    {
        return $this->hasMany(Medicion::class, 'orden_id');
    }
}

