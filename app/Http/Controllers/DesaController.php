<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class DesaController extends Controller
{
    public static function getAllDesa()
    {
        return [
            '01' => [
                'id' => '01',
                'nama' => 'Cikampek Kota',
                'kategori' => 'pusat',
                'kategoriLabel' => 'Pusat Niaga & Stasiun',
                'luas' => '0,50 km²',
                'persentaseLuas' => '1,39%',
                'kodePos' => '41373',
                'status' => 'Desa Mandiri',
                'penduduk' => '6.646 jiwa',
                'pendudukLaki' => '3.398',
                'pendudukPerempuan' => '3.248',
                'kades' => 'Ahmad Nurdin',
                'jabatan' => 'Kepala Desa',
                'kadesCatatan' => 'Kepala Desa definitif Cikampek Kota',
                'ringkasan' => 'Jantung perniagaan timur Karawang dengan simpul utama Stasiun Besar Cikampek, pasar induk regional, dan pusat perbankan.',
                'profil' => 'Desa Cikampek Kota merupakan episentrum pertumbuhan ekonomi kecamatan. Menjadi simpul pertemuan jalur rel kereta api utama pulau Jawa dan kawasan perdagangan grosir pasar tradisional modern.',
                'potensi' => 'Pusat Perdagangan & Jasa, Kawasan Transit Kereta Api Cepat/Antarkota, Perhotelan & Restoran, Sentra Bisnis Ekspedisi.',
                'fasilitas' => ['Stasiun Besar Cikampek', 'Pasar Induk Cikampek', 'Sentra Perbankan & ATM', 'Pusat Kuliner 24 Jam', 'Masjid Jami Cikampek Kota', 'Pangkalan Ojek & Angkutan']
            ],
            '02' => [
                'id' => '02',
                'nama' => 'Dawuan Tengah',
                'kategori' => 'pusat',
                'kategoriLabel' => 'Pusat Pemerintahan',
                'luas' => '3,47 km²',
                'persentaseLuas' => '9,54%',
                'kodePos' => '41373',
                'status' => 'Desa Mandiri',
                'penduduk' => '20.645 jiwa',
                'pendudukLaki' => '10.430',
                'pendudukPerempuan' => '10.215',
                'kades' => 'Jejen Jaenal Arifin, S.T.',
                'jabatan' => 'Kepala Desa',
                'kadesCatatan' => 'Kepala Desa Dawuan Tengah',
                'ringkasan' => 'Pusat pelayanan birokrasi & administrasi terpadu tempat berdirinya Kantor Kecamatan Cikampek, Polsek, dan Puskesmas DTP.',
                'profil' => 'Dawuan Tengah merupakan ibukota administratif Kecamatan Cikampek. Seluruh kantor koordinasi pelayanan umum, keamanan, dan kesehatan primer berada di poros wilayah ini.',
                'potensi' => 'Pusat Layanan Administrasi Publik, Koridor Bisnis Ahmad Yani, Sarana Kesehatan Rujukan, Kompleks Institusi Pemerintahan.',
                'fasilitas' => ['Kantor Camat Cikampek', 'Puskesmas DTP Cikampek', 'Polsek Cikampek', 'Koramil Cikampek', 'Gedung Serbaguna Kecamatan', 'Sentra Layanan Kependudukan']
            ],
            '03' => [
                'id' => '03',
                'nama' => 'Cikampek Barat',
                'kategori' => 'pusat',
                'kategoriLabel' => 'Niaga & Kuliner Pantura',
                'luas' => '2,11 km²',
                'persentaseLuas' => '5,80%',
                'kodePos' => '41373',
                'status' => 'Desa Mandiri',
                'penduduk' => '26.887 jiwa',
                'pendudukLaki' => '13.559',
                'pendudukPerempuan' => '13.328',
                'kades' => 'Hj. Yuyun Yunengsih, A.Md.',
                'jabatan' => 'Kepala Desa',
                'kadesCatatan' => 'Kepala Desa Cikampek Barat',
                'ringkasan' => 'Persimpangan vital jalur utama Pantura dan akses Purwakarta/Bandung, sentra niaga ritel, dan kuliner khas Cikampek.',
                'profil' => 'Wilayah dengan aksesibilitas tinggi yang menghubungkan Karawang dengan jalur arteri antar-provinsi. Menjadi pusat UMKM makanan khas, bengkel otomotif, dan logistik jalur darat.',
                'potensi' => 'Sentra Kuliner Khas Cikampek, Industri Jasa Otomotif, Pergudangan Skala Menengah, Ritel Komersial.',
                'fasilitas' => ['Simpang Jomin Arteri', 'Sentra Kuliner Sate Maranggi & Soto', 'Klinik Pratama', 'Sekolah Dasar & Madrasah', 'Sentra UMKM Lokal']
            ],
            '04' => [
                'id' => '04',
                'nama' => 'Cikampek Timur',
                'kategori' => 'pusat',
                'kategoriLabel' => 'Permukiman & UMKM',
                'luas' => '0,59 km²',
                'persentaseLuas' => '1,61%',
                'kodePos' => '41373',
                'status' => 'Desa Maju',
                'penduduk' => '10.766 jiwa',
                'pendudukLaki' => '5.496',
                'pendudukPerempuan' => '5.270',
                'kades' => 'H. Samsul Bakhri',
                'jabatan' => 'Kepala Desa (Periode 2026–2034)',
                'kadesCatatan' => 'Terpilih dalam Pilkades Digital periode 2026–2034 menggantikan petahana Kriswanto, A.Md.',
                'ringkasan' => 'Kawasan permukiman padat produktif dengan komunitas masyarakat aktif, sentra wirausaha rumahan, dan kerajinan.',
                'profil' => 'Desa Cikampek Timur memiliki densitas penduduk yang harmonis dengan berbagai unit usaha mikro masyarakat dan pusat pengrajin industri kreatif rumahan.',
                'potensi' => 'Industri Kreatif Rumahan, Budidaya Ikan Tawar, Jasa Boga & Katering, Koperasi Warga Produktif.',
                'fasilitas' => ['Kantor Desa Cikampek Timur', 'Poskesdes', 'Balai Warga', 'Sarana Olahraga Masyarakat', 'Masjid Jamie & Majelis']
            ],
            '05' => [
                'id' => '05',
                'nama' => 'Cikampek Selatan',
                'kategori' => 'pariwisata',
                'kategoriLabel' => 'Agrikultur & Komunitas',
                'luas' => '1,00 km²',
                'persentaseLuas' => '2,76%',
                'kodePos' => '41373',
                'status' => 'Desa Maju',
                'penduduk' => '10.968 jiwa',
                'pendudukLaki' => '5.556',
                'pendudukPerempuan' => '5.412',
                'kades' => 'Edi Suparjono',
                'jabatan' => 'Kepala Desa (Masa Bakti 2026–2034)',
                'kadesCatatan' => 'Dilantik pada 16 Maret 2026 untuk masa bakti 2026–2034.',
                'ringkasan' => 'Wilayah perpaduan permukiman hijau asri dan kawasan agrikultur modern sebagai lumbung ketahanan pangan lokal.',
                'profil' => 'Cikampek Selatan memiliki hamparan lahan pertanian subur yang didukung irigasi teknis serta pengembangan perumahan modern berkonsep ramah lingkungan.',
                'potensi' => 'Pertanian Padi Unggul, Agrowisata Edukasi Tani, Pengembangan Perumahan Asri, Peternakan Terpadu.',
                'fasilitas' => ['Kelompok Tani Berdaya', 'Saluran Irigasi Teknis', 'Balai Pertemuan Tani', 'Pustu Cikampek Selatan', 'SDN Cikampek Selatan']
            ],
            '06' => [
                'id' => '06',
                'nama' => 'Cikampek Pusaka',
                'kategori' => 'pariwisata',
                'kategoriLabel' => 'Budaya & Kearifan Lokal',
                'luas' => '2,36 km²',
                'persentaseLuas' => '6,48%',
                'kodePos' => '41373',
                'status' => 'Desa Berkembang',
                'penduduk' => '5.798 jiwa',
                'pendudukLaki' => '2.971',
                'pendudukPerempuan' => '2.827',
                'kades' => 'Pendi Supendi',
                'jabatan' => 'Kepala Desa',
                'kadesCatatan' => 'Kepala Desa Cikampek Pusaka tercantum pada data profil desa.',
                'ringkasan' => 'Kawasan pemekaran sarat sejarah lokal dengan tradisi gotong royong kental, sanggar seni budaya, dan agrowisata.',
                'profil' => 'Desa Cikampek Pusaka memegang teguh warisan tradisi leluhur Sunda Karawang dengan lanskap perbukitan landai yang asri dan udara sejuk.',
                'potensi' => 'Seni Pertunjukan Tradisional, Wisata Budaya Lokal, Kerajinan Tangan Bambu, Ketahanan Pangan Nabati.',
                'fasilitas' => ['Sanggar Seni Budaya', 'Kantor Desa Cikampek Pusaka', 'Area Hijau Terbuka', 'Pusat Kegiatan Belajar Warga', 'Posyandu Mandiri']
            ],
            '07' => [
                'id' => '07',
                'nama' => 'Dawuan Barat',
                'kategori' => 'industri',
                'kategoriLabel' => 'Niaga & Pergudangan Tol',
                'luas' => '4,11 km²',
                'persentaseLuas' => '11,30%',
                'kodePos' => '41373',
                'status' => 'Desa Mandiri',
                'penduduk' => '17.709 jiwa',
                'pendudukLaki' => '8.948',
                'pendudukPerempuan' => '8.761',
                'kades' => 'Aang Khoerul Hak, S.Kep.',
                'jabatan' => 'Pj. Kepala Desa',
                'kadesCatatan' => 'Menjabat sebagai Penjabat (Pj.) Kepala Desa setelah serah terima jabatan pada Agustus 2026.',
                'ringkasan' => 'Koridor strategis penghubung akses jalan tol dengan fasilitas pergudangan logistik modern dan sentra industri penunjang.',
                'profil' => 'Dawuan Barat merupakan pilar pendukung arus pergerakan logistik barang industri berkat kedekatannya dengan akses gerbang jalan tol Jakarta-Cikampek.',
                'potensi' => 'Pergudangan & Depo Kontainer, Sentra Logistik Nasional, Industri Pengemasan, Workshop Otomotif Berat.',
                'fasilitas' => ['Akses Dekat Gerbang Tol', 'Kawasan Pergudangan Terpadu', 'SPBU & Rest Area Arteri', 'Pusat Pelatihan Kerja', 'Klinik 24 Jam']
            ],
            '08' => [
                'id' => '08',
                'nama' => 'Dawuan Timur',
                'kategori' => 'industri',
                'kategoriLabel' => 'Sentra Pendidikan',
                'luas' => '3,00 km²',
                'persentaseLuas' => '8,26%',
                'kodePos' => '41373',
                'status' => 'Desa Maju',
                'penduduk' => '18.201 jiwa',
                'pendudukLaki' => '9.250',
                'pendudukPerempuan' => '8.951',
                'kades' => 'Tatang Somantri',
                'jabatan' => 'Kepala Desa',
                'kadesCatatan' => 'Kepala Desa Dawuan Timur',
                'ringkasan' => 'Pusat institusi edukasi sekolah menengah & kejuruan terkemuka serta pemukiman dinamis ramah keluarga.',
                'profil' => 'Dawuan Timur dikenal sebagai pusat pembinaan generasi muda melalui konsentrasi lembaga pendidikan kejuruan yang menyuplai tenaga kerja siap pakai untuk kawasan industri.',
                'potensi' => 'Pusat Lembaga Pendidikan SMK/SMA, Hunian Kos & Asrama Siswa, Penyediaan Tenaga Kerja Terampil, Wirausaha Digital.',
                'fasilitas' => ['Kompleks SMK Negeri & Swasta', 'Pondok Pesantren Modern', 'Lapangan Sepak Bola Dawuan', 'Perpustakaan Desa', 'Pusat Kursus Komputer']
            ],
            '09' => [
                'id' => '09',
                'nama' => 'Kalihurip',
                'kategori' => 'industri',
                'kategoriLabel' => 'Kawasan Industri Strategis',
                'luas' => '13,66 km²',
                'persentaseLuas' => '37,59%',
                'kodePos' => '41373',
                'status' => 'Desa Mandiri',
                'penduduk' => '5.622 jiwa',
                'pendudukLaki' => '2.868',
                'pendudukPerempuan' => '2.754',
                'kades' => 'Herman',
                'jabatan' => 'Kepala Desa',
                'kadesCatatan' => 'Terpilih dalam Pilkades Digital pada 22 November 2026.',
                'ringkasan' => 'Kawasan industri bertaraf internasional penggerak manufaktur nasional (Kawasan Industri Pupuk Kujang & BIC).',
                'profil' => 'Desa Kalihurip merupakan kawasan industri terpadu paling bergengsi di Cikampek dengan keberadaan pabrik pupuk nasional PT Pupuk Kujang dan berbagai industri multinasional.',
                'potensi' => 'Industri Kimia & Pupuk Nasional, Manufaktur Otomotif & Mesin, Lapangan Kerja Ribuan Karyawan, Perhotelan Bisnis.',
                'fasilitas' => ['Kawasan Industri Pupuk Kujang', 'Akses Tol Kalihurip Utama', 'Rumah Sakit Pupuk Kujang', 'Helipad & Fasilitas VIP', 'Pusat Keamanan Obvitnas']
            ],
            '10' => [
                'id' => '10',
                'nama' => 'Kamojing',
                'kategori' => 'pariwisata',
                'kategoriLabel' => 'Wisata Alam Situ Kamojing',
                'luas' => '5,55 km²',
                'persentaseLuas' => '15,27%',
                'kodePos' => '41373',
                'status' => 'Desa Maju',
                'penduduk' => '5.342 jiwa',
                'pendudukLaki' => '2.718',
                'pendudukPerempuan' => '2.624',
                'kades' => 'Cahyadi / Chahyadi',
                'jabatan' => 'Kepala Desa',
                'kadesCatatan' => 'Kepala Desa Kamojing',
                'ringkasan' => 'Destinasi wisata danau buatan bersejarah Situ Kamojing (sejak 1912), konservasi resapan air, dan kuliner apung.',
                'profil' => 'Desa Kamojing memiliki keindahan eksotis danau Situ Kamojing yang dibangun pada masa Hindia Belanda. Merupakan paru-paru hijau Cikampek sekaligus destinasi wisata keluarga favorit.',
                'potensi' => 'Pariwisata Danau & Sunset Spot, Wisata Kuliner Apung Sunda, Konservasi Air Irigasi, Olahraga Dayung & Memancing.',
                'fasilitas' => ['Danau Wisata Situ Kamojing', 'Pusat Wisata Kuliner Ikan Bakar', 'Dermaga Perahu Wisata', 'Jalur Lari/Jogging Pinggir Danau', 'Taman Edukasi Air']
            ]
        ];
    }

    public function downloadPdf($id)
    {
        $id = str_pad($id, 2, '0', STR_PAD_LEFT);
        $allDesa = self::getAllDesa();

        if (!isset($allDesa[$id])) {
            abort(404, 'Data Desa tidak ditemukan');
        }

        $desa = $allDesa[$id];

        $pdf = Pdf::loadView('desa.pdf', compact('desa'))
            ->setPaper('a4', 'portrait');

        $fileName = 'Profil_Desa_' . str_replace(' ', '_', $desa['nama']) . '_Cikampek.pdf';
        return $pdf->stream($fileName);
    }
}
