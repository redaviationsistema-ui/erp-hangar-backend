<?php

namespace App\Support;

use Illuminate\Support\Str;

class PublicStoragePath
{
    private const STORAGE_DIRECTORIES = [
        'discrepancias',
        'tareas',
        'refacciones',
        'consumibles',
        'herramientas',
        'ndt',
        'talleres-externos',
        'mediciones',
    ];

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

            $normalized = self::normalizeKnownStoragePath($path);

            return $normalized ?? $value;
        }

        return self::normalizeKnownStoragePath($value) ?? ltrim($value, '/');
    }

    public static function isExternalUrl(?string $value): bool
    {
        $normalized = trim((string) $value);

        if ($normalized === '' || ! Str::startsWith($normalized, ['http://', 'https://'])) {
            return false;
        }

        return self::normalize($normalized) === $normalized;
    }

    public static function url(string $path): string
    {
        return asset('storage/' . ltrim(str_replace('\\', '/', $path), '/'));
    }

    private static function normalizeKnownStoragePath(string $value): ?string
    {
        return self::normalizePublicStoragePrefix($value)
            ?? self::normalizeDirectoryPath($value);
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

    private static function normalizeDirectoryPath(string $value): ?string
    {
        $path = ltrim(trim(str_replace('\\', '/', $value)), '/');

        foreach (self::STORAGE_DIRECTORIES as $directory) {
            if (Str::startsWith($path, $directory . '/')) {
                return $path;
            }
        }

        return null;
    }
}
