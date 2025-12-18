<?php

namespace Database\Seeders;

use App\Models\Kader;
use App\Models\User;
use Illuminate\Database\Seeder;
use Nette\Utils\Random;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Panggil RolePermissionSeeder dulu (Wajib)
        $this->call([RolePermissionSeeder::class, PosyanduSeeder::class]);

        /*
        |--------------------------------------------------------------------------
        | Superadmin
        |--------------------------------------------------------------------------
        */
        // GUNAKAN firstOrCreate JANGAN create
        $superadmin = User::firstOrCreate(
            ['email' => 'admin@gmail.com'], // Cek email
            [
                'name' => 'riza',
                'password' => bcrypt('root'),
                'email_verified_at' => now(),
            ]
        );

        // Paksa pasang role (ini intinya)
        $superadmin->assignRole('superadmin');


        /*
        |--------------------------------------------------------------------------
        | Admin Puskesmas
        |--------------------------------------------------------------------------
        */
        $adminPuskesmas = User::firstOrCreate(
            ['email' => 'puskesmas@gmail.com'],
            [
                'name' => 'Admin Puskesmas',
                'password' => bcrypt('root'),
                'email_verified_at' => now(),
            ]
        );
        $adminPuskesmas->assignRole('adminPosyandu');


        /*
        |--------------------------------------------------------------------------
        | Orangtua
        |--------------------------------------------------------------------------
        */
        $orangtua = User::firstOrCreate(
            ['email' => 'orangtua@gmail.com'],
            [
                'name' => 'Orang Tua',
                'password' => bcrypt('root'),
                'email_verified_at' => now(),
            ]
        );
        $orangtua->assignRole('orangtua');


    }
}
