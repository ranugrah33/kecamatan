<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AiKnowledge;

class AdditionalKnowledgeSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'category' => 'Pembuatan Keterangan Ahli Waris',
                'title' => 'Informasi Pengurusan Surat Ahli Waris',
                'question' => 'Bagaimana cara membuat surat keterangan ahli waris? Apa saja persyaratannya?',
                'answer' => 'Untuk pengurusan Surat Keterangan Ahli Waris, pelayanan ini saat ini hanya dilayani secara tatap muka (offline). Anda diharuskan datang langsung ke kantor Kecamatan Cikampek. Persyaratan umum yang perlu disiapkan antara lain: Surat Pengantar RT/RW dan Desa/Kelurahan, fotokopi KK dan KTP pewaris serta seluruh ahli waris, Surat Keterangan Kematian, serta dokumen pendukung lainnya. Silakan temui petugas loket pelayanan kecamatan pada jam kerja untuk informasi lebih lanjut.',
                'keywords' => 'ahli waris, surat ahli waris, warisan, keterangan waris, mengurus warisan, waris',
                'priority' => 10,
                'is_active' => true,
            ],
            [
                'category' => 'Perizinan UMKM',
                'title' => 'Informasi Pengurusan Izin Usaha / UMKM',
                'question' => 'Bagaimana cara mendaftar UMKM atau membuat izin usaha?',
                'answer' => 'Pelayanan pengurusan perizinan UMKM atau pembuatan Surat Keterangan Usaha (SKU) dilayani secara tatap muka di kantor kecamatan. Silakan datang langsung ke kantor Kecamatan Cikampek dengan membawa Surat Pengantar dari Desa/Kelurahan, fotokopi KTP, fotokopi KK, serta foto tempat usaha (jika diperlukan). Petugas kami akan memandu Anda untuk proses selanjutnya.',
                'keywords' => 'umkm, izin usaha, sku, surat keterangan usaha, daftar umkm, usaha mikro, dagang',
                'priority' => 10,
                'is_active' => true,
            ],
            [
                'category' => 'Bantuan Sosial (Bansos)',
                'title' => 'Informasi Program Bantuan Sosial',
                'question' => 'Bagaimana cara mendapatkan bantuan sosial atau mengecek data bansos?',
                'answer' => 'Terkait informasi, pendaftaran, maupun pengecekan program Bantuan Sosial (Bansos), prosesnya memerlukan verifikasi data kependudukan secara langsung. Silakan berkoordinasi dengan aparatur Desa/Kelurahan setempat terlebih dahulu untuk pendataan, atau Anda dapat mengunjungi kantor Kecamatan Cikampek pada jam kerja untuk berkonsultasi dengan petugas bagian Kesejahteraan Sosial (Kesos).',
                'keywords' => 'bansos, bantuan sosial, pkh, blt, bantuan pemerintah, cek bansos, daftar bansos, sembako',
                'priority' => 10,
                'is_active' => true,
            ],
            [
                'category' => 'Pertanahan / Jual Beli Tanah',
                'title' => 'Informasi Pengurusan Akta Jual Beli (AJB)',
                'question' => 'Bagaimana prosedur mengurus surat jual beli tanah atau AJB di kecamatan?',
                'answer' => 'Pengurusan Akta Jual Beli (AJB) tanah atau dokumen pertanahan lainnya ditangani langsung oleh Pejabat Pembuat Akta Tanah Sementara (PPATS) di kecamatan. Anda diwajibkan datang langsung ke kantor Kecamatan Cikampek. Siapkan dokumen asli seperti Sertifikat/Girik, fotokopi KTP dan KK pihak penjual maupun pembeli, SPPT PBB tahun berjalan yang sudah lunas, serta didampingi saksi-saksi. Temui petugas pelayanan pertanahan untuk verifikasi kelengkapan berkas Anda.',
                'keywords' => 'jual beli tanah, ajb, akta jual beli, tanah, sertifikat tanah, urus tanah, pertanahan',
                'priority' => 10,
                'is_active' => true,
            ]
        ];

        foreach ($data as $item) {
            AiKnowledge::create($item);
        }
    }
}
