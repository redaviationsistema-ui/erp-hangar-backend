<?php

namespace App\Support;

use Illuminate\Support\Str;

class PublicStoragePath
{
    public static function normalize(?string $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        if ($value === '') {
            return null;
        }

        if (Str::startsWith($value, ['http://', 'https://'])) {
            $path = parse_url($value, PHP_URL_PATH);

            if (! is_string($path)) {
                return $value;
            }

            $normalized = self::normalizePublicStoragePrefix($path);

            return $normalized ?? $value;
        }

        return self::normalizePublicStoragePrefix($value) ?? ltrim($value, '/');
    }

    public static function isExternalUrl(?string $value): bool
    {
        $normalized = trim((string) $value);

        if ($normalized === '' || ! Str::startsWith($normalized, ['http://', 'https://'])) {
            return false;
        }

        return self::normalize($normalized) === $normalized;
    }

    private static function normalizePublicStoragePrefix(string $value): ?string
    {
        $path = trim(str_replace('\\', '/', $value));

        foreach (['/public/storage/', 'public/storage/', '/storage/', 'storage/'] as $prefix) {
            if (Str::startsWith($path, $prefix)) {
                return ltrim(Str::after($path, $prefix), '/');
            }
        }

        return null;
    }
}
