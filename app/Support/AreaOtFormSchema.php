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

        return self::mergeSchema($default, $override);
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
}
