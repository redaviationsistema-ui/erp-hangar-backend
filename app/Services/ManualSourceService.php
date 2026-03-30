<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;

class ManualSourceService
{
    public function sourcePath(): string
    {
        return (string) config('manuals.source_path');
    }

    public function listFiles(): array
    {
        $path = $this->sourcePath();

        if (! File::isDirectory($path)) {
            return [];
        }

        $extensions = collect(config('manuals.allowed_extensions', ['pdf']))
            ->map(fn ($extension) => Str::lower((string) $extension))
            ->all();

        return collect(File::files($path))
            ->filter(fn (\SplFileInfo $file) => in_array(Str::lower($file->getExtension()), $extensions, true))
            ->map(fn (\SplFileInfo $file) => [
                'nombre_archivo' => $file->getFilename(),
                'ruta_absoluta' => $file->getRealPath(),
                'extension' => Str::lower($file->getExtension()),
                'tamano_bytes' => $file->getSize(),
                'ultima_modificacion' => date('Y-m-d H:i:s', $file->getMTime()),
            ])
            ->sortBy('nombre_archivo')
            ->values()
            ->all();
    }

    public function resolveFile(string $filename): array
    {
        $sanitized = trim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $filename));
        $path = $this->sourcePath() . DIRECTORY_SEPARATOR . ltrim($sanitized, DIRECTORY_SEPARATOR);
        $realPath = realpath($path);
        $sourceRoot = realpath($this->sourcePath());

        if ($realPath === false || $sourceRoot === false || ! str_starts_with($realPath, $sourceRoot)) {
            throw new RuntimeException('El archivo solicitado no existe dentro de la carpeta de manuales.');
        }

        if (! File::exists($realPath) || ! File::isFile($realPath)) {
            throw new RuntimeException('El archivo solicitado no existe.');
        }

        if (Str::lower(pathinfo($realPath, PATHINFO_EXTENSION)) !== 'pdf') {
            throw new RuntimeException('Solo se permiten archivos PDF.');
        }

        return [
            'nombre_archivo' => basename($realPath),
            'ruta_absoluta' => $realPath,
            'tamano_bytes' => File::size($realPath),
            'ultima_modificacion' => date('Y-m-d H:i:s', File::lastModified($realPath)),
        ];
    }
}
