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

        $this->call(AdminUserSeeder::class);

        $user = \App\Models\User::firstOrCreate(
            ['email' => 'rizky@example.com'],
            [
                'name' => 'Rizky Anugrah',
                'password' => bcrypt('password'),
                'role' => 'masyarakat',
                'no_hp' => '081234567890'
            ]
        );

        if (!$user->masyarakat) {
            $user->masyarakat()->create([
                'alamat' => 'Jl. Cikampek No. 123',
                'desa' => 'Cikampek Kota'
            ]);
        }

        if (\App\Models\Aula::count() === 0) {
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

        $this->call(AiKnowledgeSeeder::class);
    }
}
