<?php

namespace App\Services;

use App\Models\AtaChapter;
use App\Models\AtaSubchapter;
use App\Models\Manual;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;

class ManualProcessingService
{
    private const STOP_WORDS = [
        'the', 'and', 'for', 'with', 'from', 'that', 'this', 'into', 'your', 'have', 'shall',
        'must', 'para', 'con', 'sin', 'por', 'del', 'las', 'los', 'una', 'uno', 'sobre', 'entre',
        'cuando', 'where', 'while', 'will', 'into', 'cada', 'como', 'manual', 'chapter', 'section',
    ];

    public function process(Manual $manual, array $options = []): array
    {
        $replaceChunks = (bool) ($options['replace_chunks'] ?? true);
        $rawText = $options['raw_text'] ?? null;

        $document = $this->loadDocument($manual, $rawText);
        $chunks = $this->buildChunks($document, $manual);

        if (empty($chunks)) {
            throw new RuntimeException('No se encontraron secciones utiles para generar chunks del manual.');
        }

        DB::transaction(function () use ($manual, $chunks, $replaceChunks) {
            if ($replaceChunks) {
                $manual->chunks()->delete();
            }

            foreach ($chunks as $chunkData) {
                $references = $chunkData['referencias'] ?? [];
                unset($chunkData['referencias']);

                $chunk = $manual->chunks()->create($chunkData);

                foreach ($references as $reference) {
                    $chunk->referencias()->create($reference);
                }
            }
        });

        return [
            'manual_id' => $manual->id,
            'document_source' => $document['source'],
            'chunks_created' => count($chunks),
            'ata_detected' => collect($chunks)
                ->pluck('ata_chapter_id')
                ->filter()
                ->unique()
                ->count(),
            'pages_detected' => collect($document['pages'] ?? [])
                ->pluck('page')
                ->filter()
                ->max(),
        ];
    }

    private function loadDocument(Manual $manual, ?string $rawText = null): array
    {
        if (filled($rawText)) {
            return [
                'source' => 'request.raw_text',
                'pages' => $this->parsePagesFromText($rawText),
            ];
        }

        $path = $manual->archivo_path;

        if (! $path || ! File::exists($path)) {
            throw new RuntimeException('El manual no tiene un archivo fuente accesible para procesar.');
        }

        $basePath = preg_replace('/\.[^.]+$/', '', $path) ?: $path;

        foreach ((array) config('manuals.sidecar_extensions', ['json', 'txt']) as $extension) {
            $sidecarPath = $basePath . '.' . $extension;

            if (! File::exists($sidecarPath)) {
                continue;
            }

            if ($extension === 'json') {
                return $this->loadFromJsonSidecar($sidecarPath);
            }

            if ($extension === 'txt') {
                return [
                    'source' => basename($sidecarPath),
                    'pages' => $this->parsePagesFromText(File::get($sidecarPath)),
                ];
            }
        }

        throw new RuntimeException('No hay texto extraido para este PDF. Agrega un archivo .txt o .json con el mismo nombre base del PDF, o envia raw_text al procesar.');
    }

    private function loadFromJsonSidecar(string $path): array
    {
        $payload = json_decode(File::get($path), true);

        if (! is_array($payload)) {
            throw new RuntimeException('El archivo JSON del manual no es valido.');
        }

        if (isset($payload['chunks']) && is_array($payload['chunks'])) {
            return [
                'source' => basename($path),
                'prebuilt_chunks' => $payload['chunks'],
            ];
        }

        if (isset($payload['pages']) && is_array($payload['pages'])) {
            return [
                'source' => basename($path),
                'pages' => collect($payload['pages'])
                    ->map(fn ($page, $index) => [
                        'page' => (int) ($page['page'] ?? ($index + 1)),
                        'text' => (string) ($page['text'] ?? ''),
                    ])
                    ->all(),
            ];
        }

        throw new RuntimeException('El JSON del manual debe incluir pages o chunks.');
    }

    private function buildChunks(array $document, Manual $manual): array
    {
        if (! empty($document['prebuilt_chunks'])) {
            return $this->normalizePrebuiltChunks($document['prebuilt_chunks'], $manual);
        }

        $pages = $document['pages'] ?? [];

        if (empty($pages)) {
            return [];
        }

        $sections = [];
        $current = null;

        foreach ($pages as $page) {
            $lines = preg_split('/\R/', (string) ($page['text'] ?? '')) ?: [];

            foreach ($lines as $line) {
                $trimmed = trim($line);

                if ($trimmed === '') {
                    continue;
                }

                $sectionMatch = [];
                if (preg_match('/^(?<code>\d{2}(?:-\d{2}){1,3})(?:\s+|-|\.)?(?<title>.+)?$/', $trimmed, $sectionMatch) === 1) {
                    if ($current) {
                        $sections[] = $current;
                    }

                    $current = [
                        'codigo_seccion' => $sectionMatch['code'],
                        'titulo' => trim((string) ($sectionMatch['title'] ?? '')) ?: null,
                        'pagina_inicio' => (int) $page['page'],
                        'pagina_fin' => (int) $page['page'],
                        'texto' => [],
                    ];

                    continue;
                }

                if (! $current) {
                    $current = [
                        'codigo_seccion' => null,
                        'titulo' => null,
                        'pagina_inicio' => (int) $page['page'],
                        'pagina_fin' => (int) $page['page'],
                        'texto' => [],
                    ];
                }

                $current['pagina_fin'] = (int) $page['page'];
                $current['texto'][] = $trimmed;
            }
        }

        if ($current) {
            $sections[] = $current;
        }

        $chunks = [];
        $order = 1;

        foreach ($sections as $section) {
            $text = trim(implode("\n", $section['texto']));

            if ($text === '') {
                continue;
            }

            foreach ($this->splitOversizedSection($section, $text) as $segment) {
                $normalized = $this->normalizeChunk([
                    'codigo_seccion' => $segment['codigo_seccion'],
                    'titulo' => $segment['titulo'],
                    'pagina_inicio' => $segment['pagina_inicio'],
                    'pagina_fin' => $segment['pagina_fin'],
                    'texto' => $segment['texto'],
                    'orden' => $order++,
                ], $manual);

                if ($normalized) {
                    $chunks[] = $normalized;
                }
            }
        }

        return $chunks;
    }

    private function normalizePrebuiltChunks(array $chunks, Manual $manual): array
    {
        $normalized = [];
        $order = 1;

        foreach ($chunks as $chunk) {
            if (! is_array($chunk)) {
                continue;
            }

            $payload = $this->normalizeChunk(array_merge($chunk, [
                'orden' => $chunk['orden'] ?? $order,
            ]), $manual);

            if ($payload) {
                $normalized[] = $payload;
                $order++;
            }
        }

        return $normalized;
    }

    private function normalizeChunk(array $chunk, Manual $manual): ?array
    {
        $text = trim((string) ($chunk['texto'] ?? ''));

        if ($text === '') {
            return null;
        }

        $sectionCode = $chunk['codigo_seccion'] ?? null;
        [$chapterId, $subchapterId] = $this->resolveAtaIds(
            $chunk['ata_chapter_id'] ?? null,
            $chunk['ata_subchapter_id'] ?? null,
            $sectionCode
        );

        $title = trim((string) ($chunk['titulo'] ?? '')) ?: $this->inferTitleFromText($text);
        $keywords = $this->extractKeywords($title . ' ' . $text);
        $summary = $this->buildSummary($text);
        $tipo = $chunk['tipo_contenido'] ?? $this->inferContentType($title . ' ' . $text);
        $references = $this->buildReferences($keywords, $sectionCode, $tipo, $title);

        return [
            'manual_id' => $manual->id,
            'ata_chapter_id' => $chapterId,
            'ata_subchapter_id' => $subchapterId,
            'codigo_seccion' => $sectionCode,
            'titulo' => $title,
            'tipo_contenido' => $tipo,
            'pagina_inicio' => $chunk['pagina_inicio'] ?? null,
            'pagina_fin' => $chunk['pagina_fin'] ?? null,
            'orden' => (int) ($chunk['orden'] ?? 0),
            'resumen' => $chunk['resumen'] ?? $summary,
            'keywords' => $chunk['keywords'] ?? $keywords,
            'embedding' => $chunk['embedding'] ?? null,
            'texto' => $text,
            'referencias' => $chunk['referencias'] ?? $references,
        ];
    }

    private function parsePagesFromText(string $text): array
    {
        $normalized = str_replace(["\r\n", "\r"], "\n", $text);
        $parts = preg_split('/\f+/', $normalized) ?: [];

        if (count($parts) > 1) {
            return collect($parts)
                ->map(fn ($pageText, $index) => [
                    'page' => $index + 1,
                    'text' => trim((string) $pageText),
                ])
                ->filter(fn (array $page) => $page['text'] !== '')
                ->values()
                ->all();
        }

        $pages = preg_split('/(?=^\s*(?:page|pagina)\s+\d+\s*$)/im', $normalized) ?: [];

        if (count($pages) > 1) {
            return collect($pages)
                ->map(function ($pageText, $index) {
                    preg_match('/^\s*(?:page|pagina)\s+(?<page>\d+)\s*$/im', $pageText, $matches);

                    return [
                        'page' => (int) ($matches['page'] ?? ($index + 1)),
                        'text' => trim((string) preg_replace('/^\s*(?:page|pagina)\s+\d+\s*$/im', '', $pageText)),
                    ];
                })
                ->filter(fn (array $page) => $page['text'] !== '')
                ->values()
                ->all();
        }

        return [[
            'page' => 1,
            'text' => trim($normalized),
        ]];
    }

    private function splitOversizedSection(array $section, string $text): array
    {
        $maxChars = (int) config('manuals.chunk.max_chars', 5000);
        $minChars = (int) config('manuals.chunk.min_chars', 400);

        if (mb_strlen($text) <= $maxChars) {
            return [[
                'codigo_seccion' => $section['codigo_seccion'],
                'titulo' => $section['titulo'],
                'pagina_inicio' => $section['pagina_inicio'],
                'pagina_fin' => $section['pagina_fin'],
                'texto' => $text,
            ]];
        }

        $paragraphs = preg_split('/\n{2,}/', $text) ?: [$text];
        $segments = [];
        $buffer = '';

        foreach ($paragraphs as $paragraph) {
            $candidate = trim($buffer === '' ? $paragraph : $buffer . "\n\n" . $paragraph);

            if ($candidate !== '' && mb_strlen($candidate) <= $maxChars) {
                $buffer = $candidate;
                continue;
            }

            if ($buffer !== '') {
                $segments[] = $buffer;
            }

            if (mb_strlen($paragraph) > $maxChars) {
                foreach (str_split($paragraph, $maxChars) as $slice) {
                    $slice = trim($slice);

                    if ($slice !== '') {
                        $segments[] = $slice;
                    }
                }

                $buffer = '';
                continue;
            }

            $buffer = trim($paragraph);
        }

        if ($buffer !== '') {
            if (! empty($segments) && mb_strlen($buffer) < $minChars) {
                $segments[count($segments) - 1] = trim($segments[count($segments) - 1] . "\n\n" . $buffer);
            } else {
                $segments[] = $buffer;
            }
        }

        return collect($segments)
            ->map(fn ($segment) => [
                'codigo_seccion' => $section['codigo_seccion'],
                'titulo' => $section['titulo'],
                'pagina_inicio' => $section['pagina_inicio'],
                'pagina_fin' => $section['pagina_fin'],
                'texto' => trim($segment),
            ])
            ->all();
    }

    private function resolveAtaIds(mixed $chapterId, mixed $subchapterId, ?string $sectionCode): array
    {
        if ($chapterId || $subchapterId) {
            return [$chapterId, $subchapterId];
        }

        if (! $sectionCode) {
            return [null, null];
        }

        $parts = explode('-', $sectionCode);
        $chapterCode = $parts[0] ?? null;
        $subchapterCode = isset($parts[1]) ? $chapterCode . '-' . $parts[1] : null;

        $chapter = $chapterCode ? AtaChapter::query()->where('codigo', $chapterCode)->first() : null;
        $subchapter = $subchapterCode ? AtaSubchapter::query()->where('codigo', $subchapterCode)->first() : null;

        if ($chapter || $subchapter) {
            return [$chapter?->id, $subchapter?->id];
        }

        return $this->defaultAtaIds();
    }

    private function defaultAtaIds(): array
    {
        $chapterCode = (string) config('manuals.default_ata_chapter_code', '100');
        $subchapterCode = (string) config('manuals.default_ata_subchapter_code', '100-10');

        $chapterId = AtaChapter::query()
            ->where('codigo', $chapterCode)
            ->value('id');

        $subchapterId = AtaSubchapter::query()
            ->where('codigo', $subchapterCode)
            ->value('id');

        return [$chapterId, $subchapterId];
    }

    private function inferTitleFromText(string $text): string
    {
        $line = trim((string) Str::of($text)->explode("\n")->first());

        return Str::limit($line !== '' ? $line : 'Seccion sin titulo', 255, '');
    }

    private function buildSummary(string $text): string
    {
        $limit = (int) config('manuals.chunk.summary_length', 280);

        return Str::limit(preg_replace('/\s+/', ' ', trim($text)) ?? trim($text), $limit, '...');
    }

    private function inferContentType(string $text): string
    {
        $normalized = Str::lower(Str::ascii($text));

        if (str_contains($normalized, 'warning') || str_contains($normalized, 'caution') || str_contains($normalized, 'advertencia')) {
            return 'warning';
        }

        if (str_contains($normalized, 'inspection') || str_contains($normalized, 'inspeccion')) {
            return 'inspeccion';
        }

        if (str_contains($normalized, 'task') || str_contains($normalized, 'procedure') || str_contains($normalized, 'procedimiento') || str_contains($normalized, 'troubleshooting')) {
            return 'procedimiento';
        }

        return 'general';
    }

    private function extractKeywords(string $text): array
    {
        $normalized = Str::lower(Str::ascii($text));
        $limit = (int) config('manuals.chunk.keyword_limit', 12);

        return collect(preg_split('/[^a-z0-9]+/', $normalized) ?: [])
            ->filter(fn (string $token) => strlen($token) >= 4 && ! in_array($token, self::STOP_WORDS, true))
            ->countBy()
            ->sortDesc()
            ->keys()
            ->take($limit)
            ->values()
            ->all();
    }

    private function buildReferences(array $keywords, ?string $sectionCode, string $contentType, ?string $title): array
    {
        $references = collect($keywords)
            ->map(fn (string $keyword) => [
                'tipo' => 'keyword',
                'valor' => $keyword,
            ]);

        if ($sectionCode) {
            $references->push([
                'tipo' => 'section',
                'valor' => $sectionCode,
            ]);
        }

        if ($title) {
            $references->push([
                'tipo' => $contentType,
                'valor' => Str::limit($title, 255, ''),
            ]);
        }

        return $references
            ->unique(fn (array $reference) => $reference['tipo'] . ':' . $reference['valor'])
            ->values()
            ->all();
    }
}
