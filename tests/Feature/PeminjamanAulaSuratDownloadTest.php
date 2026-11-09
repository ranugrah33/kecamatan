<?php

namespace Tests\Feature;

use App\Models\Aula;
use App\Models\PeminjamanAula;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PeminjamanAulaSuratDownloadTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;
    protected User $masyarakat;
    protected User $otherUser;
    protected Aula $aula;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->masyarakat = User::factory()->create(['role' => 'masyarakat']);
        $this->otherUser = User::factory()->create(['role' => 'masyarakat']);

        $this->aula = Aula::firstOrCreate(
            ['nama' => 'Aula Utama'],
            [
                'lokasi' => 'Lantai 2 Kantor Kecamatan',
                'deskripsi' => 'Aula serbaguna',
                'kapasitas' => 200,
                'fasilitas' => ['AC', 'Sound System'],
                'jam_buka' => '08:00:00',
                'jam_tutup' => '16:00:00',
            ]
        );
    }

    public function test_masyarakat_can_download_surat_izin(): void
    {
        Storage::fake('public');

        $fakeFilePath = 'surat_izin/test_surat_final.pdf';
        Storage::disk('public')->put($fakeFilePath, 'Fake PDF Content of Surat Izin');

        $peminjaman = PeminjamanAula::create([
            'user_id' => $this->masyarakat->id,
            'aula_id' => $this->aula->id,
            'nomor_pengajuan' => 'AULA-20261109-0099',
            'instansi' => 'Organisasi Masyarakat',
            'penanggung_jawab' => 'Ahmad',
            'nama_kegiatan' => 'Musyawarah Warga',
            'jenis_kegiatan' => 'Sosial',
            'deskripsi_kegiatan' => 'Rapat koordinasi',
            'jumlah_peserta' => 50,
            'tanggal' => now()->addDays(2)->format('Y-m-d'),
            'jam_mulai' => '09:00',
            'jam_selesai' => '11:00',
            'nomor_surat_permohonan' => '001/ORG/2026',
            'status' => 'Surat Tersedia',
            'nomor_surat_izin' => '400.2/099/Kec-Ckp/2026',
            'file_surat_final' => $fakeFilePath,
        ]);

        $response = $this->actingAs($this->masyarakat)->get(route('masyarakat.peminjaman_aula.downloadSurat', $peminjaman->id));
        $response->assertStatus(200);
        $response->assertHeader('content-disposition', 'attachment; filename=Surat_Izin_AULA-20261109-0099.pdf');
    }

    public function test_other_user_cannot_download_surat_izin(): void
    {
        Storage::fake('public');

        $fakeFilePath = 'surat_izin/test_surat_final_secret.pdf';
        Storage::disk('public')->put($fakeFilePath, 'Secret Surat Content');

        $peminjaman = PeminjamanAula::create([
            'user_id' => $this->masyarakat->id,
            'aula_id' => $this->aula->id,
            'nomor_pengajuan' => 'AULA-20261109-0098',
            'instansi' => 'Organisasi Masyarakat',
            'penanggung_jawab' => 'Ahmad',
            'nama_kegiatan' => 'Musyawarah Warga',
            'jenis_kegiatan' => 'Sosial',
            'deskripsi_kegiatan' => 'Rapat koordinasi',
            'jumlah_peserta' => 50,
            'tanggal' => now()->addDays(2)->format('Y-m-d'),
            'jam_mulai' => '09:00',
            'jam_selesai' => '11:00',
            'nomor_surat_permohonan' => '001/ORG/2026',
            'status' => 'Surat Tersedia',
            'file_surat_final' => $fakeFilePath,
        ]);

        $response = $this->actingAs($this->otherUser)->get(route('masyarakat.peminjaman_aula.downloadSurat', $peminjaman->id));
        $response->assertStatus(404);
    }

    public function test_download_redirects_with_error_if_file_missing(): void
    {
        Storage::fake('public');

        $peminjaman = PeminjamanAula::create([
            'user_id' => $this->masyarakat->id,
            'aula_id' => $this->aula->id,
            'nomor_pengajuan' => 'AULA-20261109-0097',
            'instansi' => 'Organisasi Masyarakat',
            'penanggung_jawab' => 'Ahmad',
            'nama_kegiatan' => 'Musyawarah Warga',
            'jenis_kegiatan' => 'Sosial',
            'deskripsi_kegiatan' => 'Rapat koordinasi',
            'jumlah_peserta' => 50,
            'tanggal' => now()->addDays(2)->format('Y-m-d'),
            'jam_mulai' => '09:00',
            'jam_selesai' => '11:00',
            'nomor_surat_permohonan' => '001/ORG/2026',
            'status' => 'Surat Tersedia',
            'file_surat_final' => 'surat_izin/missing_file.pdf',
        ]);

        $response = $this->actingAs($this->masyarakat)->get(route('masyarakat.peminjaman_aula.downloadSurat', $peminjaman->id));
        $response->assertRedirect();
        $response->assertSessionHasErrors();
    }

    public function test_admin_can_download_surat_izin(): void
    {
        Storage::fake('public');

        $fakeFilePath = 'surat_izin/test_admin_surat.pdf';
        Storage::disk('public')->put($fakeFilePath, 'Admin downloaded content');

        $peminjaman = PeminjamanAula::create([
            'user_id' => $this->masyarakat->id,
            'aula_id' => $this->aula->id,
            'nomor_pengajuan' => 'AULA-20261109-0096',
            'instansi' => 'Organisasi Masyarakat',
            'penanggung_jawab' => 'Ahmad',
            'nama_kegiatan' => 'Musyawarah Warga',
            'jenis_kegiatan' => 'Sosial',
            'deskripsi_kegiatan' => 'Rapat koordinasi',
            'jumlah_peserta' => 50,
            'tanggal' => now()->addDays(2)->format('Y-m-d'),
            'jam_mulai' => '09:00',
            'jam_selesai' => '11:00',
            'nomor_surat_permohonan' => '001/ORG/2026',
            'status' => 'Surat Tersedia',
            'file_surat_final' => $fakeFilePath,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.peminjaman_aula.downloadSurat', $peminjaman->id));
        $response->assertStatus(200);
        $response->assertHeader('content-disposition', 'attachment; filename=Surat_Izin_AULA-20261109-0096.pdf');
    }

    public function test_storage_fallback_route_serves_file(): void
    {
        Storage::fake('public');

        $fakeFilePath = 'surat_izin/fallback_test.pdf';
        Storage::disk('public')->put($fakeFilePath, 'Fallback PDF content');

        $response = $this->get('/storage/' . $fakeFilePath);
        $response->assertStatus(200);
    }
}
