<?php

namespace Tests\Feature;

use App\Http\Resources\DiscrepanciaResource;
use App\Models\Discrepancia;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicStorageImageTest extends TestCase
{
    public function test_discrepancia_resource_normalizes_legacy_public_storage_url(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('discrepancias/test.png', 'image-content');

        $resource = DiscrepanciaResource::make(new Discrepancia([
            'imagen_path' => 'https://erp-hangar-backend.onrender.com/public/storage/discrepancias/test.png',
        ]));

        $payload = $resource->resolve(request());

        $this->assertSame('discrepancias/test.png', $payload['imagen_archivo']);
        $this->assertStringEndsWith('/storage/discrepancias/test.png', $payload['imagen_path']);
        $this->assertSame($payload['imagen_path'], $payload['foto']);
    }

    public function test_discrepancia_resource_normalizes_bare_directory_url(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('discrepancias/test.png', 'image-content');

        $resource = DiscrepanciaResource::make(new Discrepancia([
            'imagen_path' => 'https://erp-hangar-backend.onrender.com/discrepancias/test.png',
        ]));

        $payload = $resource->resolve(request());

        $this->assertSame('discrepancias/test.png', $payload['imagen_archivo']);
        $this->assertStringEndsWith('/storage/discrepancias/test.png', $payload['imagen_path']);
        $this->assertSame($payload['imagen_path'], $payload['foto_url']);
    }

    public function test_legacy_public_storage_route_serves_files(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('discrepancias/test.png', 'image-content');

        $response = $this->get('/public/storage/discrepancias/test.png');

        $response->assertOk();
        $this->assertSame('image/png', $response->headers->get('content-type'));
    }
}
