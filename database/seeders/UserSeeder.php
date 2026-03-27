<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $defaultPassword = '123456';

        $users = [
            [
                'name' => 'Administrador General',
                'email' => 'administradoror@redaviation.com',
                'password' => $defaultPassword,
                'area_codigo' => 0,
                'rol' => 'admin',
            ],
            [
                'name' => 'Supervisor General',
                'email' => 'supervisor@redaviation.com',
                'password' => $defaultPassword,
                'area_codigo' => 0,
                'rol' => 'supervisor',
            ],
            [
                'name' => 'Administracion Inventario',
                'email' => 'administracion@redaviation.com',
                'password' => $defaultPassword,
                'area_codigo' => 0,
                'rol' => 'administracion',
            ],
            [
                'name' => 'Ing',
                'email' => 'ing@redaviation.com',
                'password' => $defaultPassword,
                'area_codigo' => 'AVCS',
                'rol' => 'admin',
            ],
            [
                'name' => 'Usuario AVCS',
                'email' => 'avcs@redaviation.com',
                'password' => $defaultPassword,
                'area_codigo' => 'AVCS',
                'rol' => 'tecnico',
            ],
            [
                'name' => 'Usuario HANG',
                'email' => 'hang@redaviation.com',
                'password' => $defaultPassword,
                'area_codigo' => 'HANG',
                'rol' => 'tecnico',
            ],
            [
                'name' => 'Usuario BATT',
                'email' => 'batt@redaviation.com',
                'password' => $defaultPassword,
                'area_codigo' => 'BATT',
                'rol' => 'tecnico',
            ],
            [
                'name' => 'Usuario FREN',
                'email' => 'fren@redaviation.com',
                'password' => $defaultPassword,
                'area_codigo' => 'FREN',
                'rol' => 'tecnico',
            ],
            [
                'name' => 'Usuario TREN',
                'email' => 'tren@redaviation.com',
                'password' => $defaultPassword,
                'area_codigo' => 'TREN',
                'rol' => 'tecnico',
            ],
            [
                'name' => 'Usuario HELI',
                'email' => 'heli@redaviation.com',
                'password' => $defaultPassword,
                'area_codigo' => 'HELI',
                'rol' => 'tecnico',
            ],
            [
                'name' => 'Usuario PROP',
                'email' => 'prop@redaviation.com',
                'password' => $defaultPassword,
                'area_codigo' => 'PROP',
                'rol' => 'tecnico',
            ],
            [
                'name' => 'Usuario PIST',
                'email' => 'pist@redaviation.com',
                'password' => $defaultPassword,
                'area_codigo' => 'PIST',
                'rol' => 'tecnico',
            ],
            [
                'name' => 'Usuario VEST',
                'email' => 'vest@redaviation.com',
                'password' => $defaultPassword,
                'area_codigo' => 'VEST',
                'rol' => 'tecnico',
            ],
            [
                'name' => 'Usuario ESTR',
                'email' => 'estr@redaviation.com',
                'password' => $defaultPassword,
                'area_codigo' => 'ESTR',
                'rol' => 'tecnico',
            ],
            [
                'name' => 'Usuario TORN',
                'email' => 'torn@redaviation.com',
                'password' => $defaultPassword,
                'area_codigo' => 'TORN',
                'rol' => 'tecnico',
            ],
            [
                'name' => 'Usuario SALV',
                'email' => 'salv@redaviation.com',
                'password' => $defaultPassword,
                'area_codigo' => 'SALV',
                'rol' => 'tecnico',
            ],
            [
                'name' => 'Usuario SOLD',
                'email' => 'sold@redaviation.com',
                'password' => $defaultPassword,
                'area_codigo' => 'SOLD',
                'rol' => 'tecnico',
            ],
            [
                'name' => 'Kevin',
                'email' => 'kevin@test.com',
                'password' => $defaultPassword,
                'area_codigo' => 'AVCS',
                'rol' => 'admin',
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make($userData['password']),
                    'area_id' => $userData['area_codigo']
                        ? Area::where('codigo', $userData['area_codigo'])->value('id')
                        : null,
                    'rol' => $userData['rol'],
                ]
            );
        }
    }
}
