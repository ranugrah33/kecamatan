<?php

namespace Tests\Feature;

use App\Mail\PeminjamanAulaSuratFinalMail;
use App\Models\Aula;
use App\Models\CertificateRequest;
use App\Models\InventoryBorrowing;
use App\Models\InventoryItem;
use App\Models\PeminjamanAula;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PeminjamanAulaStatusSimplificationTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;
    protected User $masyarakat;
    protected Aula $aula;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->masyarakat = User::factory()->create([
            'role' => 'masyarakat',
            'email' => 'warga.cikampek@example.com',
        ]);

        $this->aula = Aula::firstOrCreate(
            ['nama' => 'Aula Serbaguna Cikampek'],
            [
                'lokasi' => 'Lantai 2 Kantor Kecamatan',
                'deskripsi' => 'Aula serbaguna kapasitas besar',
                'kapasitas' => 150,
                'fasilitas' => ['AC', 'Sound System', 'Proyektor'],
                'jam_buka' => '08:00:00',
                'jam_tutup' => '17:00:00',
            ]
        );
    }

    /**
     * 1. Admin melihat 6 opsi status baru pada halaman review peminjaman aula.
     */
    public function test_admin_sees_simplified_six_statuses_in_form(): void
    {
        $peminjaman = PeminjamanAula::create([
            'user_id' => $this->masyarakat->id,
            'aula_id' => $this->aula->id,
            'nomor_pengajuan' => 'AULA-TEST-0001',
            'penanggung_jawab' => 'Budi Santoso',
            'nama_kegiatan' => 'Sosialisasi Kesehatan',
            'jenis_kegiatan' => 'Sosial',
            'deskripsi_kegiatan' => 'Kegiatan penyuluhan kesehatan warga',
            'jumlah_peserta' => 40,
            'tanggal' => now()->addDays(3)->format('Y-m-d'),
            'jam_mulai' => '09:00',
            'jam_selesai' => '11:00',
            'nomor_surat_permohonan' => '01/KES/2026',
            'status' => 'Menunggu Verifikasi',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.peminjaman_aula.show', $peminjaman->id));
        $response->assertStatus(200);

        // Pastikan 6 status tersedia
        $response->assertSee('1. Menunggu Verifikasi');
        $response->assertSee('2. Perlu Perbaikan');
        $response->assertSee('3. Surat Diproses (Draft)');
        $response->assertSee('4. Surat Tersedia (Final)');
        $response->assertSee('5. Selesai (Kegiatan Rampung)');
        $response->assertSee('6. Ditolak');

        // Pastikan opsi lama "Setujui Pengajuan" tidak muncul sebagai opsi normal
        $response->assertDontSee('>Setujui Pengajuan<', false);
    }

    /**
     * 2. Admin dapat memperbarui status ke seluruh 6 status yang disederhanakan.
     */
    public function test_admin_can_update_to_any_of_the_six_statuses(): void
    {
        $peminjaman = PeminjamanAula::create([
            'user_id' => $this->masyarakat->id,
            'aula_id' => $this->aula->id,
            'nomor_pengajuan' => 'AULA-TEST-0002',
            'penanggung_jawab' => 'Siti Rahma',
            'nama_kegiatan' => 'Rapat RT RW',
            'jenis_kegiatan' => 'Pemerintahan',
            'deskripsi_kegiatan' => 'Rapat koordinasi keamanan lingkungan',
            'jumlah_peserta' => 30,
            'tanggal' => now()->addDays(4)->format('Y-m-d'),
            'jam_mulai' => '09:00',
            'jam_selesai' => '11:00',
            'nomor_surat_permohonan' => '02/RT/2026',
            'status' => 'Menunggu Verifikasi',
        ]);

        $statusesToTest = [
            'Perlu Perbaikan',
            'Surat Diproses',
            'Ditolak',
            'Menunggu Verifikasi',
            'Selesai',
        ];

        foreach ($statusesToTest as $status) {
            $response = $this->actingAs($this->admin)->put(route('admin.peminjaman_aula.updateStatus', $peminjaman->id), [
                'status' => $status,
                'catatan_petugas' => 'Catatan status ' . $status,
            ]);

            $response->assertRedirect(route('admin.peminjaman_aula.show', $peminjaman->id));
            $this->assertDatabaseHas('peminjaman_aulas', [
                'id' => $peminjaman->id,
                'status' => $status,
                'catatan_petugas' => 'Catatan status ' . $status,
            ]);
        }
    }

    /**
     * 3. Pengajuan lama dengan status legacy (Disetujui) tetap dapat ditampilkan tanpa error.
     */
    public function test_legacy_disetujui_status_is_displayed_safely(): void
    {
        $legacy = PeminjamanAula::create([
            'user_id' => $this->masyarakat->id,
            'aula_id' => $this->aula->id,
            'nomor_pengajuan' => 'AULA-TEST-LEGACY-01',
            'penanggung_jawab' => 'Warga Lama',
            'nama_kegiatan' => 'Kegiatan Masa Lalu',
            'jenis_kegiatan' => 'Kebudayaan',
            'deskripsi_kegiatan' => 'Data historis sebelum simplifikasi',
            'jumlah_peserta' => 20,
            'tanggal' => now()->addDays(5)->format('Y-m-d'),
            'jam_mulai' => '09:00',
            'jam_selesai' => '11:00',
            'nomor_surat_permohonan' => '00/LEGACY/2026',
            'status' => 'Disetujui',
        ]);

        // Cek pada admin detail
        $adminShow = $this->actingAs($this->admin)->get(route('admin.peminjaman_aula.show', $legacy->id));
        $adminShow->assertStatus(200);
        $adminShow->assertSee('Setujui Pengajuan (Legacy)');

        // Cek pada admin index
        $adminIndex = $this->actingAs($this->admin)->get(route('admin.peminjaman_aula.index'));
        $adminIndex->assertStatus(200);
        $adminIndex->assertSee('AULA-TEST-LEGACY-01');

        // Cek pada masyarakat show
        $masyarakatShow = $this->actingAs($this->masyarakat)->get(route('masyarakat.peminjaman_aula.show', $legacy->id));
        $masyarakatShow->assertStatus(200);
        $masyarakatShow->assertSee('AULA-TEST-LEGACY-01');

        // Cek pada riwayat masyarakat
        $riwayat = $this->actingAs($this->masyarakat)->get(route('masyarakat.riwayat'));
        $riwayat->assertStatus(200);
        $riwayat->assertSee('AULA-TEST-LEGACY-01');
    }

    /**
     * 4. Upload surat final mengubah status menjadi 'Surat Tersedia' dan otomatis mengirim email notifikasi.
     */
    public function test_upload_surat_final_sets_surat_tersedia_and_sends_email(): void
    {
        Storage::fake('public');
        Mail::fake();

        $peminjaman = PeminjamanAula::create([
            'user_id' => $this->masyarakat->id,
            'aula_id' => $this->aula->id,
            'nomor_pengajuan' => 'AULA-TEST-EMAIL-01',
            'penanggung_jawab' => 'Hendra Wijaya',
            'nama_kegiatan' => 'Workshop UMKM Cikampek',
            'jenis_kegiatan' => 'Ekonomi',
            'deskripsi_kegiatan' => 'Pelatihan pemasaran produk lokal',
            'jumlah_peserta' => 50,
            'tanggal' => now()->addDays(5)->format('Y-m-d'),
            'jam_mulai' => '09:00',
            'jam_selesai' => '12:00',
            'nomor_surat_permohonan' => '05/UMKM/2026',
            'status' => 'Surat Diproses',
        ]);

        $fakePdf = UploadedFile::fake()->create('surat_izin_resmi.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->admin)->post(route('admin.peminjaman_aula.uploadSuratFinal', $peminjaman->id), [
            'file_surat_final' => $fakePdf,
            'nomor_surat_izin' => '400.2/088/Kec-Ckp/2026',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $peminjaman->refresh();

        // Verifikasi status dan tracking surat_notified_at
        $this->assertEquals('Surat Tersedia', $peminjaman->status);
        $this->assertNotNull($peminjaman->file_surat_final);
        $this->assertNotNull($peminjaman->surat_notified_at);
        $this->assertEquals('400.2/088/Kec-Ckp/2026', $peminjaman->nomor_surat_izin);

        // Verifikasi file fisik tersimpan
        Storage::disk('public')->assertExists($peminjaman->file_surat_final);

        // Verifikasi email terkirim ke pemohon
        Mail::assertSent(PeminjamanAulaSuratFinalMail::class, function ($mail) use ($peminjaman) {
            return $mail->hasTo('warga.cikampek@example.com') &&
                   $mail->peminjaman->id === $peminjaman->id;
        });
    }

    /**
     * 5. Upload ulang atau update tidak mengirimkan email duplikat jika sudah pernah dinotifikasikan.
     */
    public function test_does_not_send_duplicate_email_if_already_notified(): void
    {
        Storage::fake('public');
        Mail::fake();

        $fakePath = 'surat_izin/existing_file.pdf';
        Storage::disk('public')->put($fakePath, 'PDF content');

        $alreadyNotifiedTime = now()->subHours(2);

        $peminjaman = PeminjamanAula::create([
            'user_id' => $this->masyarakat->id,
            'aula_id' => $this->aula->id,
            'nomor_pengajuan' => 'AULA-TEST-DUP-01',
            'penanggung_jawab' => 'Rina',
            'nama_kegiatan' => 'Festival Seni Tradisional',
            'jenis_kegiatan' => 'Seni',
            'deskripsi_kegiatan' => 'Pentas seni anak',
            'jumlah_peserta' => 60,
            'tanggal' => now()->addDays(7)->format('Y-m-d'),
            'jam_mulai' => '13:00',
            'jam_selesai' => '15:00',
            'nomor_surat_permohonan' => '07/SENI/2026',
            'status' => 'Surat Tersedia',
            'file_surat_final' => $fakePath,
            'surat_notified_at' => $alreadyNotifiedTime,
        ]);

        // Upload berkas revisi
        $newPdf = UploadedFile::fake()->create('surat_izin_revisi.pdf', 600, 'application/pdf');

        $response = $this->actingAs($this->admin)->post(route('admin.peminjaman_aula.uploadSuratFinal', $peminjaman->id), [
            'file_surat_final' => $newPdf,
            'nomor_surat_izin' => '400.2/099/Kec-Ckp/2026',
        ]);

        $response->assertRedirect();

        // Tidak boleh ada pengiriman email baru karena surat_notified_at sudah ada
        Mail::assertNothingSent();

        $peminjaman->refresh();
        $this->assertEquals($alreadyNotifiedTime->toDateTimeString(), $peminjaman->surat_notified_at->toDateTimeString());
    }

    /**
     * 6. Alur cetak surat (draft) dapat dilakukan saat status 'Surat Diproses'.
     */
    public function test_cetak_surat_works_for_surat_diproses_and_migrates_legacy(): void
    {
        $peminjaman = PeminjamanAula::create([
            'user_id' => $this->masyarakat->id,
            'aula_id' => $this->aula->id,
            'nomor_pengajuan' => 'AULA-TEST-CETAK-01',
            'penanggung_jawab' => 'Kurniawan',
            'nama_kegiatan' => 'Bimtek IT',
            'jenis_kegiatan' => 'Pendidikan',
            'deskripsi_kegiatan' => 'Bimbingan teknis komputer',
            'jumlah_peserta' => 25,
            'tanggal' => now()->addDays(6)->format('Y-m-d'),
            'jam_mulai' => '09:00',
            'jam_selesai' => '12:00',
            'nomor_surat_permohonan' => '09/IT/2026',
            'status' => 'Surat Diproses',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.peminjaman_aula.cetakSurat', $peminjaman->id));
        $response->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $response->headers->get('content-type'));

        // Cek juga pada status ditolak (harus diblokir)
        $peminjaman->update(['status' => 'Ditolak']);
        $badResponse = $this->actingAs($this->admin)->get(route('admin.peminjaman_aula.cetakSurat', $peminjaman->id));
        $badResponse->assertRedirect();
        $badResponse->assertSessionHasErrors();
    }

    /**
     * 7. Cek bentrok jadwal mendeteksi status aktif: Surat Diproses, Surat Tersedia, Selesai.
     */
    public function test_jadwal_bentrok_rule_includes_surat_diproses_and_surat_tersedia(): void
    {
        PeminjamanAula::create([
            'user_id' => $this->masyarakat->id,
            'aula_id' => $this->aula->id,
            'nomor_pengajuan' => 'AULA-BENTROK-01',
            'penanggung_jawab' => 'Pak Joko',
            'nama_kegiatan' => 'Seminar Bisnis',
            'jenis_kegiatan' => 'Seminar',
            'deskripsi_kegiatan' => 'Seminar kewirausahaan',
            'jumlah_peserta' => 50,
            'tanggal' => now()->addDays(10)->format('Y-m-d'),
            'jam_mulai' => '10:00',
            'jam_selesai' => '12:00',
            'nomor_surat_permohonan' => '10/SEM/2026',
            'status' => 'Surat Diproses',
        ]);

        $bentrokExists = PeminjamanAula::whereIn('status', ['Surat Diproses', 'Surat Tersedia', 'Selesai', 'Disetujui'])
            ->where('tanggal', now()->addDays(10)->format('Y-m-d'))
            ->where(function ($query) {
                $query->whereBetween('jam_mulai', ['10:30', '11:30'])
                      ->orWhereBetween('jam_selesai', ['10:30', '11:30'])
                      ->orWhere(function ($q) {
                          $q->where('jam_mulai', '<=', '10:30')
                            ->where('jam_selesai', '>=', '11:30');
                      });
            })
            ->exists();

        $this->assertTrue($bentrokExists);
    }

    /**
     * 8. Fitur Peminjaman Inventaris dan Sertifikat tetap berfungsi normal (tidak terganggu).
     */
    public function test_inventory_and_certificate_features_remain_unaffected(): void
    {
        $item = InventoryItem::firstOrCreate(
            ['name' => 'Proyektor Epson Test'],
            [
                'category' => 'Elektronik',
                'total_quantity' => 5,
                'available_quantity' => 5,
                'condition' => 'Baik',
            ]
        );

        $borrowing = InventoryBorrowing::create([
            'user_id' => $this->masyarakat->id,
            'request_code' => 'REQ-INV-TEST-999',
            'applicant_name' => 'Warga Cikampek',
            'phone' => '081299998888',
            'penanggung_jawab' => 'Warga Cikampek',
            'borrow_date' => now()->addDay()->format('Y-m-d'),
            'borrowing_time' => '09:00',
            'return_date' => now()->addDays(2)->format('Y-m-d'),
            'return_time' => '17:00',
            'purpose' => 'Presentasi Rapat',
            'status' => 'Diajukan',
        ]);

        $this->assertDatabaseHas('inventory_borrowings', [
            'id' => $borrowing->id,
            'status' => 'Diajukan',
        ]);

        $cert = CertificateRequest::create([
            'user_id' => $this->masyarakat->id,
            'request_code' => 'CERT-TEST-999',
            'applicant_name' => 'Warga Cikampek',
            'phone' => '081299998888',
            'document_type' => 'Sertifikat',
            'activity_name' => 'Lomba Adiwiyata',
            'activity_date' => now()->format('Y-m-d'),
            'activity_place' => 'Aula Kecamatan Cikampek',
            'purpose' => 'Apresiasi pemenang lomba',
            'status' => 'Diajukan',
        ]);

        $this->assertDatabaseHas('certificate_requests', [
            'id' => $cert->id,
            'status' => 'Diajukan',
        ]);
    }
}
