<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AiKnowledge;

class AiKnowledgeSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'category' => 'Peminjaman Aula',
                'title' => 'Informasi Umum Peminjaman Aula',
                'question' => 'Bagaimana cara meminjam aula kecamatan?',
                'answer' => 'Untuk meminjam aula Kecamatan Cikampek, Anda perlu mengajukan permohonan melalui sistem pelayanan ini atau datang langsung ke kantor kecamatan. Persyaratan umum meliputi surat permohonan, identitas pemohon, dan persetujuan jadwal. Silakan cek menu "Layanan Publik" > "Peminjaman Aula" untuk melihat jadwal yang tersedia dan mengisi form pengajuan.',
                'keywords' => 'pinjam aula, gedung, sewa aula, prosedur aula, syarat aula, jadwal aula',
                'priority' => 10,
                'is_active' => true,
            ],
            [
                'category' => 'Peminjaman Barang Inventaris',
                'title' => 'Peminjaman Kursi, Sound System, dan Barang Lainnya',
                'question' => 'Apakah bisa meminjam kursi, sound system, atau PC? Bagaimana caranya?',
                'answer' => 'Kecamatan Cikampek menyediakan fasilitas peminjaman barang inventaris seperti kursi, sound system, PC, dll (tergantung ketersediaan dan kebijakan). Anda dapat mengajukan permohonan melalui menu "Layanan Publik" > "Peminjaman Inventaris". Harap cantumkan jumlah barang dan tujuan penggunaan.',
                'keywords' => 'pinjam kursi, pinjam sound system, pinjam pc, sewa kursi, inventaris, barang, proyektor',
                'priority' => 10,
                'is_active' => true,
            ],
            [
                'category' => 'Pengajuan Sertifikat/Piagam',
                'title' => 'Prosedur Pengajuan Sertifikat',
                'question' => 'Bagaimana cara mengajukan sertifikat atau piagam?',
                'answer' => 'Masyarakat dapat mengajukan permohonan pembuatan sertifikat atau piagam dengan mengirimkan surat permohonan melalui menu "Layanan Publik" > "Pengajuan Sertifikat". Setelah permohonan diproses dan disetujui, admin Kecamatan Cikampek akan menerbitkan dan mengunggah sertifikat/piagam tersebut ke sistem, dan Anda dapat mengunduhnya secara langsung dari riwayat pengajuan Anda.',
                'keywords' => 'sertifikat, piagam, pengajuan sertifikat, buat sertifikat, cetak piagam',
                'priority' => 10,
                'is_active' => true,
            ],
            [
                'category' => 'Informasi Umum',
                'title' => 'Pelayanan yang Tersedia',
                'question' => 'Pelayanan apa saja yang tersedia di Kecamatan Cikampek?',
                'answer' => "Saat ini, pelayanan digital yang tersedia di Kecamatan Cikampek meliputi:\n1. Peminjaman Aula\n2. Peminjaman Barang Inventaris (Kursi, Sound System, dll)\n3. Pengajuan Pembuatan Sertifikat/Piagam\nInformasi lebih lanjut dapat Anda temukan pada menu Layanan Publik.",
                'keywords' => 'layanan, pelayanan, daftar layanan, ada apa saja, fasilitas',
                'priority' => 10,
                'is_active' => true,
            ],
            [
                'category' => 'Wilayah & 10 Desa Cikampek',
                'title' => 'Data Luas Wilayah 10 Desa di Kecamatan Cikampek',
                'question' => 'Berapa luas wilayah Kecamatan Cikampek dan masing-masing desanya?',
                'answer' => "Total luas wilayah Kecamatan Cikampek adalah 47,60 km² yang terdiri dari 10 desa/kelurahan berdasarkan data BPS:\n1. Desa Kalihurip: 19,91 km²\n2. Desa Kamojing: 12,31 km²\n3. Desa Dawuan Barat: 3,19 km²\n4. Desa Dawuan Timur: 2,80 km²\n5. Desa Dawuan Tengah: 2,27 km²\n6. Desa Cikampek Barat: 1,94 km²\n7. Desa Cikampek Pusaka: 1,87 km²\n8. Desa Cikampek Selatan: 1,22 km²\n9. Desa Cikampek Timur: 1,12 km²\n10. Desa Cikampek Kota: 0,97 km²",
                'keywords' => 'luas desa, luas wilayah, data desa, desa cikampek, kalihurip, kamojing, dawuan, luas kecamatan',
                'priority' => 10,
                'is_active' => true,
            ]
        ];

        foreach ($data as $item) {
            AiKnowledge::create($item);
        }
    }
}
