<?php

namespace App\Http\Requests;

use App\Models\Orden;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreOrdenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'area_id' => 'required|exists:areas,id',
            'tipo_id' => 'required|exists:tipo_ordenes,id',
            'user_id' => 'required|exists:users,id',
            'ata_chapter_id' => 'nullable|exists:ata_chapters,id',
            'ata_subchapter_id' => 'nullable|exists:ata_subchapters,id',
            'motor_id' => 'nullable|exists:motores,id',
            'folio' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('ordenes', 'folio')->ignore($this->currentOrder()?->id),
            ],
            'consecutivo' => 'nullable|integer|min:1',
            'anio' => 'nullable|integer|min:2000|max:2100',
            'fecha' => 'nullable|date',
            'cliente' => 'nullable|string|max:255',
            'matricula' => 'nullable|string|max:255',
            'aeronave_modelo' => 'nullable|string|max:255',
            'aeronave_serie' => 'nullable|string|max:255',
            'tiempo_total' => 'nullable|numeric',
            'ciclos_totales' => 'nullable|numeric',
            'descripcion' => 'required|string|max:1000',
            'trabajo_descripcion' => 'nullable|string',
            'componente_descripcion' => 'nullable|string|max:255',
            'componente_modelo' => 'nullable|string|max:255',
            'componente_numero_parte' => 'nullable|string|max:255',
            'componente_numero_serie' => 'nullable|string|max:255',
            'componente_tiempo_total' => 'nullable|numeric',
            'componente_ciclos_totales' => 'nullable|numeric',
            'tipo_tarea' => 'nullable|string|max:255',
            'intervalo' => 'nullable|string|max:255',
            'accion_correctiva' => 'nullable|string',
            'horas_labor' => 'nullable|string|max:255',
            'tecnico_responsable' => 'nullable|string|max:255',
            'auxiliar' => 'nullable|string|max:255',
            'inspector' => 'nullable|string|max:255',
            'fecha_inicio' => 'nullable|date',
            'fecha_termino' => 'nullable|date',
            'estado' => 'nullable|string|max:255',
            'miscelanea_costo_total' => 'nullable|numeric',
            'miscelanea_precio_venta' => 'nullable|numeric',
            'miscelanea_observaciones_admin' => 'nullable|string',
            'generar_tareas_ata' => 'nullable|boolean',
            'tareas' => 'nullable|array',
            'tareas.*.area_id' => 'nullable|exists:areas,id',
            'tareas.*.ata_task_template_id' => 'nullable|exists:ata_task_templates,id',
            'tareas.*.titulo' => 'required_with:tareas|string|max:255',
            'tareas.*.descripcion' => 'nullable|string',
            'tareas.*.orden' => 'nullable|integer',
            'tareas.*.tipo' => 'nullable|string|max:100',
            'tareas.*.prioridad' => 'nullable|string|max:100',
            'tareas.*.tiempo_estimado_min' => 'nullable|integer',
            'tareas.*.estado' => 'nullable|string|max:100',
            'tareas.*.tecnico' => 'nullable|string|max:255',
            'tareas.*.foto_path' => 'nullable|string',
            'tareas.*.foto' => 'nullable|string',
            'tareas.*.imagen' => 'nullable|string',
            'tareas.*.image' => 'nullable|string',
            'tareas.*.evidencia' => 'nullable|string',
            'tareas.*.foto_base64' => 'nullable|string',
            'tareas.*.imagen_base64' => 'nullable|string',
            'tareas.*.evidencia_base64' => 'nullable|string',
            'cartas' => 'nullable|array',
            'cartas.*.item' => 'nullable|string|max:20',
            'cartas.*.tarea' => 'nullable|string|max:255',
            'cartas.*.titulo' => 'required_with:cartas|string|max:255',
            'cartas.*.remanente' => 'nullable|string|max:255',
            'cartas.*.completado' => 'nullable|string|max:255',
            'cartas.*.siguiente' => 'nullable|string|max:255',
            'cartas.*.notas' => 'nullable|string',
            'cartas.*.accion_correctiva' => 'nullable|string',
            'cartas.*.descripcion_componente' => 'nullable|string|max:255',
            'cartas.*.cantidad' => 'nullable|integer|min:1',
            'cartas.*.numero_parte' => 'nullable|string|max:255',
            'cartas.*.numero_serie_removido' => 'nullable|string|max:255',
            'cartas.*.numero_serie_instalado' => 'nullable|string|max:255',
            'cartas.*.observaciones' => 'nullable|string',
            'cartas.*.fecha_termino' => 'nullable|date',
            'cartas.*.horas_labor' => 'nullable|numeric',
            'cartas.*.auxiliar' => 'nullable|string|max:255',
            'cartas.*.tecnico' => 'nullable|string|max:255',
            'cartas.*.inspector' => 'nullable|string|max:255',
            'discrepancias' => 'nullable|array',
            'discrepancias.*.item' => 'nullable|string|max:20',
            'discrepancias.*.descripcion' => 'required_with:discrepancias|string',
            'discrepancias.*.accion_correctiva' => 'nullable|string',
            'discrepancias.*.status' => 'nullable|string|max:100',
            'discrepancias.*.tecnico' => 'nullable|string|max:255',
            'discrepancias.*.inspector' => 'nullable|string|max:255',
            'discrepancias.*.fecha_inicio' => 'nullable|date',
            'discrepancias.*.fecha_termino' => 'nullable|date',
            'discrepancias.*.horas_hombre' => 'nullable|numeric',
            'discrepancias.*.imagen_path' => 'nullable|string',
            'discrepancias.*.foto' => 'nullable|string',
            'discrepancias.*.imagen' => 'nullable|string',
            'discrepancias.*.image' => 'nullable|string',
            'discrepancias.*.evidencia' => 'nullable|string',
            'discrepancias.*.foto_base64' => 'nullable|string',
            'discrepancias.*.imagen_base64' => 'nullable|string',
            'discrepancias.*.evidencia_base64' => 'nullable|string',
            'discrepancias.*.componente_numero_parte_off' => 'nullable|string|max:255',
            'discrepancias.*.componente_numero_serie_off' => 'nullable|string|max:255',
            'discrepancias.*.componente_numero_parte_on' => 'nullable|string|max:255',
            'discrepancias.*.componente_numero_serie_on' => 'nullable|string|max:255',
            'discrepancias.*.observaciones' => 'nullable|string',
            'refacciones' => 'nullable|array',
            'consumibles' => 'nullable|array',
            'herramientas' => 'nullable|array',
            'ndt' => 'nullable|array',
            'talleres_externos' => 'nullable|array',
            'mediciones' => 'nullable|array',
        ] + $this->catalogItemRules('refacciones') + $this->catalogItemRules('consumibles') + $this->catalogItemRules('herramientas') + $this->ndtRules() + $this->tallerRules() + $this->medicionRules();
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $data = $validator->safe()->toArray();

            if (! $this->isClosingState($data['estado'] ?? null)) {
                return;
            }

            foreach ($this->closingValidationMessages($data) as $message) {
                $validator->errors()->add('estado', $message);
            }
        });
    }

    protected function closingValidationMessages(array $data): array
    {
        $snapshot = $this->buildClosingSnapshot($data);
        $messages = [];

        if (! $snapshot['has_responsible']) {
            $messages[] = 'No se puede cerrar la OT sin responsable tecnico o usuario asignado.';
        }

        if (! $snapshot['has_evidence']) {
            $messages[] = 'No se puede cerrar la OT sin evidencia tecnica registrada.';
        }

        if (! $snapshot['has_materials']) {
            $messages[] = 'No se puede cerrar la OT sin materiales o registros tecnicos relacionados.';
        }

        if ($snapshot['has_open_discrepancies']) {
            $messages[] = 'No se puede cerrar la OT mientras existan discrepancias sin resolver.';
        }

        return $messages;
    }

    protected function buildClosingSnapshot(array $data): array
    {
        $order = $this->currentOrder();
        $relations = [
            'tareas',
            'discrepancias',
            'refacciones',
            'consumibles',
            'herramientas',
            'ndt',
            'talleresExternos',
            'mediciones',
        ];

        if (Schema::hasTable('cartas')) {
            $relations[] = 'cartas';
        }

        $order?->loadMissing($relations);

        $responsible = trim((string) ($data['tecnico_responsable'] ?? $order?->tecnico_responsable ?? ''));
        $hasAssignedUser = ! empty($data['user_id']) || ! empty($order?->user_id);

        $tareas = $this->relationItems($data, 'tareas', $order?->tareas?->all() ?? []);
        $cartas = Schema::hasTable('cartas')
            ? $this->relationItems($data, 'cartas', $order?->cartas?->all() ?? [])
            : [];
        $discrepancias = $this->relationItems($data, 'discrepancias', $order?->discrepancias?->all() ?? []);
        $refacciones = $this->relationItems($data, 'refacciones', $order?->refacciones?->all() ?? []);
        $consumibles = $this->relationItems($data, 'consumibles', $order?->consumibles?->all() ?? []);
        $herramientas = $this->relationItems($data, 'herramientas', $order?->herramientas?->all() ?? []);
        $ndt = $this->relationItems($data, 'ndt', $order?->ndt?->all() ?? []);
        $talleres = $this->relationItems($data, 'talleres_externos', $order?->talleresExternos?->all() ?? []);
        $mediciones = $this->relationItems($data, 'mediciones', $order?->mediciones?->all() ?? []);

        return [
            'has_responsible' => $responsible !== '' || $hasAssignedUser,
            'has_evidence' => $this->hasEvidence([
                ...$tareas,
                ...$cartas,
                ...$discrepancias,
                ...$ndt,
                ...$talleres,
                ...$mediciones,
            ]),
            'has_materials' => $this->hasMaterials([
                ...$cartas,
                ...$refacciones,
                ...$consumibles,
                ...$herramientas,
                ...$ndt,
                ...$talleres,
                ...$mediciones,
            ]),
            'has_open_discrepancies' => $this->hasOpenDiscrepancies($discrepancias),
        ];
    }

    protected function currentOrder(): ?Orden
    {
        $order = $this->route('ordene');

        return $order instanceof Orden ? $order : null;
    }

    protected function relationItems(array $data, string $key, array $fallback): array
    {
        $source = array_key_exists($key, $data) ? ($data[$key] ?? []) : $fallback;

        return collect($source)
            ->map(function ($item) {
                if (is_array($item)) {
                    return $item;
                }

                if (is_object($item) && method_exists($item, 'toArray')) {
                    return $item->toArray();
                }

                return [];
            })
            ->filter(fn (array $item) => ! empty($item))
            ->values()
            ->all();
    }

    protected function hasEvidence(array $items): bool
    {
        $keys = [
            'imagen_path',
            'foto_path',
            'evidencia_path',
            'foto',
            'imagen',
            'image',
            'evidencia',
            'foto_url',
            'imagen_url',
            'evidencia_url',
        ];

        foreach ($items as $item) {
            foreach ($keys as $key) {
                if (trim((string) ($item[$key] ?? '')) !== '') {
                    return true;
                }
            }
        }

        return false;
    }

    protected function hasMaterials(array $items): bool
    {
        foreach ($items as $item) {
            $descriptor = trim((string) ($item['descripcion'] ?? $item['nombre'] ?? $item['tipo_prueba'] ?? $item['proveedor'] ?? $item['parametro'] ?? ''));
            $partNumber = trim((string) ($item['numero_parte'] ?? ''));
            $quantity = $item['cantidad'] ?? null;

            if ($descriptor !== '' || $partNumber !== '' || (is_numeric($quantity) && (int) $quantity > 0)) {
                return true;
            }
        }

        return false;
    }

    protected function hasOpenDiscrepancies(array $items): bool
    {
        foreach ($items as $item) {
            $status = strtolower(trim((string) ($item['status'] ?? '')));
            $hasResolution = trim((string) ($item['accion_correctiva'] ?? '')) !== '';
            $hasEndDate = trim((string) ($item['fecha_termino'] ?? '')) !== '';

            $resolvedByStatus = in_array($status, [
                'resuelta',
                'resuelto',
                'cerrada',
                'cerrado',
                'completada',
                'completado',
                'finalizada',
                'finalizado',
                'liberada',
                'liberado',
                'ok',
            ], true);

            if (! $resolvedByStatus && ! $hasResolution && ! $hasEndDate) {
                return true;
            }
        }

        return false;
    }

    protected function isClosingState(mixed $state): bool
    {
        return strtolower(trim((string) $state)) === 'cerrada';
    }

    private function catalogItemRules(string $prefix): array
    {
        return [
            $prefix . '.*.item' => 'nullable|string|max:20',
            $prefix . '.*.solicitante_fecha' => 'nullable|date',
            $prefix . '.*.solicitante_nombre' => 'nullable|string|max:255',
            $prefix . '.*.nombre' => 'nullable|string|max:255',
            $prefix . '.*.descripcion' => 'required_with:' . $prefix . '|string',
            $prefix . '.*.cantidad' => 'nullable|integer|min:1',
            $prefix . '.*.numero_parte' => 'nullable|string|max:255',
            $prefix . '.*.status' => 'nullable|string|max:100',
            $prefix . '.*.certificado_conformidad' => 'nullable|string|max:255',
            $prefix . '.*.certificado_conformidad_imagen' => 'nullable|string|max:2048',
            $prefix . '.*.certificado_conformidad_imagen_base64' => 'nullable|string',
            $prefix . '.*.certificado_conformidad_foto' => 'nullable|string',
            $prefix . '.*.certificado_conformidad_imagen_file' => 'nullable|string',
            $prefix . '.*.area_procedencia' => 'nullable|string|max:255',
            $prefix . '.*.recibe_fecha' => 'nullable|date',
            $prefix . '.*.recibe_nombre' => 'nullable|string|max:255',
            $prefix . '.*.costo_total' => 'nullable|numeric',
            $prefix . '.*.precio_venta' => 'nullable|numeric',
            $prefix . '.*.fecha_entrega' => 'nullable|date',
        ];
    }

    private function ndtRules(): array
    {
        return [
            'ndt.*.item' => 'nullable|string|max:20',
            'ndt.*.tipo_prueba' => 'required_with:ndt|string|max:255',
            'ndt.*.cantidad' => 'nullable|integer|min:1',
            'ndt.*.sub_componente' => 'nullable|string|max:255',
            'ndt.*.numero_parte' => 'nullable|string|max:255',
            'ndt.*.numero_serie' => 'nullable|string|max:255',
            'ndt.*.evidencia_path' => 'nullable|string',
            'ndt.*.foto' => 'nullable|string',
            'ndt.*.imagen' => 'nullable|string',
            'ndt.*.image' => 'nullable|string',
            'ndt.*.evidencia' => 'nullable|string',
            'ndt.*.foto_base64' => 'nullable|string',
            'ndt.*.imagen_base64' => 'nullable|string',
            'ndt.*.evidencia_base64' => 'nullable|string',
            'ndt.*.seccion_manual' => 'nullable|string|max:255',
            'ndt.*.certificado' => 'nullable|string|max:255',
            'ndt.*.envio_a' => 'nullable|string|max:255',
            'ndt.*.recepcion' => 'nullable|string|max:255',
            'ndt.*.costo_total' => 'nullable|numeric',
            'ndt.*.precio_venta' => 'nullable|numeric',
            'ndt.*.resultado' => 'nullable|string|max:255',
        ];
    }

    private function tallerRules(): array
    {
        return [
            'talleres_externos.*.item' => 'nullable|string|max:20',
            'talleres_externos.*.proveedor' => 'required_with:talleres_externos|string|max:255',
            'talleres_externos.*.tarea' => 'nullable|string|max:255',
            'talleres_externos.*.cantidad' => 'nullable|integer|min:1',
            'talleres_externos.*.sub_componente' => 'nullable|string|max:255',
            'talleres_externos.*.numero_parte' => 'nullable|string|max:255',
            'talleres_externos.*.numero_serie' => 'nullable|string|max:255',
            'talleres_externos.*.foto_path' => 'nullable|string',
            'talleres_externos.*.foto' => 'nullable|string',
            'talleres_externos.*.imagen' => 'nullable|string',
            'talleres_externos.*.image' => 'nullable|string',
            'talleres_externos.*.evidencia' => 'nullable|string',
            'talleres_externos.*.foto_base64' => 'nullable|string',
            'talleres_externos.*.imagen_base64' => 'nullable|string',
            'talleres_externos.*.evidencia_base64' => 'nullable|string',
            'talleres_externos.*.observaciones' => 'nullable|string',
            'talleres_externos.*.certificado' => 'nullable|string|max:255',
            'talleres_externos.*.envio_a' => 'nullable|string|max:255',
            'talleres_externos.*.recepcion' => 'nullable|string|max:255',
            'talleres_externos.*.trabajo_realizado' => 'nullable|string',
            'talleres_externos.*.costo' => 'nullable|numeric',
            'talleres_externos.*.precio_venta' => 'nullable|numeric',
        ];
    }

    private function medicionRules(): array
    {
        return [
            'mediciones.*.item' => 'nullable|string|max:20',
            'mediciones.*.tecnico' => 'nullable|string|max:255',
            'mediciones.*.descripcion' => 'required_with:mediciones|string',
            'mediciones.*.manual_od' => 'nullable|string|max:255',
            'mediciones.*.manual_id' => 'nullable|string|max:255',
            'mediciones.*.resultado_od' => 'nullable|string|max:255',
            'mediciones.*.resultado_id' => 'nullable|string|max:255',
            'mediciones.*.imagen_path' => 'nullable|string',
            'mediciones.*.foto' => 'nullable|string',
            'mediciones.*.imagen' => 'nullable|string',
            'mediciones.*.image' => 'nullable|string',
            'mediciones.*.evidencia' => 'nullable|string',
            'mediciones.*.foto_base64' => 'nullable|string',
            'mediciones.*.imagen_base64' => 'nullable|string',
            'mediciones.*.evidencia_base64' => 'nullable|string',
            'mediciones.*.observaciones' => 'nullable|string',
            'mediciones.*.parametro' => 'nullable|string|max:255',
            'mediciones.*.valor' => 'nullable|string|max:255',
            'mediciones.*.unidad' => 'nullable|string|max:255',
        ];
    }
}


