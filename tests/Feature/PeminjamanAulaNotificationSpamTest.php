<?php

namespace Tests\Feature;

use App\Models\Aula;
use App\Models\PeminjamanAula;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PeminjamanAulaNotificationSpamTest extends TestCase
{
    use DatabaseTransactions;

    protected User $masyarakat;
    protected Aula $aula;

    protected function setUp(): void
    {
        parent::setUp();

        $this->masyarakat = User::factory()->create([
            'role' => 'masyarakat',
            'email' => 'warga.cikampek.test@example.com',
            'name' => 'Warga Cikampek Test',
        ]);

        $this->aula = Aula::first() ?? Aula::create([
            'nama' => 'Aula Serbaguna Cikampek',
            'lokasi' => 'Lantai 2 Kantor Kecamatan',
            'deskripsi' => 'Aula serbaguna kapasitas besar',
            'kapasitas' => 150,
            'fasilitas' => ['AC', 'Sound System', 'Proyektor'],
            'jam_buka' => '08:00:00',
            'jam_tutup' => '22:00:00',
        ]);
    }

    /**
     * 1. Pengajuan peminjaman aula berhasil dan redirect dengan flash session success mengenai email dan spam.
     */
    public function test_masyarakat_submit_peminjaman_aula_redirects_with_email_spam_notice(): void
    {
        $fakePdf = UploadedFile::fake()->create('surat_permohonan.pdf', 200, 'application/pdf');

        $payload = [
            'instansi' => 'Karang Taruna Sukaseuri',
            'penanggung_jawab' => 'Budi Setiawan',
            'nama_kegiatan' => 'Musyawarah Pemuda',
            'jenis_kegiatan' => 'Organisasi',
            'deskripsi_kegiatan' => 'Pembahasan program kerja pemuda tahun 2026',
            'jumlah_peserta' => 35,
            'tanggal' => now()->addDays(20)->format('Y-m-d'),
            'jam_mulai' => '09:00',
            'jam_selesai' => '12:00',
            'nomor_surat_permohonan' => '05/KT-SKS/XI/2026',
            'file_surat_permohonan' => $fakePdf,
        ];

        $response = $this->actingAs($this->masyarakat)
            ->post(route('masyarakat.peminjaman_aula.store'), $payload);

        $response->assertSessionHasNoErrors();

        $peminjaman = PeminjamanAula::where('nomor_surat_permohonan', '05/KT-SKS/XI/2026')->first();
        $this->assertNotNull($peminjaman);

        $response->assertRedirect(route('masyarakat.peminjaman_aula.sukses', $peminjaman->id));
        $response->assertSessionHas('success');

        $sessionSuccess = session('success');
        $this->assertStringContainsString('spam', strtolower($sessionSuccess));
        $this->assertStringContainsString('email', strtolower($sessionSuccess));
    }

    /**
     * 2. Halaman sukses peminjaman aula memuat pop-up modal dan pemberitahuan mengenai email dan spam.
     */
    public function test_halaman_sukses_peminjaman_aula_menampilkan_popup_dan_notifikasi_spam(): void
    {
        $peminjaman = PeminjamanAula::create([
            'user_id' => $this->masyarakat->id,
            'aula_id' => $this->aula->id,
            'nomor_pengajuan' => 'AULA-NOTIF-0001',
            'penanggung_jawab' => 'Budi Setiawan',
            'nama_kegiatan' => 'Musyawarah Pemuda',
            'jenis_kegiatan' => 'Organisasi',
            'deskripsi_kegiatan' => 'Pembahasan program kerja pemuda tahun 2026',
            'jumlah_peserta' => 35,
            'tanggal' => now()->addDays(21)->format('Y-m-d'),
            'jam_mulai' => '09:00',
            'jam_selesai' => '12:00',
            'nomor_surat_permohonan' => '05/KT-SKS/XI/2026',
            'status' => 'Menunggu Verifikasi',
        ]);

        $response = $this->actingAs($this->masyarakat)
            ->get(route('masyarakat.peminjaman_aula.sukses', $peminjaman->id));

        $response->assertStatus(200);

        // Memastikan pop-up dan teks modal ada
        $response->assertSee('showEmailModal');
        $response->assertSee('Pemberitahuan Penting');
        $response->assertSee('Cek Email & Periksa Folder Spam Anda');
        $response->assertSee('warga.cikampek.test@example.com');
        $response->assertSee('Spam');
        $response->assertSee('Bukan Spam');
        $response->assertSee('Mengerti, Saya Paham');

        // Memastikan banner notifikasi permanen di halaman juga ada
        $response->assertSee('Informasi Pengecekan Email & Folder Spam');
        $response->assertSee('Buka Pop-up Petunjuk');
    }

    /**
     * 3. Halaman detail (show) peminjaman aula memuat pengingat notifikasi email dan spam.
     */
    public function test_halaman_show_peminjaman_aula_menampilkan_pengingat_email_dan_spam(): void
    {
        $peminjaman = PeminjamanAula::create([
            'user_id' => $this->masyarakat->id,
            'aula_id' => $this->aula->id,
            'nomor_pengajuan' => 'AULA-NOTIF-0002',
            'penanggung_jawab' => 'Budi Setiawan',
            'nama_kegiatan' => 'Musyawarah Pemuda',
            'jenis_kegiatan' => 'Organisasi',
            'deskripsi_kegiatan' => 'Pembahasan program kerja',
            'jumlah_peserta' => 35,
            'tanggal' => now()->addDays(22)->format('Y-m-d'),
            'jam_mulai' => '09:00',
            'jam_selesai' => '12:00',
            'nomor_surat_permohonan' => '06/KT-SKS/XI/2026',
            'status' => 'Menunggu Verifikasi',
        ]);

        $response = $this->actingAs($this->masyarakat)
            ->get(route('masyarakat.peminjaman_aula.show', $peminjaman->id));

        $response->assertStatus(200);
        $response->assertSee('Pemberitahuan Email & Folder Spam');
        $response->assertSee('warga.cikampek.test@example.com');
        $response->assertSee('Spam');
    }
}
