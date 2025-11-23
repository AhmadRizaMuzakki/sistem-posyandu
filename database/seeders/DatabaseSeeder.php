<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        

        $this->call(RolePermissionSeeder::class);
        /*
        |--------------------------------------------------------------------------
        | Superadmin
        |--------------------------------------------------------------------------
        */
        $superadmin = User::create([
            'name' => 'riza',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('root'),
        ]);
        $superadmin->assignRole('superadmin');


        /*
        |--------------------------------------------------------------------------
        | Admin Puskesmas
        |--------------------------------------------------------------------------
        */
        $adminPuskesmas = User::create([
            'name' => 'Admin Puskesmas',
            'email' => 'puskesmas@gmail.com',
            'password' => bcrypt('root'),
        ]);
        $adminPuskesmas->assignRole('adminPuskesmas');


        /*
        |--------------------------------------------------------------------------
        | Orangtua
        |--------------------------------------------------------------------------
        */
        $orangtua = User::create([
            'name' => 'Orang Tua',
            'email' => 'orangtua@gmail.com',
            'password' => bcrypt('root'),
        ]);
        $orangtua->assignRole('orangtua');
    
    }
    
}
