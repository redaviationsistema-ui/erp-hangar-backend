<?php

namespace Database\Seeders;

use App\Models\Cliente;
use Illuminate\Database\Seeder;

class ClienteSeeder extends Seeder
{
    public function run(): void
    {
        $clientes = [
            [
                'nombre_comercial' => 'Cliente Demo',
                'razon_social' => 'Cliente Demo SA de CV',
                'rfc' => 'CDE260406CC3',
                'contacto_nombre' => 'Cliente Demo',
                'email' => 'cliente.demo@redaviation.com',
                'telefono' => '555-100-2000',
                'ciudad' => 'Ciudad de Mexico',
                'estatus' => 'Activo',
                'notas' => 'Cliente demo separado de la tabla users.',
            ],
            [
                'nombre_comercial' => 'Aerolineas Demo',
                'razon_social' => 'Aerolineas Demo SA de CV',
                'rfc' => 'ADE260406AA1',
                'contacto_nombre' => 'Laura Mendez',
                'email' => 'mantenimiento@aerolineasdemo.com',
                'password' => 'Cliente123!',
                'telefono' => '555-100-2001',
                'ciudad' => 'Ciudad de Mexico',
                'estatus' => 'Activo',
                'notas' => 'Cliente semilla para pruebas ERP.',
            ],
            [
                'nombre_comercial' => 'Heli Servicios',
                'razon_social' => 'Heli Servicios Ejecutivos SA de CV',
                'rfc' => 'HSE260406BB2',
                'contacto_nombre' => 'Carlos Ruiz',
                'email' => 'operaciones@heliservicios.com',
                'password' => 'Cliente123!',
                'telefono' => '555-100-2002',
                'ciudad' => 'Toluca',
                'estatus' => 'Prospecto',
                'notas' => 'Prospecto comercial para mantenimiento mayor.',
            ],
        ];

        foreach ($clientes as $payload) {
            Cliente::updateOrCreate(
                ['nombre_comercial' => $payload['nombre_comercial']],
                $payload,
            );
        }
    }
}
