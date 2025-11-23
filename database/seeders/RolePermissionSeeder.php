<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cache permission Spatie
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        /*
        |--------------------------------------------------------------------------
        | List Permission
        |--------------------------------------------------------------------------
        */

        $permissions = [
            'view dashboard',
            'manage users',
            'manage anak',
            'manage imunisasi',
            'manage puskesmas',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        /*
        |--------------------------------------------------------------------------
        | Roles
        |--------------------------------------------------------------------------
        */
        $superadmin = Role::firstOrCreate(['name' => 'superadmin']);
        $adminPuskesmas = Role::firstOrCreate(['name' => 'adminPuskesmas']);
        $orangtua = Role::firstOrCreate(['name' => 'orangtua']);

        /*
        |--------------------------------------------------------------------------
        | Give Permission to Roles
        |--------------------------------------------------------------------------
        */

        // Superadmin punya semua permission
        $superadmin->syncPermissions(Permission::all());

        // Admin puskesmas
        $adminPuskesmas->syncPermissions([
            'view dashboard',
            'manage imunisasi',
            'manage anak',
        ]);

        // Orangtua (hanya akses dasar)
        $orangtua->syncPermissions([
            'view dashboard',
        ]);
        
        echo "Roles & Permissions berhasil dibuat.\n";
    }
}
