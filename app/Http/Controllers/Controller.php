<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Support\PublicStoragePath;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\HttpException;

abstract class Controller
{
    private const AREA_CONTEXT_ATTRIBUTE = '_area_access_context';

    protected function areaAccessContext(Request $request): array
    {
        if ($request->attributes->has(self::AREA_CONTEXT_ATTRIBUTE)) {
            return $request->attributes->get(self::AREA_CONTEXT_ATTRIBUTE);
        }

        $user = $request->user();
        $global = false;

        if ($user) {
            if ($user->rol === 'admin') {
                $global = true;
            } elseif (in_array($user->rol, ['supervisor', 'administracion'], true)) {
                if ($user->area_id === null) {
                    $global = true;
                } else {
                    $area = $user->relationLoaded('area') ? $user->area : $user->area()->first();

                    $global = ! $area
                        || strtoupper((string) $area->codigo) === 'GENERAL'
                        || strtoupper((string) $area->nombre) === 'GENERAL';
                }
            }
        }

        $context = [
            'global' => $global,
            'current_area_id' => $user?->area_id,
        ];

        $request->attributes->set(self::AREA_CONTEXT_ATTRIBUTE, $context);

        return $context;
    }

    protected function hasGlobalAreaAccess(Request $request): bool
    {
        return $this->areaAccessContext($request)['global'];
    }

    protected function currentAreaId(Request $request): ?int
    {
        return $this->areaAccessContext($request)['current_area_id'];
    }

    protected function requestedAreaId(Request $request, string $key = 'area_id'): ?int
    {
        return $request->filled($key) ? $request->integer($key) : null;
    }

    protected function effectiveAreaId(Request $request, string $key = 'area_id'): ?int
    {
        if ($this->hasGlobalAreaAccess($request)) {
            return $this->requestedAreaId($request, $key);
        }

        return $this->currentAreaId($request);
    }

    protected function applyAreaScope(Request $request, Builder $query, string $column = 'area_id'): Builder
    {
        $areaId = $this->effectiveAreaId($request);

        return $areaId ? $query->where($column, $areaId) : $query;
    }

    protected function applyOrderAreaScope(Request $request, Builder $query, string $relation = 'orden'): Builder
    {
        $areaId = $this->effectiveAreaId($request);

        return $areaId
            ? $query->whereHas($relation, fn (Builder $order) => $order->where('area_id', $areaId))
            : $query;
    }

    protected function authorizeAreaId(Request $request, ?int $areaId): void
    {
        if ($areaId === null || $this->hasGlobalAreaAccess($request) || $areaId === $this->currentAreaId($request)) {
            return;
        }

        throw new HttpException(403, 'No autorizado para acceder a esta area.');
    }

    protected function authorizeModelArea(Request $request, Model $model, string $column = 'area_id'): void
    {
        $this->authorizeAreaId($request, $model->getAttribute($column));
    }

    protected function authorizeOrderArea(Request $request, Model $model, string $relation = 'orden'): void
    {
        $order = $model->relationLoaded($relation) ? $model->getRelation($relation) : $model->{$relation};

        $this->authorizeAreaId($request, $order?->area_id);
    }

    protected function areaCacheContext(Request $request): array
    {
        return [
            'user_id' => $request->user()?->id,
            'role' => $request->user()?->rol,
            'area_id' => $this->effectiveAreaId($request),
            'global' => $this->hasGlobalAreaAccess($request),
        ];
    }

    protected function applyIndexedPrefixSearch(Builder|QueryBuilder $query, string $column, mixed $value): Builder|QueryBuilder
    {
        $term = trim((string) $value);

        if ($term === '') {
            return $query;
        }

        return $query->where($column, 'like', $term . '%');
    }

    protected function cacheOrFetch(string $cacheKey, \DateTimeInterface $ttl, callable $callback): mixed
    {
        if (! app()->environment('production')) {
            return $callback();
        }

        return Cache::remember($cacheKey, $ttl, $callback);
    }

    protected function cacheForeverOrFetch(string $cacheKey, callable $callback): mixed
    {
        if (! app()->environment('production')) {
            return $callback();
        }

        return Cache::rememberForever($cacheKey, $callback);
    }

    protected function canManageInventoryPricing(Request $request): bool
    {
        return in_array($request->user()?->rol, ['admin', 'supervisor', 'administracion'], true)
            || $this->isComprasUser($request);
    }

    protected function normalizedUserEmail(Request $request): string
    {
        return strtolower(trim((string) $request->user()?->email));
    }

    protected function isEngineeringUser(Request $request): bool
    {
        return $this->normalizedUserEmail($request) === 'ing@redaviation.com';
    }

    protected function isAdministracionUser(Request $request): bool
    {
        return $request->user()?->rol === 'administracion'
            || $this->normalizedUserEmail($request) === 'administracion@redaviation.com';
    }

    protected function isComprasUser(Request $request): bool
    {
        $user = $request->user();
        $email = $this->normalizedUserEmail($request);
        $role = strtolower(trim((string) $user?->rol));
        $roleName = strtolower(trim((string) $user?->rol_nombre));
        $areaCode = strtoupper(trim((string) $user?->area?->codigo));

        return $email === 'compras@redaviation.com'
            || in_array($role, ['compras', 'purchasing'], true)
            || in_array($roleName, ['compras', 'purchasing'], true)
            || $areaCode === 'COMPRAS';
    }

    protected function canCaptureInventoryCost(Request $request): bool
    {
        return $this->isAdministracionUser($request)
            || $this->isComprasUser($request)
            || in_array($request->user()?->rol, ['admin', 'supervisor'], true);
    }

    protected function isExclusiveAdministracionEmail(Request $request): bool
    {
        return $this->normalizedUserEmail($request) === 'administracion@redaviation.com';
    }

    protected function authorizeExclusiveAdministracionEmail(
        Request $request,
        string $message = 'Solo administracion@redaviation.com puede modificar este registro.',
    ): void {
        if ($this->isExclusiveAdministracionEmail($request)) {
            return;
        }

        throw new HttpException(403, $message);
    }

    protected function isAdminGeneralUser(Request $request): bool
    {
        return $request->user()?->rol === 'admin'
            && ! $this->isEngineeringUser($request)
            && ! $this->isAdministracionUser($request);
    }

    protected function isTecnicoUser(Request $request): bool
    {
        return $request->user()?->rol === 'tecnico';
    }

    protected function isClienteUser(Request $request): bool
    {
        return $request->user() instanceof Cliente
            || $request->user()?->rol === 'cliente';
    }

    protected function currentClientNames(Request $request): array
    {
        $user = $request->user();

        if ($user instanceof Cliente) {
            return array_values(array_filter(array_unique([
                trim((string) $user->nombre_comercial),
                trim((string) $user->razon_social),
                trim((string) $user->contacto_nombre),
            ])));
        }

        return [];
    }

    protected function authorizeTecnicoOnly(Request $request, string $message = 'Solo el tecnico del area puede modificar este registro.'): void
    {
        if ($this->isTecnicoUser($request)) {
            return;
        }

        throw new HttpException(403, $message);
    }

    protected function authorizeEngineeringOrTecnico(Request $request, string $message = 'Solo Ingenieria o el tecnico del area pueden modificar este registro.'): void
    {
        if ($this->isTecnicoUser($request) || $this->isEngineeringUser($request)) {
            return;
        }

        throw new HttpException(403, $message);
    }

    protected function authorizeOperationalPayload(
        Request $request,
        array $payload,
        array $operationalKeys,
        bool $allowEngineering = false,
    ): void {
        $hasOperationalChanges = false;

        foreach ($operationalKeys as $key) {
            if (! array_key_exists($key, $payload)) {
                continue;
            }

            $hasOperationalChanges = true;
            break;
        }

        if (! $hasOperationalChanges) {
            return;
        }

        if ($allowEngineering) {
            $this->authorizeEngineeringOrTecnico($request);

            return;
        }

        $this->authorizeTecnicoOnly($request);
    }

    protected function authorizeNestedOperationalPayload(
        Request $request,
        array $items,
        array $operationalKeys,
        bool $allowEngineering = false,
    ): void {
        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $this->authorizeOperationalPayload(
                $request,
                $item,
                $operationalKeys,
                $allowEngineering,
            );
        }
    }

    protected function authorizeOrderAreaCodeAllowed(Request $request, int $orderId, array $allowedCodes): void
    {
        $order = \App\Models\Orden::query()
            ->with('area:id,codigo')
            ->findOrFail($orderId);

        $this->authorizeAreaId($request, $order->area_id);

        $code = strtoupper(trim((string) $order->area?->codigo));
        $allowed = array_map(fn ($item) => strtoupper(trim((string) $item)), $allowedCodes);

        if ($code !== '' && in_array($code, $allowed, true)) {
            return;
        }

        throw new HttpException(403, 'Este modulo no esta habilitado para el area de la OT.');
    }

    protected function authorizeInventoryPricing(Request $request): void
    {
        if ($this->canManageInventoryPricing($request)) {
            return;
        }

        throw new HttpException(403, 'No autorizado para capturar precios en inventario.');
    }

    protected function authorizeInventoryPricingIfPresent(Request $request, array $payload): void
    {
        $costKeys = ['costo_total', 'costo'];

        foreach ($costKeys as $costKey) {
            if (array_key_exists($costKey, $payload) && ! $this->canCaptureInventoryCost($request)) {
                throw new HttpException(403, 'Solo Administracion/Inventario puede capturar costo.');
            }
        }

        if (array_key_exists('precio_venta', $payload) && ! $this->isAdminGeneralUser($request)) {
            throw new HttpException(403, 'Solo el administrador general puede capturar precio de venta.');
        }
    }

    protected function authorizeAdministracionFields(Request $request, array $payload, array $keys): void
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $payload) && ! $this->isAdministracionUser($request)) {
                throw new HttpException(403, 'Solo Administracion/Inventario puede capturar este campo.');
            }
        }
    }

    protected function authorizeNestedInventoryPricing(Request $request, array $items): void
    {
        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $this->authorizeInventoryPricingIfPresent($request, $item);
        }
    }

    protected function storeIncomingImage(
        Request $request,
        array &$data,
        string $targetKey,
        string $directory,
        array $aliases = [],
        bool $requireCloudinary = true
    ): void
    {
        $keys = $this->imageInputKeys($targetKey, $aliases);

        foreach ($keys as $key) {
            $uploadedFile = $request->file($key);

            if ($uploadedFile instanceof UploadedFile && $uploadedFile->isValid()) {
                $data[$targetKey] = $this->storeUploadedImage($uploadedFile, $directory, $requireCloudinary);

                return;
            }

            $value = $request->input($key);

            if (! is_string($value) || trim($value) === '') {
                continue;
            }

            $storedPath = $this->storeBase64Image($value, $directory, $requireCloudinary);

            if ($storedPath !== null) {
                $data[$targetKey] = $storedPath;

                return;
            }

            if ($key !== $targetKey && ! array_key_exists($targetKey, $data)) {
                $data[$targetKey] = PublicStoragePath::normalize($value) ?? $value;
            }
        }
    }

    protected function storeIncomingImageFromData(
        array &$data,
        string $targetKey,
        string $directory,
        array $aliases = [],
        bool $requireCloudinary = true
    ): void
    {
        $keys = $this->imageInputKeys($targetKey, $aliases);

        foreach ($keys as $key) {
            $value = $data[$key] ?? null;

            if (! is_string($value) || trim($value) === '') {
                continue;
            }

            $storedPath = $this->storeBase64Image($value, $directory, $requireCloudinary);

            if ($storedPath !== null) {
                $data[$targetKey] = $storedPath;

                return;
            }

            if ($key !== $targetKey && ! array_key_exists($targetKey, $data)) {
                $data[$targetKey] = PublicStoragePath::normalize($value) ?? $value;
            }
        }
    }

    protected function replaceStoredImage(?string $currentPath, ?string $newPath): void
    {
        $currentPath = PublicStoragePath::normalize($currentPath);
        $newPath = PublicStoragePath::normalize($newPath);

        if ($currentPath && $newPath && $currentPath !== $newPath) {
            $this->deleteStoredImage($currentPath);
        }
    }

    protected function deleteStoredImage(?string $path): void
    {
        $path = PublicStoragePath::normalize($path);

        if ($path === null) {
            return;
        }

        if ($this->isCloudinaryUrl($path)) {
            $this->deleteCloudinaryAsset($path);
            return;
        }

        if (! Str::startsWith($path, ['http://', 'https://'])) {
            Storage::disk('public')->delete($path);
        }
    }

    protected function publicFileUrl(?string $path): ?string
    {
        $normalizedPath = PublicStoragePath::normalize($path);

        if ($normalizedPath === null) {
            return null;
        }

        if (PublicStoragePath::isExternalUrl($normalizedPath) || Str::startsWith($normalizedPath, ['http://', 'https://'])) {
            return $normalizedPath;
        }

        if (! Storage::disk('public')->exists($normalizedPath)) {
            return null;
        }

        return PublicStoragePath::url($normalizedPath);
    }

    protected function exposePublicFileUrl(Model|EloquentCollection $resource, string $pathKey, ?string $aliasKey = null): Model|EloquentCollection
    {
        if ($resource instanceof EloquentCollection) {
            return $resource->map(fn (Model $model) => $this->exposePublicFileUrl($model, $pathKey, $aliasKey));
        }

        $url = $this->publicFileUrl($resource->getAttribute($pathKey));

        $resource->setAttribute($pathKey, $url);

        if ($aliasKey !== null) {
            $resource->setAttribute($aliasKey, $url);
        }

        return $resource;
    }


    protected function imageInputKeys(string $targetKey, array $aliases = []): array
    {
        return array_values(array_unique([
            $targetKey,
            ...$aliases,
            ...$this->defaultImageAliasesForKey($targetKey),
        ]));
    }

    protected function defaultImageAliasesForKey(string $targetKey): array
    {
        $common = [
            'foto_url',
            'imagen_url',
            'evidencia_url',
            'secure_url',
            'secureUrl',
            'url',
            'src',
            'cloudinary_url',
            'cloudinaryUrl',
            'cloudinary_secure_url',
            'cloudinarySecureUrl',
            'foto_cloudinary_url',
            'imagen_cloudinary_url',
            'evidencia_cloudinary_url',
        ];

        return match ($targetKey) {
            'certificado_conformidad_imagen' => [
                ...$common,
                'certificado_conformidad_imagen_url',
                'certificado_conformidad_imagen_cloudinary_url',
                'certificado_conformidad_foto',
                'certificado_conformidad_foto_url',
                'certificado_conformidad_foto_cloudinary_url',
                'foto_certificado',
                'foto_certificado_url',
                'foto_certificado_cloudinary_url',
                'certificado_foto',
                'certificado_foto_url',
                'certificado_foto_cloudinary_url',
            ],
            default => $common,
        };
    }

    private function storeBase64Image(string $value, string $directory, bool $requireCloudinary = true): ?string
    {
        if (! preg_match('/^data:image\/(?P<extension>[a-zA-Z0-9.+-]+);base64,(?P<data>.+)$/', trim($value), $matches)) {
            return null;
        }

        $binary = base64_decode($matches['data'], true);

        if ($binary === false) {
            return null;
        }

        $extension = strtolower($matches['extension']);
        $extension = match ($extension) {
            'jpeg' => 'jpg',
            'svg+xml' => 'svg',
            default => $extension,
        };

        if (! in_array($extension, ['jpg', 'png', 'webp', 'gif', 'bmp', 'svg'], true)) {
            $extension = 'jpg';
        }

        $path = trim($directory, '/') . '/' . Str::uuid() . '.' . $extension;

        if ($this->shouldUseCloudinary()) {
            return $this->uploadToCloudinary($binary, $path);
        }

        $this->ensureLocalImageStorageAllowed($requireCloudinary);

        Storage::disk('public')->put($path, $binary);

        return $path;
    }

    private function storeUploadedImage(UploadedFile $uploadedFile, string $directory, bool $requireCloudinary = true): string
    {
        $path = trim($directory, '/') . '/' . Str::uuid() . '.' . $uploadedFile->getClientOriginalExtension();

        if ($this->shouldUseCloudinary()) {
            $binary = file_get_contents($uploadedFile->getRealPath());

            if ($binary === false) {
                throw new RuntimeException('No se pudo leer la imagen subida.');
            }

            return $this->uploadToCloudinary($binary, $path);
        }

        $this->ensureLocalImageStorageAllowed($requireCloudinary);

        return $uploadedFile->store($directory, 'public');
    }

    private function ensureLocalImageStorageAllowed(bool $requireCloudinary = true): void
    {
        if (! $requireCloudinary && ! app()->environment('production')) {
            return;
        }

        throw new RuntimeException(
            'Cloudinary no esta configurado en el backend. '
            . 'Configura CLOUDINARY_CLOUD_NAME, CLOUDINARY_API_KEY y CLOUDINARY_API_SECRET.'
        );
    }

    private function shouldUseCloudinary(): bool
    {
        return filled(config('services.cloudinary.cloud_name'))
            && filled(config('services.cloudinary.api_key'))
            && filled(config('services.cloudinary.api_secret'));
    }

    private function uploadToCloudinary(string $binary, string $path): string
    {
        $cloudName = (string) config('services.cloudinary.cloud_name');
        $apiKey = (string) config('services.cloudinary.api_key');
        $apiSecret = (string) config('services.cloudinary.api_secret');
        $baseFolder = trim((string) config('services.cloudinary.folder', ''), '/');
        $timestamp = time();
        $relativeId = pathinfo(str_replace('\\', '/', $path), PATHINFO_DIRNAME);
        $relativeId = trim($relativeId === '.' ? '' : $relativeId, '/')
            . ($relativeId === '' ? '' : '/')
            . pathinfo($path, PATHINFO_FILENAME);
        $relativeId = trim($relativeId, '/');
        $publicId = trim(($baseFolder !== '' ? $baseFolder . '/' : '') . $relativeId, '/');

        $signature = $this->cloudinarySignature([
            'public_id' => $publicId,
            'timestamp' => $timestamp,
        ], $apiSecret);

        $response = Http::attach('file', $binary, basename($path))
            ->asMultipart()
            ->post("https://api.cloudinary.com/v1_1/{$cloudName}/image/upload", [
                'api_key' => $apiKey,
                'public_id' => $publicId,
                'timestamp' => $timestamp,
                'signature' => $signature,
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('No se pudo subir la imagen a Cloudinary.');
        }

        return (string) $response->json('secure_url');
    }

    private function deleteCloudinaryAsset(string $url): void
    {
        if (! $this->shouldUseCloudinary()) {
            return;
        }

        $publicId = $this->extractCloudinaryPublicId($url);

        if ($publicId === null) {
            return;
        }

        $timestamp = time();
        $apiSecret = (string) config('services.cloudinary.api_secret');
        $apiKey = (string) config('services.cloudinary.api_key');
        $cloudName = (string) config('services.cloudinary.cloud_name');
        $signature = $this->cloudinarySignature([
            'public_id' => $publicId,
            'timestamp' => $timestamp,
        ], $apiSecret);

        Http::asForm()->post("https://api.cloudinary.com/v1_1/{$cloudName}/image/destroy", [
            'api_key' => $apiKey,
            'public_id' => $publicId,
            'timestamp' => $timestamp,
            'signature' => $signature,
        ]);
    }

    private function cloudinarySignature(array $params, string $apiSecret): string
    {
        ksort($params);

        $signatureBase = collect($params)
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->map(fn ($value, $key) => $key . '=' . $value)
            ->implode('&');

        return sha1($signatureBase . $apiSecret);
    }

    private function isCloudinaryUrl(string $path): bool
    {
        return Str::contains($path, 'res.cloudinary.com/');
    }

    private function extractCloudinaryPublicId(string $url): ?string
    {
        $normalized = strtok($url, '?') ?: $url;

        if (! preg_match('#/image/upload/(?:v\d+/)?(?P<public_id>.+)\.(?:jpg|jpeg|png|webp|gif|bmp|svg)$#i', $normalized, $matches)) {
            return null;
        }

        return $matches['public_id'] ?? null;
    }
}

