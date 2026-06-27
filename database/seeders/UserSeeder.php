<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name'               => 'Administrador del Sistema',
            'email'              => 'admin@rsu.com',
            'password'           => Hash::make('admin'), 
            'dni'                => '77777777',
            'birthdate'          => '1995-05-15',
            'address'            => 'Av. Principal 123',
            'license'            => null,
            'usertype_id'        => null, 
            'status'             => 1, 
            'profile_photo_path' => null,
        ]);
    }
}