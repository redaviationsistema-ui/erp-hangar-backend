<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
        'tecnico_responsable',
        'inspector',
        'fecha_inicio',
        'fecha_termino',
        'estado',
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
        ];
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function tipo()
    {
        return $this->belongsTo(TipoOrden::class, 'tipo_id');
    }

    public function detalles()
    {
        return $this->hasMany(OrdenDetalle::class, 'orden_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function ataChapter()
    {
        return $this->belongsTo(AtaChapter::class, 'ata_chapter_id');
    }

    public function ataSubchapter()
    {
        return $this->belongsTo(AtaSubchapter::class, 'ata_subchapter_id');
    }

    public function motor()
    {
        return $this->belongsTo(Motor::class, 'motor_id');
    }

    public function tareas()
    {
        return $this->hasMany(Tarea::class, 'orden_id');
    }

    public function discrepancias()
    {
        return $this->hasMany(Discrepancia::class, 'orden_id');
    }

    public function refacciones()
    {
        return $this->hasMany(Refaccion::class, 'orden_id');
    }

    public function consumibles()
    {
        return $this->hasMany(Consumible::class, 'orden_id');
    }

    public function herramientas()
    {
        return $this->hasMany(Herramienta::class, 'orden_id');
    }

    public function ndt()
    {
        return $this->hasMany(Ndt::class, 'orden_id');
    }

    public function talleresExternos()
    {
        return $this->hasMany(TallerExterno::class, 'orden_id');
    }

    public function mediciones()
    {
        return $this->hasMany(Medicion::class, 'orden_id');
    }
}
