<?php

namespace Tests\Feature;

use App\Models\CertificateRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CertificateRequestNotificationSpamTest extends TestCase
{
    use DatabaseTransactions;

    protected User $masyarakat;

    protected function setUp(): void
    {
        parent::setUp();

        $this->masyarakat = User::factory()->create([
            'role' => 'masyarakat',
            'email' => 'warga.sertifikat.test@example.com',
            'name' => 'Warga Sertifikat Test',
            'no_hp' => '081233445566',
        ]);
    }

    /**
     * 1. Halaman formulir create permohonan sertifikat menampilkan info pengiriman email dan spam.
     */
    public function test_halaman_create_sertifikat_menampilkan_pemberitahuan_email_dan_spam(): void
    {
        $response = $this->actingAs($this->masyarakat)
            ->get(route('masyarakat.sertifikat.create'));

        $response->assertStatus(200);
        $response->assertSee('Pemberitahuan Pengiriman Notifikasi & Berkas');
        $response->assertSee('Spam');
        $response->assertSee('warga.sertifikat.test@example.com');
    }

    /**
     * 2. Pengajuan permohonan sertifikat berhasil dan redirect dengan flash session success mengenai email dan spam.
     */
    public function test_masyarakat_submit_certificate_request_redirects_with_email_spam_notice(): void
    {
        $payload = [
            'applicant_name' => 'Warga Sertifikat Test',
            'phone' => '081233445566',
            'document_type' => 'Sertifikat',
            'activity_name' => 'Pelatihan Digital Marketing UMKM',
            'activity_theme' => 'Go Digital 2026',
            'activity_date' => now()->addDays(7)->format('Y-m-d'),
            'activity_place' => 'Aula Kecamatan Cikampek',
            'purpose' => 'Sertifikat apresiasi peserta pelatihan',
            'description' => 'Mohon bantuan penerbitan sertifikat',
        ];

        $response = $this->actingAs($this->masyarakat)
            ->post(route('masyarakat.sertifikat.store'), $payload);

        $response->assertSessionHasNoErrors();

        $cert = CertificateRequest::where('applicant_name', 'Warga Sertifikat Test')->latest('id')->first();
        $this->assertNotNull($cert);

        $response->assertRedirect(route('masyarakat.sertifikat.show', $cert->id));
        $response->assertSessionHas('success');

        $sessionSuccess = session('success');
        $this->assertStringContainsString('spam', strtolower($sessionSuccess));
        $this->assertStringContainsString('email', strtolower($sessionSuccess));
    }

    /**
     * 3. Halaman detail (show) sertifikat memuat pop-up modal dan banner pemberitahuan mengenai email dan spam.
     */
    public function test_halaman_show_sertifikat_menampilkan_popup_dan_notifikasi_spam(): void
    {
        $cert = CertificateRequest::create([
            'user_id' => $this->masyarakat->id,
            'request_code' => 'CERT-TEST-0001',
            'applicant_name' => 'Warga Sertifikat Test',
            'certificate_name' => 'Warga Sertifikat Test',
            'phone' => '081233445566',
            'document_type' => 'Sertifikat',
            'activity_name' => 'Pelatihan Digital Marketing UMKM',
            'activity_date' => now()->addDays(7)->format('Y-m-d'),
            'activity_place' => 'Aula Kecamatan Cikampek',
            'purpose' => 'Sertifikat apresiasi peserta pelatihan',
            'status' => 'Diajukan',
        ]);

        $response = $this->actingAs($this->masyarakat)
            ->get(route('masyarakat.sertifikat.show', $cert->id));

        $response->assertStatus(200);

        // Memastikan modal dan teks petunjuk ada
        $response->assertSee('showEmailModal');
        $response->assertSee('Pemberitahuan Penting');
        $response->assertSee('Cek Email & Periksa Folder Spam Anda');
        $response->assertSee('warga.sertifikat.test@example.com');
        $response->assertSee('Spam');
        $response->assertSee('Bukan Spam');
        $response->assertSee('Mengerti, Saya Paham');

        // Memastikan banner notifikasi permanen di halaman juga ada
        $response->assertSee('Informasi Pengecekan Email & Folder Spam');
        $response->assertSee('Buka Pop-up Petunjuk');
    }

    /**
     * 4. Halaman index permohonan sertifikat juga memuat panduan informasi spam.
     */
    public function test_halaman_index_sertifikat_menampilkan_informasi_spam(): void
    {
        $response = $this->actingAs($this->masyarakat)
            ->get(route('masyarakat.sertifikat.index'));

        $response->assertStatus(200);
        $response->assertSee('Spam');
    }
}
