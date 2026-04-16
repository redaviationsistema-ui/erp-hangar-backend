<?php

namespace App\Support;

use App\Models\Area;

class AreaOtFormSchema
{
    public static function for(?Area $area): array
    {
        $default = config('ot_forms.default', []);

        if (! $area) {
            return $default;
        }

        $override = config('ot_forms.areas.' . strtoupper((string) $area->codigo), []);
        $schema = self::mergeSchema($default, $override);

        if (strtoupper((string) $area->codigo) === 'TREN') {
            return self::normalizeTrenExcelSchema($schema);
        }

        return $schema;
    }

    private static function mergeSchema(array $default, array $override): array
    {
        $merged = array_replace_recursive($default, $override);

        if (isset($default['tabs']) || isset($override['tabs'])) {
            $merged['tabs'] = self::mergeTabs($default['tabs'] ?? [], $override['tabs'] ?? []);
        }

        return $merged;
    }

    private static function mergeTabs(array $defaultTabs, array $overrideTabs): array
    {
        $tabs = [];

        foreach ($defaultTabs as $tab) {
            if (! isset($tab['key'])) {
                continue;
            }

            $tabs[$tab['key']] = $tab;
        }

        foreach ($overrideTabs as $tab) {
            $key = $tab['key'] ?? null;

            if (! $key) {
                continue;
            }

            $tabs[$key] = array_replace_recursive($tabs[$key] ?? [], $tab);
        }

        return array_values($tabs);
    }

    private static function normalizeTrenExcelSchema(array $schema): array
    {
        $existingByCollection = [];

        foreach (($schema['tabs'] ?? []) as $tab) {
            $collection = strtolower((string) ($tab['collection'] ?? $tab['key'] ?? ''));
            if ($collection === '') {
                continue;
            }

            $existingByCollection[$collection] = $tab;
        }

        $definitions = [
            [
                'key' => 'caratula',
                'label' => 'CARATULA',
                'collection' => 'caratula',
                'empty_state' => 'Sin datos de caratula configurados para esta area.',
            ],
            [
                'key' => 'miscelanea',
                'label' => 'MISCELANEA',
                'collection' => 'miscelanea',
                'empty_state' => 'Sin datos de MISCELANEA configurados para esta area.',
            ],
            [
                'key' => 'paso_a_paso',
                'label' => 'PASO A PASO',
                'collection' => 'tareas',
                'empty_state' => 'Sin pasos configurados para esta area.',
            ],
            [
                'key' => 'cartas',
                'label' => 'CARTAS',
                'collection' => 'cartas',
                'empty_state' => 'Sin cartas configuradas para esta area.',
            ],
            [
                'key' => 'discrepancia',
                'label' => 'DISCREPANCIA',
                'collection' => 'discrepancias',
                'empty_state' => 'Sin discrepancias configuradas para esta area.',
            ],
            [
                'key' => 'refacciones',
                'label' => 'REFACCIONES',
                'collection' => 'refacciones',
                'empty_state' => 'Sin refacciones configuradas para esta area.',
            ],
            [
                'key' => 'consumible',
                'label' => 'CONSUMIBLE',
                'collection' => 'consumibles',
                'empty_state' => 'Sin consumibles configurados para esta area.',
            ],
            [
                'key' => 'herramienta',
                'label' => 'HERRAMIENTA',
                'collection' => 'herramientas',
                'empty_state' => 'Sin herramienta configurada para esta area.',
            ],
            [
                'key' => 'ndt',
                'label' => 'NDT',
                'collection' => 'ndt',
                'empty_state' => 'Sin registros NDT configurados para esta area.',
            ],
            [
                'key' => 'taller_externo',
                'label' => 'TALLER EXTERNO',
                'collection' => 'talleres',
                'empty_state' => 'Sin talleres externos configurados para esta area.',
            ],
            [
                'key' => 'mediciones',
                'label' => 'MEDICIONES',
                'collection' => 'mediciones',
                'empty_state' => 'Sin mediciones configuradas para esta area.',
            ],
        ];

        $schema['tabs'] = array_map(function (array $definition) use ($existingByCollection): array {
            $collection = strtolower($definition['collection']);
            $existing = $existingByCollection[$collection] ?? [];
            $merged = array_replace_recursive($existing, $definition);
            $merged['fields'] = $existing['fields'] ?? ($merged['fields'] ?? []);
            $merged['presets'] = $existing['presets'] ?? ($merged['presets'] ?? []);

            return $merged;
        }, $definitions);

        return $schema;
    }
}


