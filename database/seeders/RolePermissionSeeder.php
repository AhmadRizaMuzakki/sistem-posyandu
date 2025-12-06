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
        // 1. Reset Cache (Wajib)
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Buat Permission (Menggunakan firstOrCreate agar aman di-run berkali-kali)
        Permission::firstOrCreate(['name' => 'view dashboard']);
        Permission::firstOrCreate(['name' => 'manage users']);
        Permission::firstOrCreate(['name' => 'manage anak']);
        Permission::firstOrCreate(['name' => 'manage imunisasi']);
        Permission::firstOrCreate(['name' => 'view imunisasi']);
        Permission::firstOrCreate(['name' => 'manage Posyandu']);

        // 3. Buat Role
        Role::firstOrCreate(['name' => 'superadmin']);
        Role::firstOrCreate(['name' => 'adminPosyandu']);
        Role::firstOrCreate(['name' => 'orangtua']);

        // --- A. Admin Posyandu ---
        $roleAdmin = Role::findByName('adminPosyandu');
        $roleAdmin->givePermissionTo('view dashboard');
        $roleAdmin->givePermissionTo('manage imunisasi');
        $roleAdmin->givePermissionTo('manage anak');

        // --- B. Orang Tua ---
        $roleOrangtua = Role::findByName('orangtua');
        $roleOrangtua->givePermissionTo('view dashboard');
        $roleOrangtua->givePermissionTo('view imunisasi');

        // --- C. Superadmin (Beri Semua) ---
        $roleSuperadmin = Role::findByName('superadmin');
        $roleSuperadmin->givePermissionTo(Permission::all());


        echo "✅ Role & Permission berhasil di-setup sesuai screenshot.\n";
    }
}
