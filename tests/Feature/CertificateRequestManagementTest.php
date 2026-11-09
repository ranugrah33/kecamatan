<?php

namespace Tests\Feature;

use App\Models\CertificateRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CertificateRequestManagementTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;
    protected User $masyarakat;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->masyarakat = User::factory()->create([
            'role' => 'masyarakat',
        ]);
    }

    /**
     * Test 1: Masyarakat dapat melihat layanan dan halaman form pengajuan sertifikat.
     */
    public function test_masyarakat_can_view_certificate_service_and_create_page(): void
    {
        $responseIndex = $this->actingAs($this->masyarakat)->get(route('masyarakat.sertifikat.index'));
        $responseIndex->assertStatus(200);
        $responseIndex->assertViewIs('masyarakat.sertifikat.index');

        $responseCreate = $this->actingAs($this->masyarakat)->get(route('masyarakat.sertifikat.create'));
        $responseCreate->assertStatus(200);
        $responseCreate->assertViewIs('masyarakat.sertifikat.create');
        // Pastikan tidak ada tulisan auto-generated badge
        $responseCreate->assertDontSee('Auto-Generated');
        $responseCreate->assertDontSee('isiContohTeks');
    }

    /**
     * Test 2: Masyarakat dapat mengisi dan mengirim pengajuan sertifikat beserta dokumen persyaratan.
     */
    public function test_masyarakat_can_submit_certificate_request_with_supporting_document(): void
    {
        $file = UploadedFile::fake()->create('surat_permohonan.pdf', 100, 'application/pdf');

        $payload = [
            'applicant_name' => 'Budi Santoso',
            'phone' => '081234567890',
            'document_type' => 'Sertifikat',
            'activity_name' => 'Pelatihan Desain Grafis',
            'activity_theme' => 'Kreatif Digital 2026',
            'activity_date' => '2026-11-20',
            'activity_place' => 'Aula Kecamatan Cikampek',
            'purpose' => 'Permohonan sertifikat apresiasi narasumber',
            'description' => 'Mohon diproses untuk 1 narasumber.',
            'application_file' => $file,
        ];

        $response = $this->actingAs($this->masyarakat)->post(route('masyarakat.sertifikat.store'), $payload);

        $this->assertDatabaseHas('certificate_requests', [
            'user_id' => $this->masyarakat->id,
            'applicant_name' => 'Budi Santoso',
            'activity_name' => 'Pelatihan Desain Grafis',
            'status' => 'Diajukan',
        ]);

        $created = CertificateRequest::where('user_id', $this->masyarakat->id)->latest('id')->first();
        $this->assertNotNull($created);
        $response->assertRedirect(route('masyarakat.sertifikat.show', $created->id));

        if ($created && $created->application_file && file_exists(public_path($created->application_file))) {
            @unlink(public_path($created->application_file));
        }
    }

    /**
     * Test 3: Halaman detail masyarakat tidak menampilkan tombol download auto-generated PDF dan tidak menampilkan preview template otomatis.
     */
    public function test_masyarakat_detail_does_not_show_auto_generate_button_or_preview(): void
    {
        $certRequest = CertificateRequest::create([
            'user_id' => $this->masyarakat->id,
            'request_code' => 'CERT-20261108-0099',
            'applicant_name' => 'Budi Santoso',
            'phone' => '081234567890',
            'document_type' => 'Sertifikat',
            'activity_name' => 'Pelatihan Desain Grafis',
            'activity_date' => '2026-11-20',
            'activity_place' => 'Aula Kecamatan Cikampek',
            'purpose' => 'Permohonan sertifikat',
            'status' => 'Disetujui',
        ]);

        $response = $this->actingAs($this->masyarakat)->get(route('masyarakat.sertifikat.show', $certRequest->id));
        $response->assertStatus(200);

        // Tidak boleh ada link ke download-pdf otomatis
        $response->assertDontSee('/download-pdf');
        $response->assertDontSee('Unduh Sertifikat (PDF)');
    }

    /**
     * Test 4: Admin dapat melihat daftar pengajuan tanpa tombol cetak/generate PDF otomatis.
     */
    public function test_admin_can_view_certificate_list_without_auto_generate_print_button(): void
    {
        $certRequest = CertificateRequest::create([
            'user_id' => $this->masyarakat->id,
            'request_code' => 'CERT-20261108-0098',
            'applicant_name' => 'Budi Santoso',
            'phone' => '081234567890',
            'document_type' => 'Sertifikat',
            'activity_name' => 'Pelatihan Desain Grafis',
            'activity_date' => '2026-11-20',
            'activity_place' => 'Aula Kecamatan Cikampek',
            'purpose' => 'Permohonan sertifikat',
            'status' => 'Diajukan',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.sertifikat.index'));
        $response->assertStatus(200);
        $response->assertViewIs('admin.sertifikat.index');
        $response->assertSee($certRequest->request_code);

        // Tidak ada route cetak otomatis
        $response->assertDontSee('/admin/sertifikat/' . $certRequest->id . '/cetak');
    }

    /**
     * Test 5: Admin dapat melihat detail pengajuan dan mengubah status tanpa tombol cetak PDF.
     */
    public function test_admin_can_view_detail_and_update_status(): void
    {
        $certRequest = CertificateRequest::create([
            'user_id' => $this->masyarakat->id,
            'request_code' => 'CERT-20261108-0097',
            'applicant_name' => 'Budi Santoso',
            'phone' => '081234567890',
            'document_type' => 'Sertifikat',
            'activity_name' => 'Pelatihan Desain Grafis',
            'activity_date' => '2026-11-20',
            'activity_place' => 'Aula Kecamatan Cikampek',
            'purpose' => 'Permohonan sertifikat',
            'status' => 'Diajukan',
        ]);

        $responseDetail = $this->actingAs($this->admin)->get(route('admin.sertifikat.show', $certRequest->id));
        $responseDetail->assertStatus(200);
        $responseDetail->assertDontSee('Cetak / Generate Sertifikat (PDF)');

        // Update status ke Diproses
        $responseUpdate = $this->actingAs($this->admin)->put(route('admin.sertifikat.updateStatus', $certRequest->id), [
            'status' => 'Diproses',
        ]);
        $responseUpdate->assertRedirect(route('admin.sertifikat.show', $certRequest->id));

        $this->assertDatabaseHas('certificate_requests', [
            'id' => $certRequest->id,
            'status' => 'Diproses',
        ]);
    }

    /**
     * Test 6: Admin dapat mengunggah file sertifikat final secara manual.
     */
    public function test_admin_can_upload_final_certificate_manually(): void
    {
        Storage::fake('public');

        $certRequest = CertificateRequest::create([
            'user_id' => $this->masyarakat->id,
            'request_code' => 'CERT-20261108-0096',
            'applicant_name' => 'Budi Santoso',
            'phone' => '081234567890',
            'document_type' => 'Sertifikat',
            'activity_name' => 'Pelatihan Desain Grafis',
            'activity_date' => '2026-11-20',
            'activity_place' => 'Aula Kecamatan Cikampek',
            'purpose' => 'Permohonan sertifikat',
            'status' => 'Diproses',
        ]);

        $finalFile = UploadedFile::fake()->create('sertifikat_resmi_ttd.pdf', 200, 'application/pdf');

        $response = $this->actingAs($this->admin)->post(route('admin.sertifikat.upload_final', $certRequest->id), [
            'file_sertifikat_final' => $finalFile,
        ]);

        $response->assertSessionHas('success');

        $certRequest->refresh();
        $this->assertEquals('Selesai', $certRequest->status);
        $this->assertNotNull($certRequest->file_sertifikat_final);
        Storage::disk('public')->assertExists($certRequest->file_sertifikat_final);
    }

    /**
     * Test 7: Masyarakat dapat mengunduh sertifikat final setelah diunggah admin.
     */
    public function test_masyarakat_can_download_final_certificate(): void
    {
        Storage::fake('public');

        $fakeFilePath = 'sertifikat_final/test_final.pdf';
        Storage::disk('public')->put($fakeFilePath, 'Fake PDF Content');

        $certRequest = CertificateRequest::create([
            'user_id' => $this->masyarakat->id,
            'request_code' => 'CERT-20261108-0095',
            'applicant_name' => 'Budi Santoso',
            'phone' => '081234567890',
            'document_type' => 'Sertifikat',
            'activity_name' => 'Pelatihan Desain Grafis',
            'activity_date' => '2026-11-20',
            'activity_place' => 'Aula Kecamatan Cikampek',
            'purpose' => 'Permohonan sertifikat',
            'status' => 'Selesai',
            'file_sertifikat_final' => $fakeFilePath,
        ]);

        // Download via download_final
        $responseFinal = $this->actingAs($this->masyarakat)->get(route('masyarakat.sertifikat.download_final', $certRequest->id));
        $responseFinal->assertStatus(200);

        // Download via standard download route
        $responseGeneral = $this->actingAs($this->masyarakat)->get(route('masyarakat.sertifikat.download', $certRequest->id));
        $responseGeneral->assertStatus(200);
    }

    /**
     * Test 8: Route cetak otomatis admin dan download PDF otomatis masyarakat menghasilkan 404.
     */
    public function test_removed_auto_generate_routes_return_404(): void
    {
        $certRequest = CertificateRequest::create([
            'user_id' => $this->masyarakat->id,
            'request_code' => 'CERT-20261108-0094',
            'applicant_name' => 'Budi Santoso',
            'phone' => '081234567890',
            'document_type' => 'Sertifikat',
            'activity_name' => 'Pelatihan Desain Grafis',
            'activity_date' => '2026-11-20',
            'activity_place' => 'Aula Kecamatan Cikampek',
            'purpose' => 'Permohonan sertifikat',
            'status' => 'Disetujui',
        ]);

        // Route admin cetak otomatis
        $responseAdminCetak = $this->actingAs($this->admin)->get('/admin/sertifikat/' . $certRequest->id . '/cetak');
        $responseAdminCetak->assertStatus(404);

        // Route masyarakat download pdf otomatis
        $responseMasyarakatPdf = $this->actingAs($this->masyarakat)->get('/masyarakat/sertifikat/' . $certRequest->id . '/download-pdf');
        $responseMasyarakatPdf->assertStatus(404);
    }
}
