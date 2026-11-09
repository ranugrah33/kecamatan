<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AiKnowledge;

class AiKnowledgeSeeder extends Seeder
{
    /**
     * Run the database seeds for AI Knowledge Base SIPAF Kecamatan Cikampek.
     */
    public function run(): void
    {
        $data = [
            // =========================================================================
            // A. PROFIL KECAMATAN
            // =========================================================================
            [
                'category' => 'Profil Kecamatan',
                'title' => 'Profil & Letak Geografis Kecamatan Cikampek',
                'question' => 'Bagaimana profil dan letak wilayah Kecamatan Cikampek?',
                'answer' => "Kecamatan Cikampek adalah salah satu kecamatan strategis di Kabupaten Karawang, Jawa Barat, yang dikenal sebagai simpul transportasi dan pusat perekonomian. Kantor Pemerintah Kecamatan Cikampek beralamat di Jl. Ahmad Yani No. 1, Cikampek, Karawang 41373. Kecamatan Cikampek membawahi 10 desa dengan luas total 36,34 km².",
                'keywords' => 'profil kecamatan, profil cikampek, letak geografis, alamat kecamatan, wilayah cikampek',
                'priority' => 10,
                'is_active' => true,
            ],
            [
                'category' => 'Profil Kecamatan',
                'title' => 'Data Luas Wilayah 10 Desa di Kecamatan Cikampek',
                'question' => 'Berapa luas wilayah Kecamatan Cikampek dan masing-masing desanya?',
                'answer' => "Luas wilayah Kecamatan Cikampek adalah 36,34 km² terbagi ke dalam 10 desa (BPS Karawang 2025): Kalihurip 13,66 km² (37,59%), Kamojing 5,55 km² (15,27%), Dawuan Barat 4,11 km² (11,30%), Dawuan Tengah 3,47 km² (9,54%), Dawuan Timur 3,00 km² (8,26%), Cikampek Pusaka 2,36 km² (6,48%), Cikampek Barat 2,11 km² (5,80%), Cikampek Selatan 1,00 km² (2,76%), Cikampek Timur 0,59 km² (1,61%), dan Cikampek Kota 0,50 km² (1,39%).",
                'keywords' => 'luas desa, luas wilayah, desa cikampek, kalihurip, kamojing, dawuan barat, dawuan tengah, dawuan timur, cikampek pusaka, cikampek barat, cikampek selatan, cikampek timur, cikampek kota, bps karawang',
                'priority' => 10,
                'is_active' => true,
            ],
            [
                'category' => 'Profil Kecamatan',
                'title' => 'Data Demografi & Jumlah Penduduk Kecamatan Cikampek',
                'question' => 'Berapa jumlah penduduk di Kecamatan Cikampek dan masing-masing desanya?',
                'answer' => "Berdasarkan data BPS Karawang 2025 (Disdukcapil), jumlah penduduk Kecamatan Cikampek adalah 128.584 jiwa (Laki-laki: 65.194, Perempuan: 63.390). Rincian per desa: Cikampek Barat 26.887 jiwa, Dawuan Tengah 20.645 jiwa, Dawuan Timur 18.201 jiwa, Dawuan Barat 17.709 jiwa, Cikampek Selatan 10.968 jiwa, Cikampek Timur 10.766 jiwa, Cikampek Kota 6.646 jiwa, Cikampek Pusaka 5.798 jiwa, Kalihurip 5.622 jiwa, dan Kamojing 5.342 jiwa.",
                'keywords' => 'jumlah penduduk, jumlah jiwa, demografi, populasi, data bps karawang, warga cikampek',
                'priority' => 10,
                'is_active' => true,
            ],
            [
                'category' => 'Profil Kecamatan',
                'title' => 'Struktur Organisasi dan Seksi Pelayanan Kecamatan',
                'question' => 'Apa saja struktur organisasi dan seksi pelayanan di Kantor Kecamatan Cikampek?',
                'answer' => "Pemerintah Kecamatan Cikampek dipimpin oleh Camat dan Sekretaris Camat (Sekcam). Seksi pelayanan meliputi: 1. Seksi Pelayanan Publik (Paten) untuk administrasi umum dan surat rekomendasi warga; 2. Seksi Tata Pemerintahan untuk pembinaan desa dan administrasi; 3. Seksi Trantib/Satpol PP untuk ketertiban umum; 4. Seksi PMD untuk pemberdayaan desa; 5. Seksi Kesejahteraan Sosial (Kesos) untuk bantuan dan sosial masyarakat.",
                'keywords' => 'struktur organisasi, camat, sekcam, seksi pelayanan, paten, trantib, tata pemerintahan, kesos, pmd',
                'priority' => 8,
                'is_active' => true,
            ],

            // =========================================================================
            // B. PERSYARATAN UMUM
            // =========================================================================
            [
                'category' => 'Persyaratan Umum',
                'title' => 'Persyaratan Umum Pengurusan Surat Keterangan Ahli Waris',
                'question' => 'Apa saja persyaratan ahli waris dan bagaimana cara mengurusnya?',
                'answer' => "Persyaratan Umum Surat Keterangan Ahli Waris yang biasanya diperlukan meliputi:\n1. Akta Kematian pewaris yang diterbitkan oleh Dinas Kependudukan dan Pencatatan Sipil (Dukcapil).\n2. Kartu Keluarga (KK) dan KTP milik pewaris serta para ahli waris, sesuai dokumen yang tersedia dan ketentuan pelayanan.\n3. Buku Nikah atau Akta Perkawinan pewaris, jika relevan.\n4. Akta Kelahiran para ahli waris sebagai bukti hubungan kekeluargaan/nasab.\n5. Surat pengantar dari Ketua RT dan RW setempat yang diketahui Kepala Desa.\n6. Surat Pernyataan Ahli Waris yang ditandatangani di atas meterai oleh seluruh ahli waris dan disaksikan oleh para saksi.\n\nCatatan Penting:\n- Daftar di atas merupakan gambaran persyaratan umum, bukan daftar resmi atau persyaratan final Kecamatan Cikampek.\n- Persyaratan dapat berbeda tergantung kondisi keluarga, status pewaris, dan ketentuan yang berlaku.\n- Untuk memastikan kelengkapan dokumen resmi sesuai kondisi Anda, disarankan berkonsultasi langsung dengan petugas pelayanan Kantor Kecamatan Cikampek.\n- Jangan menyatakan seluruh dokumen tersebut pasti wajib tanpa verifikasi berkas oleh petugas.",
                'keywords' => 'persyaratan ahli waris, surat ahli waris, syarat ahli waris, berkas ahli waris, surat keterangan ahli waris, akta kematian, warisan, hak waris, cara mengurus surat ahli waris',
                'priority' => 20,
                'is_active' => true,
            ],
            [
                'category' => 'Persyaratan Umum',
                'title' => 'Persyaratan Umum Surat Keterangan Tidak Mampu (SKTM)',
                'question' => 'Apa saja persyaratan umum pengurusan SKTM di kecamatan?',
                'answer' => "Persyaratan umum SKTM (pendidikan/kesehatan): 1. Surat Pengantar RT/RW; 2. Surat Keterangan Tidak Mampu dari Kantor Desa bertanda tangan Kepala Desa; 3. Fotokopi KTP pemohon dan orang tua/wali; 4. Fotokopi Kartu Keluarga (KK); 5. Surat pernyataan tidak mampu bermeterai. Catatan: Ini adalah gambaran umum, konfirmasi berkas resmi ke loket Paten/Kesos Kantor Kecamatan Cikampek.",
                'keywords' => 'sktm, surat keterangan tidak mampu, beasiswa, bantuan sekolah, surat miskin, syarat sktm',
                'priority' => 10,
                'is_active' => true,
            ],
            [
                'category' => 'Persyaratan Umum',
                'title' => 'Persyaratan Umum Rekomendasi Surat Keterangan Usaha (SKU)',
                'question' => 'Apa saja persyaratan umum surat keterangan usaha atau izin usaha mikro?',
                'answer' => "Persyaratan umum rekomendasi surat keterangan usaha tingkat kecamatan: 1. Surat Pengantar RT/RW; 2. Surat Keterangan Usaha dari Desa; 3. Fotokopi KTP dan KK pemilik usaha; 4. Foto tempat/kegiatan usaha. Catatan: Untuk NIB resmi, daftar mandiri via OSS (Online Single Submission). Untuk rekomendasi kecamatan, konfirmasi ke petugas Paten Kecamatan Cikampek.",
                'keywords' => 'surat keterangan usaha, sku, iumk, surat izin usaha, domisili usaha, usaha mikro, umkm',
                'priority' => 10,
                'is_active' => true,
            ],
            [
                'category' => 'Persyaratan Umum',
                'title' => 'Persyaratan Umum Administrasi Pertanahan Tingkat Kecamatan',
                'question' => 'Apa persyaratan pengurusan surat keterangan riwayat tanah di kecamatan?',
                'answer' => "Persyaratan umum surat keterangan riwayat tanah/tidak sengketa: 1. Surat Pengantar RT/RW dan pengantar Desa; 2. Fotokopi KTP dan KK pemohon; 3. Bukti alas hak awal (Letter C, girik, akta jual beli); 4. Surat pernyataan penguasaan fisik bidang tanah tidak sengketa disaksikan tetangga batas dan bermeterai; 5. Fotokopi SPPT PBB tahun berjalan. Catatan: Kecamatan hanya melayani administrasi surat keterangan bila dipersyaratkan. Penerbitan Sertifikat Hak Milik (SHM) tanah adalah wewenang Kantor Pertanahan (ATR/BPN) Karawang, bukan SIPAF.",
                'keywords' => 'riwayat tanah, surat tanah, letter c, girik, tanah tidak sengketa, pbb, bukan sertifikat tanah',
                'priority' => 10,
                'is_active' => true,
            ],

            // =========================================================================
            // C. PELAYANAN SIPAF
            // =========================================================================
            [
                'category' => 'Pelayanan SIPAF',
                'title' => 'Layanan Peminjaman Aula Kecamatan Cikampek melalui SIPAF',
                'question' => 'Bagaimana cara meminjam aula dan apa persyaratannya di website SIPAF?',
                'answer' => "Masyarakat, komunitas, maupun instansi dapat mengajukan peminjaman Aula Kecamatan Cikampek secara online melalui website SIPAF.\n\nAlur Pengajuan:\n1. Buka website SIPAF dan login menggunakan akun masyarakat.\n2. Pilih menu Peminjaman Aula.\n3. Lengkapi formulir pengajuan (nama instansi, penanggung jawab, kontak aktif, keperluan acara, tanggal dan waktu peminjaman).\n4. Unggah file surat permohonan resmi dari instansi/pemohon sesuai ketentuan sistem.\n5. Kirim pengajuan dan tunggu pemeriksaan petugas Kecamatan.\n6. Pantau status pengajuan melalui menu Riwayat pada website SIPAF.\n\nKetentuan: Persetujuan dan ketersediaan jadwal aula ditentukan petugas Kecamatan. Chatbot tidak mengutip atau mengarang biaya sewa fiktif; mengenai ketentuan biaya atau retribusi resmi dapat dikonfirmasi langsung ke Kantor Kecamatan Cikampek.",
                'keywords' => 'peminjaman aula, pinjam aula, aula kecamatan, sewa aula, gedung kecamatan, aula online, syarat aula, sipaf aula, cara meminjam aula',
                'priority' => 25,
                'is_active' => true,
            ],
            [
                'category' => 'Pelayanan SIPAF',
                'title' => 'Layanan Peminjaman Barang Inventaris melalui SIPAF',
                'question' => 'Bagaimana cara meminjam barang inventaris seperti kursi, sound system, atau PC?',
                'answer' => "SIPAF melayani peminjaman barang inventaris milik Kecamatan Cikampek (seperti kursi, sound system, PC/komputer, dan perlengkapan lainnya) untuk mendukung kegiatan masyarakat dan instansi.\n\nAlur Layanan:\n1. Login ke website SIPAF menggunakan akun masyarakat.\n2. Pilih menu Peminjaman Inventaris.\n3. Pilih barang yang ingin dipinjam dan masukkan jumlah yang dibutuhkan.\n4. Isi perihal keperluan kegiatan serta tanggal dan jam peminjaman dan pengembalian.\n5. Kirim pengajuan dan tunggu pemeriksaan serta persetujuan petugas Kecamatan.\n6. Pantau status pengajuan melalui menu Riwayat.\n7. Setelah disetujui, ambil barang dan kembalikan tepat waktu dalam kondisi utuh dan bersih.\n\nKetentuan: Ketersediaan stok dan persetujuan barang ditentukan petugas Kecamatan. Chatbot tidak menampilkan angka stok fiktif.",
                'keywords' => 'pinjam kursi, pinjam sound system, pinjam pc, komputer, proyektor, inventaris kecamatan, barang inventaris, sipaf inventaris, cara meminjam kursi',
                'priority' => 25,
                'is_active' => true,
            ],
            [
                'category' => 'Pelayanan SIPAF',
                'title' => 'Layanan Pengajuan Sertifikat / Piagam melalui SIPAF',
                'question' => 'Bagaimana cara mengajukan sertifikat atau piagam penghargaan melalui SIPAF?',
                'answer' => "Masyarakat dapat mengajukan permohonan sertifikat atau piagam penghargaan kegiatan dari Kecamatan Cikampek secara online melalui website SIPAF.\n\nAlur Layanan:\n1. Login akun masyarakat di SIPAF.\n2. Pilih menu Sertifikat / Piagam.\n3. Isi data pengajuan (jenis kegiatan, deskripsi, tanggal kegiatan).\n4. Unggah surat permohonan dan berkas pendukung.\n5. Kirim pengajuan dan tunggu verifikasi petugas Kecamatan.\n6. Pantau status di menu Riwayat. Jika disetujui, soft-copy sertifikat/piagam resmi dapat diunduh langsung dari sistem SIPAF.\n\nPENTING: Layanan ini KHUSUS untuk sertifikat/piagam penghargaan kegiatan kecamatan, BUKAN untuk pengajuan sertifikat kepemilikan tanah. Pengurusan sertifikat tanah dilakukan di Kantor Pertanahan (ATR/BPN) Karawang.",
                'keywords' => 'sertifikat piagam, pengajuan piagam, piagam penghargaan, sertifikat kecamatan, cetak piagam, sipaf sertifikat, bukan sertifikat tanah, cara mengajukan sertifikat',
                'priority' => 25,
                'is_active' => true,
            ],
            [
                'category' => 'Pelayanan SIPAF',
                'title' => 'Akun Masyarakat dan Kelengkapan Profil Pengguna di SIPAF',
                'question' => 'Bagaimana cara mendaftar akun dan memperbarui data profil di SIPAF?',
                'answer' => "Masyarakat dapat mendaftar mandiri di website SIPAF dengan mengklik 'Daftar Akun Baru' dan mengisi nama, email, nomor HP/WhatsApp aktif, serta password. Pastikan nomor kontak dan alamat pada menu 'Profil' selalu diperbarui dan aktif agar petugas Kecamatan Cikampek dapat menghubungi Anda saat memverifikasi pengajuan.",
                'keywords' => 'daftar akun, buat akun, login sipaf, profil masyarakat, update no hp, nomor whatsapp, akun pengguna',
                'priority' => 12,
                'is_active' => true,
            ],
            [
                'category' => 'Pelayanan SIPAF',
                'title' => 'Pemantauan Status Pengajuan & Pengingat Pengembalian Inventaris',
                'question' => 'Bagaimana cara memantau status pengajuan dan aturan pengembalian barang?',
                'answer' => "Masyarakat dapat memantau status pengajuan secara real-time pada menu 'Riwayat' di SIPAF. Ada 4 status: 1. Menunggu Persetujuan (sedang diperiksa petugas); 2. Disetujui (diterima, siap ditindaklanjuti); 3. Ditolak (belum memenuhi syarat, klik detail untuk melihat catatan alasan penolakan); 4. Selesai (layanan tuntas). Untuk inventaris, barang wajib dikembalikan tepat waktu; sistem SIPAF mengirimkan email reminder pengingat otomatis pada jadwal pengembalian.",
                'keywords' => 'status pengajuan, cek status, riwayat pengajuan, menunggu persetujuan, disetujui, ditolak, selesai, reminder pengembalian',
                'priority' => 18,
                'is_active' => true,
            ],

            // =========================================================================
            // D. PROSEDUR PELAYANAN
            // =========================================================================
            [
                'category' => 'Prosedur Pelayanan',
                'title' => 'Alur Langkah Demi Langkah Peminjaman Aula Kecamatan',
                'question' => 'Bagaimana alur langkah demi langkah mengajukan peminjaman aula?',
                'answer' => "Tahapan pengajuan peminjaman aula Kecamatan Cikampek:\n1. Buka website SIPAF dan login dengan akun masyarakat.\n2. Masuk ke menu Layanan > Peminjaman Aula.\n3. Klik tombol Ajukan Peminjaman.\n4. Isi data permohonan: nama instansi, penanggung jawab, perihal kegiatan, tanggal dan jam pemakaian.\n5. Unggah file surat permohonan resmi dari instansi/pemohon.\n6. Kirim pengajuan dan pantau status di menu Riwayat.\n7. Jika status Disetujui, ikuti petunjuk teknis koordinasi pelaksanaan dari petugas Kecamatan.",
                'keywords' => 'alur aula, alur peminjaman aula, prosedur pinjam aula, langkah pinjam aula, tahapan aula',
                'priority' => 15,
                'is_active' => true,
            ],
            [
                'category' => 'Prosedur Pelayanan',
                'title' => 'Alur Langkah Demi Langkah Peminjaman Barang Inventaris',
                'question' => 'Bagaimana alur langkah demi langkah mengajukan peminjaman barang inventaris?',
                'answer' => "Tahapan peminjaman inventaris (kursi, sound system, PC, dll.):\n1. Login ke website SIPAF.\n2. Buka menu Layanan > Peminjaman Inventaris.\n3. Klik Ajukan Peminjaman.\n4. Pilih jenis barang dan tentukan jumlah unit yang dibutuhkan.\n5. Isi keperluan serta tanggal dan jam peminjaman dan rencana pengembalian.\n6. Kirim pengajuan dan tunggu verifikasi petugas.\n7. Pantau menu Riwayat. Jika Disetujui, serah terima barang dilakukan dengan petugas.\n8. Kembalikan barang tepat waktu sesuai jadwal dalam kondisi baik dan lengkap.",
                'keywords' => 'alur inventaris, alur pinjam kursi, langkah pinjam barang, prosedur inventaris, tahapan pinjam sound system',
                'priority' => 15,
                'is_active' => true,
            ],
            [
                'category' => 'Prosedur Pelayanan',
                'title' => 'Alur Langkah Demi Langkah Pengajuan Sertifikat/Piagam',
                'question' => 'Bagaimana alur langkah demi langkah mengajukan permohonan sertifikat/piagam?',
                'answer' => "Tahapan pengajuan sertifikat/piagam:\n1. Login akun masyarakat di SIPAF.\n2. Buka menu Layanan > Sertifikat / Piagam.\n3. Klik Buat Permohonan.\n4. Isi data dokumen, nama kegiatan, dan deskripsi tujuan pengajuan.\n5. Unggah surat permohonan resmi / dokumen pendukung.\n6. Kirim pengajuan dan pantau status di menu Riwayat.\n7. Jika Disetujui, unduh soft-copy sertifikat/piagam resmi langsung dari website SIPAF.",
                'keywords' => 'alur sertifikat, langkah pengajuan sertifikat, prosedur piagam, tahapan sertifikat, download sertifikat',
                'priority' => 15,
                'is_active' => true,
            ],
            [
                'category' => 'Prosedur Pelayanan',
                'title' => 'Verifikasi Petugas, Alasan Penolakan, dan Konsultasi Langsung',
                'question' => 'Bagaimana verifikasi berkas oleh petugas dan prosedur jika butuh konsultasi langsung?',
                'answer' => "Verifikasi & Penolakan: Petugas memverifikasi kelengkapan berkas, validitas kontak, dan bentrok jadwal/stok. Jika ditolak, alasan penolakan dapat dilihat di menu Riwayat dan warga dapat mengajukan ulang setelah melengkapi catatan.\n\nKonsultasi Langsung: Jika memerlukan konfirmasi langsung, masyarakat dapat datang ke Kantor Kecamatan Cikampek di Jl. Ahmad Yani No. 1, Cikampek pada jam pelayanan (Senin–Jumat pukul 07.45–15.45 WIB) di meja Pelayanan Administrasi Terpadu Kecamatan (Paten).",
                'keywords' => 'verifikasi petugas, alasan ditolak, mengapa ditolak, konsultasi langsung, datang ke kantor, paten cikampek',
                'priority' => 12,
                'is_active' => true,
            ],

            // =========================================================================
            // E. INFORMASI UMUM
            // =========================================================================
            [
                'category' => 'Informasi Umum',
                'title' => 'Alamat, Kontak Resmi, dan Jam Pelayanan Kantor Kecamatan',
                'question' => 'Di mana alamat kantor kecamatan, nomor kontak, dan jam operasional pelayanan?',
                'answer' => "Informasi Resmi Kantor Kecamatan Cikampek:\n- Alamat Kantor: Jl. Ahmad Yani No. 1, Cikampek, Kabupaten Karawang, Jawa Barat 41373.\n- Jam Pelayanan Kantor: Senin sampai Jumat pukul 07.45 – 15.45 WIB (Sabtu, Minggu & Hari Libur Nasional tutup).\n- Kontak Telepon: (0264) 301234.\n- Email: kec.cikampek@karawangkab.go.id.\n- Portal Layanan Online: Website SIPAF Kecamatan Cikampek (dapat diakses 24 jam).",
                'keywords' => 'alamat kantor camat, jam buka, jam tutup, jam kerja, jam operasional, no telepon kecamatan, nomor telepon, kontak kecamatan, hari kerja',
                'priority' => 15,
                'is_active' => true,
            ],
            [
                'category' => 'Informasi Umum',
                'title' => 'Pengalihan Pelayanan KTP, KK, dan Akta ke Gedung MPP',
                'question' => 'Apakah pembuatan atau pengurusan KTP, KK, dan Akta dilayani di kantor kecamatan?',
                'answer' => "PENTING: Pelayanan pembuatan baru, perpanjangan, atau perbaikan KTP Elektronik (KTP-el), Kartu Keluarga (KK), dan Akta Catatan Sipil (Kelahiran/Kematian) SUDAH TIDAK DILAYANI di Kantor Kecamatan Cikampek. Pelayanan dokumen kependudukan tersebut telah dialihkan sepenuhnya ke Gedung Mall Pelayanan Publik (MPP) yang berlokasi tepat di sebelah Kantor Kecamatan Cikampek. Silakan masyarakat langsung menuju loket Disdukcapil di Gedung MPP Cikampek.",
                'keywords' => 'buat ktp, rekam ktp, cetak ktp, kartu keluarga, bikin kk, akta kelahiran, akta kematian, disdukcapil, mpp cikampek, mall pelayanan publik, ktp di kecamatan',
                'priority' => 25,
                'is_active' => true,
            ],
            [
                'category' => 'Informasi Umum',
                'title' => 'Pengurusan Sertifikat Tanah: Kewenangan BPN Karawang',
                'question' => 'Apakah pengurusan sertifikat tanah bisa dilakukan melalui SIPAF?',
                'answer' => "Pengurusan sertifikat tanah TIDAK BISA dilakukan melalui website SIPAF. Layanan sertifikat pada SIPAF khusus untuk permohonan piagam/sertifikat penghargaan kegiatan dari Kecamatan Cikampek. Untuk pengurusan sertifikat tanah (SHM), penerbitan sertifikat, balik nama, atau pendaftaran tanah, seluruh wewenang resmi berada pada Kantor Pertanahan (Kantah ATR/BPN) Kabupaten Karawang. Pihak Kecamatan hanya melayani pengesahan surat keterangan riwayat tanah bila dipersyaratkan.",
                'keywords' => 'sertifikat tanah, bpn karawang, shm, akta tanah, balik nama tanah, sertifikat rumah, pendaftaran tanah, atr bpn, tanah sipaf',
                'priority' => 25,
                'is_active' => true,
            ],
            [
                'category' => 'Informasi Umum',
                'title' => 'Koordinasi Pelayanan di 10 Kantor Desa se-Kecamatan Cikampek',
                'question' => 'Layanan administrasi apa saja yang harus diurus terlebih dahulu di kantor desa?',
                'answer' => "Sebelum mengurus berkas ke kecamatan, masyarakat perlu membawa pengantar dari RT/RW dan Kantor Desa masing-masing (Cikampek Barat, Dawuan Tengah, Dawuan Timur, Dawuan Barat, Cikampek Selatan, Cikampek Timur, Cikampek Kota, Cikampek Pusaka, Kalihurip, Kamojing). Berkas seperti surat pengantar ahli waris, surat keterangan usaha awal, dan pengantar SKTM wajib disahkan Kepala Desa setempat.",
                'keywords' => 'kantor desa, pengantar rt rw, surat desa, kepala desa, 10 desa, pengantar kecamatan',
                'priority' => 8,
                'is_active' => true,
            ],

            // =========================================================================
            // F. FAQ (FREQUENTLY ASKED QUESTIONS)
            // =========================================================================
            [
                'category' => 'FAQ (Frequently Asked Questions)',
                'title' => 'FAQ: Cara Meminjam Aula Kecamatan Cikampek secara Online',
                'question' => 'Bagaimana cara meminjam aula Kecamatan Cikampek? Apakah bisa online?',
                'answer' => "Ya, peminjaman aula Kecamatan Cikampek dapat diajukan secara online melalui website SIPAF. Langkahnya: 1. Login ke website SIPAF menggunakan akun masyarakat; 2. Pilih menu Layanan > Peminjaman Aula; 3. Isi formulir permohonan (instansi, penanggung jawab, keperluan, tanggal & jam); 4. Unggah surat permohonan resmi dari instansi/komunitas; 5. Kirim pengajuan dan pantau status persetujuan di menu Riwayat.",
                'keywords' => 'faq aula, cara pinjam aula online, syarat pinjam aula, langkah aula sipaf, apakah peminjaman aula bisa dilakukan secara online',
                'priority' => 20,
                'is_active' => true,
            ],
            [
                'category' => 'FAQ (Frequently Asked Questions)',
                'title' => 'FAQ: Ketentuan Biaya Peminjaman Aula Kecamatan',
                'question' => 'Apakah peminjaman aula Kecamatan Cikampek dikenakan biaya?',
                'answer' => "Ketentuan biaya, retribusi, atau operasional peminjaman aula mengikuti peraturan daerah dan kebijakan resmi Pemerintah Kecamatan Cikampek. Sistem chatbot SIPAF tidak memungut, mengutip, atau menentukan nominal biaya sewa secara fiktif. Untuk mengetahui kepastian ketentuan biaya atau operasional aula, silakan melakukan konfirmasi langsung dengan petugas pelayanan di Kantor Kecamatan Cikampek saat pengajuan Anda diproses.",
                'keywords' => 'biaya aula, sewa aula, tarif aula, harga sewa gedung, gratis atau bayar, uang sewa aula, apakah peminjaman aula dikenakan biaya',
                'priority' => 20,
                'is_active' => true,
            ],
            [
                'category' => 'FAQ (Frequently Asked Questions)',
                'title' => 'FAQ: Cara Meminjam Kursi dan Ketersediaan Stok',
                'question' => 'Bagaimana cara meminjam kursi dan berapa jumlah kursi yang tersedia?',
                'answer' => "Peminjaman kursi diajukan secara online di SIPAF (menu Layanan > Peminjaman Inventaris) dengan memilih barang Kursi, mengisi jumlah unit yang dibutuhkan, serta tanggal dan jam peminjaman dan pengembalian. Mengenai jumlah kursi yang tersedia, sistem tidak menampilkan angka stok tetap fiktif karena ketersediaan bersifat dinamis mengikuti jadwal peminjaman berjalan. Ketersediaan barang yang Anda ajukan akan diverifikasi dan disetujui langsung oleh petugas Kecamatan Cikampek.",
                'keywords' => 'cara meminjam kursi, berapa jumlah kursi yang tersedia, jumlah kursi, stok kursi, pinjam kursi, ketersediaan kursi',
                'priority' => 20,
                'is_active' => true,
            ],
            [
                'category' => 'FAQ (Frequently Asked Questions)',
                'title' => 'FAQ: Peminjaman Sound System dan Barang Elektronik / PC',
                'question' => 'Apakah bisa meminjam sound system atau PC/komputer di kecamatan?',
                'answer' => "Ya, fasilitas sound system dan PC/komputer dapat diajukan peminjamannya melalui website SIPAF pada menu Layanan > Peminjaman Inventaris, disesuaikan dengan ketersediaan barang dan persetujuan dari petugas Kecamatan Cikampek.",
                'keywords' => 'apakah bisa meminjam sound system, pinjam sound system, pinjam pc, komputer, inventaris elektronik',
                'priority' => 15,
                'is_active' => true,
            ],
            [
                'category' => 'FAQ (Frequently Asked Questions)',
                'title' => 'FAQ: Pengajuan Sertifikat/Piagam SIPAF vs Sertifikat Tanah',
                'question' => 'Apakah pengajuan sertifikat tanah bisa melalui SIPAF?',
                'answer' => "Tidak bisa. Layanan sertifikat pada SIPAF khusus untuk permohonan sertifikat penghargaan atau piagam kegiatan dari Kecamatan Cikampek. Pengurusan sertifikat kepemilikan tanah (SHM/HGB) merupakan kewenangan Badan Pertanahan Nasional (ATR/BPN) Kabupaten Karawang dan tidak dilayani di SIPAF.",
                'keywords' => 'apakah pengajuan sertifikat tanah bisa melalui sipaf, sertifikat tanah, bpn karawang, beda sertifikat sipaf',
                'priority' => 20,
                'is_active' => true,
            ],
            [
                'category' => 'FAQ (Frequently Asked Questions)',
                'title' => 'FAQ: Cara Mengetahui Status Pengajuan di SIPAF',
                'question' => 'Bagaimana cara mengetahui status pengajuan saya di SIPAF?',
                'answer' => "Anda dapat mengetahui perkembangan status pengajuan secara real-time dengan login ke website SIPAF dan membuka menu 'Riwayat'. Di halaman tersebut tertera status: Menunggu Persetujuan, Disetujui, Ditolak (lengkap dengan catatan alasan penolakan petugas), atau Selesai.",
                'keywords' => 'bagaimana cara mengetahui status pengajuan, cek status pengajuan, cara melihat status, riwayat sipaf',
                'priority' => 18,
                'is_active' => true,
            ],
        ];

        // Clean slate to prevent duplicate records
        AiKnowledge::truncate();

        foreach ($data as $item) {
            AiKnowledge::create($item);
        }
    }
}
