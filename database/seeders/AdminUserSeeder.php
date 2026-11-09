<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Aula;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds for Admin & Petugas Kecamatan.
     */
    public function run(): void
    {
        // 1. Akun Admin Utama Kecamatan
        User::updateOrCreate(
            ['email' => 'admin@cikampek.go.id'],
            [
                'name' => 'Admin Kecamatan Cikampek',
                'password' => Hash::make('password'),
                'no_hp' => '081234567890',
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        // 2. Akun Petugas Pelayanan Kecamatan
        User::updateOrCreate(
            ['email' => 'petugas@cikampek.go.id'],
            [
                'name' => 'Petugas Pelayanan Kecamatan',
                'password' => Hash::make('password'),
                'no_hp' => '081234567891',
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        // 3. Pastikan data Aula default tersedia jika belum ada
        if (Aula::count() === 0) {
            Aula::create([
                'nama' => 'Aula Kecamatan Cikampek',
                'lokasi' => 'Kecamatan Cikampek',
                'kapasitas' => 100,
                'jam_buka' => '08:00:00',
                'jam_tutup' => '22:00:00',
                'fasilitas' => ['Meja', 'Kursi', 'Sound System', 'Toilet', 'Mic', 'Kipas'],
                'ketentuan' => "1. Menjaga kebersihan aula.\n2. Tidak merusak fasilitas.\n3. Mematuhi jam operasional."
            ]);
        }
    }
}

