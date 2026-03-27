<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrdenResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'folio' => $this->folio,
            'estado' => $this->estado,
            'fecha' => $this->fecha?->toDateString(),
            'cliente' => $this->cliente,
            'matricula' => $this->matricula,
            'aeronave_modelo' => $this->aeronave_modelo,
            'aeronave_serie' => $this->aeronave_serie,
            'tiempo_total' => $this->tiempo_total,
            'ciclos_totales' => $this->ciclos_totales,
            'descripcion' => $this->descripcion,
            'trabajo_descripcion' => $this->trabajo_descripcion,
            'componente_descripcion' => $this->componente_descripcion,
            'componente_modelo' => $this->componente_modelo,
            'componente_numero_parte' => $this->componente_numero_parte,
            'componente_numero_serie' => $this->componente_numero_serie,
            'componente_tiempo_total' => $this->componente_tiempo_total,
            'componente_ciclos_totales' => $this->componente_ciclos_totales,
            'tipo_tarea' => $this->tipo_tarea,
            'intervalo' => $this->intervalo,
            'accion_correctiva' => $this->accion_correctiva,
            'tecnico_responsable' => $this->tecnico_responsable,
            'inspector' => $this->inspector,
            'fecha_inicio' => $this->fecha_inicio?->toDateString(),
            'fecha_termino' => $this->fecha_termino?->toDateString(),
            'area' => $this->whenLoaded('area', fn () => [
                'id' => $this->area?->id,
                'codigo' => $this->area?->codigo,
                'numero' => $this->area?->numero,
                'nombre' => $this->area?->nombre,
            ]),
            'tipo' => $this->whenLoaded('tipo', fn () => [
                'id' => $this->tipo?->id,
                'codigo' => $this->tipo?->codigo,
                'nombre' => $this->tipo?->nombre,
            ]),
            'usuario' => $this->whenLoaded('usuario', fn () => [
                'id' => $this->usuario?->id,
                'nombre' => $this->usuario?->name,
                'email' => $this->usuario?->email,
            ]),
            $this->mergeWhen($this->relationLoaded('ataChapter') || $this->relationLoaded('ataSubchapter'), [
                'ata' => [
                    'chapter' => $this->relationLoaded('ataChapter') && $this->ataChapter ? [
                        'id' => $this->ataChapter->id,
                        'codigo' => $this->ataChapter->codigo,
                        'descripcion' => $this->ataChapter->descripcion,
                    ] : null,
                    'subchapter' => $this->relationLoaded('ataSubchapter') && $this->ataSubchapter ? [
                        'id' => $this->ataSubchapter->id,
                        'codigo' => $this->ataSubchapter->codigo,
                        'descripcion' => $this->ataSubchapter->descripcion,
                        'tipo_mantenimiento' => $this->ataSubchapter->tipo_mantenimiento,
                        'intervalo_horas' => $this->ataSubchapter->intervalo_horas,
                        'intervalo_ciclos' => $this->ataSubchapter->intervalo_ciclos,
                        'intervalo_dias' => $this->ataSubchapter->intervalo_dias,
                    ] : null,
                ],
            ]),
            'motor' => $this->whenLoaded('motor', fn () => $this->motor ? [
                'id' => $this->motor->id,
                'posicion' => $this->motor->posicion,
                'fabricante' => $this->motor->fabricante,
                'modelo' => $this->motor->modelo,
                'numero_parte' => $this->motor->numero_parte,
                'numero_serie' => $this->motor->numero_serie,
                'tiempo_total' => $this->motor->tiempo_total,
                'ciclos_totales' => $this->motor->ciclos_totales,
                'estado' => $this->motor->estado,
                'aeronave' => $this->motor->relationLoaded('aeronave') && $this->motor->aeronave ? [
                    'id' => $this->motor->aeronave->id,
                    'cliente' => $this->motor->aeronave->cliente,
                    'matricula' => $this->motor->aeronave->matricula,
                    'fabricante' => $this->motor->aeronave->fabricante,
                    'modelo' => $this->motor->aeronave->modelo,
                    'numero_serie' => $this->motor->aeronave->numero_serie,
                    'estado' => $this->motor->aeronave->estado,
                ] : null,
            ] : null),
            'tareas' => $this->whenLoaded('tareas'),
            'tareas_count' => $this->whenCounted('tareas'),
            'discrepancias' => $this->whenLoaded('discrepancias'),
            'discrepancias_count' => $this->whenCounted('discrepancias'),
            'refacciones' => $this->whenLoaded('refacciones'),
            'refacciones_count' => $this->whenCounted('refacciones'),
            'consumibles' => $this->whenLoaded('consumibles'),
            'consumibles_count' => $this->whenCounted('consumibles'),
            'herramientas' => $this->whenLoaded('herramientas'),
            'herramientas_count' => $this->whenCounted('herramientas'),
            'ndt' => $this->whenLoaded('ndt'),
            'ndt_count' => $this->whenCounted('ndt'),
            'talleres_externos' => $this->whenLoaded('talleresExternos'),
            'talleres_externos_count' => $this->whenCounted('talleresExternos'),
            'mediciones' => $this->whenLoaded('mediciones'),
            'mediciones_count' => $this->whenCounted('mediciones'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
