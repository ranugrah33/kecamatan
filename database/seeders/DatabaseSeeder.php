<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        \App\Models\User::factory()->create([
            'name' => 'Admin Kecamatan',
            'email' => 'admin@cikampek.go.id',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $user = \App\Models\User::factory()->create([
            'name' => 'Rizky Anugrah',
            'email' => 'rizky@example.com',
            'password' => bcrypt('password'),
            'role' => 'masyarakat',
            'nik' => '3215000000000001',
            'no_hp' => '081234567890'
        ]);

        $user->masyarakat()->create([
            'alamat' => 'Jl. Cikampek No. 123',
            'desa' => 'Cikampek Kota'
        ]);

        \App\Models\Aula::create([
            'nama' => 'Aula Kecamatan Cikampek',
            'lokasi' => 'Kecamatan Cikampek',
            'kapasitas' => 100,
            'jam_buka' => '08:00:00',
            'jam_tutup' => '22:00:00',
            'fasilitas' => ['Meja', 'Kursi', 'Sound System', 'Toilet', 'Mic', 'Kipas'],
            'ketentuan' => '1. Menjaga kebersihan aula. 2. Tidak merusak fasilitas. 3. Mematuhi jam operasional.'
        ]);
    }
}
