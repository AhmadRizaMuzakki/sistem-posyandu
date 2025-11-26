<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OrangtuaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan role 'orangtua' sudah ada
        // Jika belum, seeder ini akan error, jadi pastikan RolePermissionSeeder sudah dijalankan
        
        // Jumlah user random yang ingin dibuat (bisa diubah sesuai kebutuhan)
        $jumlahUser = 20;
        
        // Nama-nama Indonesia untuk variasi
        $namaDepan = ['Ahmad', 'Budi', 'Siti', 'Rina', 'Dedi', 'Lina', 'Eko', 'Maya', 'Fajar', 'Nina', 
                      'Gunawan', 'Sari', 'Hadi', 'Dewi', 'Indra', 'Rita', 'Joko', 'Sinta', 'Kurnia', 'Yuni'];
        $namaBelakang = ['Santoso', 'Wijaya', 'Sari', 'Kurniawan', 'Pratiwi', 'Hidayat', 'Lestari', 
                         'Saputra', 'Rahayu', 'Setiawan', 'Dewi', 'Purnomo', 'Sari', 'Wibowo', 'Kusuma'];
        
        for ($i = 1; $i <= $jumlahUser; $i++) {
            // Generate random name
            $nama = fake()->randomElement($namaDepan) . ' ' . fake()->randomElement($namaBelakang);
            
            // Generate unique email
            $email = 'orangtua' . $i . '@example.com';
            
            // Buat user
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $nama,
                    'password' => Hash::make('password'), // Default password: password
                    'email_verified_at' => now(),
                ]
            );
            
            // Assign role orangtua
            if (!$user->hasRole('orangtua')) {
                $user->assignRole('orangtua');
            }
        }
        
        echo "✅ Berhasil membuat {$jumlahUser} user random dengan role orangtua.\n";
    }
}

