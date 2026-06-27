<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\Province;
use App\Models\District;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Insertar o Buscar Departamento (Lambayeque: 14)
        $department = Department::firstOrCreate(
            ['name' => 'Lambayeque'],
            ['code' => '14']
        );

        // 2. Insertar o Buscar Provincias
        $chiclayo = Province::firstOrCreate(
            [
                'department_id' => $department->id,
                'name' => 'Chiclayo'
            ],
            [
                'code' => '1401'
            ]
        );

        Province::firstOrCreate(
            [
                'department_id' => $department->id,
                'name' => 'Lambayeque'
            ],
            [
                'code' => '1403'
            ]
        );

        Province::firstOrCreate(
            [
                'department_id' => $department->id,
                'name' => 'Ferreñafe'
            ],
            [
                'code' => '1402'
            ]
        );

        // 3. Distritos para la provincia de Chiclayo (Pasando de forma explícita province_id y department_id)
        $districtsChiclayo = [
            'Chiclayo'            => '140101',
            'Jose Leonardo Ortiz' => '140105',
            'La Victoria'         => '140106',
            'Pimentel'            => '140112',
            'Reque'               => '140113',
            'Monsefú'             => '140109'
        ];

        foreach ($districtsChiclayo as $distName => $distCode) {
            District::firstOrCreate(
                [
                    'province_id'   => $chiclayo->id,
                    'department_id' => $department->id, // <--- Aquí añadimos el campo que faltaba
                    'name'          => $distName
                ],
                [
                    'code'          => $distCode
                ]
            );
        }
    }
}