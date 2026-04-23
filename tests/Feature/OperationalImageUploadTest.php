<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Orden;
use App\Models\TipoOrden;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OperationalImageUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_operational_tables_store_uploaded_images(): void
    {
        [$orden, $user] = $this->createOperationalOrder();
        $this->authenticateAsUser($user);
        $this->fakeCloudinary();

        $this->post('/api/v1/discrepancias', [
            'orden_id' => $orden->id,
            'descripcion' => 'Discrepancia con evidencia',
            'foto' => $this->fakeJpeg('discrepancia.jpg'),
        ])
            ->assertCreated()
            ->assertJsonPath('data.imagen_archivo', 'https://res.cloudinary.com/demo/image/upload/red-aviation/test/upload.jpg');

        $this->post('/api/v1/refacciones', [
            'orden_id' => $orden->id,
            'descripcion' => 'Refaccion con certificado',
            'nombre' => 'Filtro',
            'cantidad' => 1,
            'certificado_conformidad_foto' => $this->fakeJpeg('certificado.jpg'),
        ])
            ->assertCreated()
            ->assertJsonPath('data.certificado_conformidad_imagen', 'https://res.cloudinary.com/demo/image/upload/red-aviation/test/upload.jpg')
            ->assertJsonPath('data.certificado_conformidad_imagen_url', 'https://res.cloudinary.com/demo/image/upload/red-aviation/test/upload.jpg');

        $this->post('/api/v1/talleres', [
            'orden_id' => $orden->id,
            'tarea' => 'REPARACION',
            'foto' => $this->fakeJpeg('taller.jpg'),
        ])
            ->assertCreated()
            ->assertJsonPath('data.foto_path', 'https://res.cloudinary.com/demo/image/upload/red-aviation/test/upload.jpg')
            ->assertJsonPath('data.foto_url', 'https://res.cloudinary.com/demo/image/upload/red-aviation/test/upload.jpg');

        $this->post('/api/v1/ndt', [
            'orden_id' => $orden->id,
            'tipo_prueba' => 'INSPECCION VISUAL',
            'foto' => $this->fakeJpeg('ndt.jpg'),
        ])
            ->assertCreated()
            ->assertJsonPath('data.evidencia_path', 'https://res.cloudinary.com/demo/image/upload/red-aviation/test/upload.jpg')
            ->assertJsonPath('data.evidencia_url', 'https://res.cloudinary.com/demo/image/upload/red-aviation/test/upload.jpg');
    }

    private function createOperationalOrder(): array
    {
        $area = Area::create([
            'nombre' => 'Hangar',
            'codigo' => 'HANG',
            'numero' => '02',
        ]);

        $tipo = TipoOrden::create([
            'nombre' => 'Hangar',
            'codigo' => 'HANG',
        ]);

        $user = User::create([
            'name' => 'Tecnico Imagenes',
            'email' => 'tecnico-imagenes@redaviation.test',
            'password' => 'secret123',
            'area_id' => $area->id,
            'rol' => 'tecnico',
        ]);

        $orden = Orden::create([
            'area_id' => $area->id,
            'tipo_id' => $tipo->id,
            'user_id' => $user->id,
            'folio' => 'CESA-HANG-2026-001',
            'consecutivo' => 1,
            'anio' => 2026,
            'fecha' => now()->toDateString(),
            'estado' => 'abierta',
            'descripcion' => 'Orden para validar imagenes',
        ]);

        return [$orden, $user];
    }

    private function fakeCloudinary(): void
    {
        Config::set('services.cloudinary.cloud_name', 'demo');
        Config::set('services.cloudinary.api_key', 'key');
        Config::set('services.cloudinary.api_secret', 'secret');
        Config::set('services.cloudinary.folder', 'red-aviation/test');

        Http::fake([
            'https://api.cloudinary.com/*' => Http::response([
                'secure_url' => 'https://res.cloudinary.com/demo/image/upload/red-aviation/test/upload.jpg',
            ]),
        ]);
    }

    private function fakeJpeg(string $name): UploadedFile
    {
        return UploadedFile::fake()->createWithContent(
            $name,
            base64_decode('/9j/4AAQSkZJRgABAQAAAQABAAD/2w==') ?: 'jpeg'
        );
    }
}
