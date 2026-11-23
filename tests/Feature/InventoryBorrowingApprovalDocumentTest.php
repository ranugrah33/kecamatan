<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\InventoryItem;
use App\Models\InventoryBorrowing;
use App\Models\InventoryBorrowingDetail;
use App\Models\InventoryBorrowingDocument;
use App\Mail\InventoryBorrowingSuratFinalMail;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class InventoryBorrowingApprovalDocumentTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;
    protected User $masyarakat;
    protected User $otherMasyarakat;
    protected InventoryItem $item;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin.inv.doc@cikampek.go.id',
        ]);

        $this->masyarakat = User::factory()->create([
            'role' => 'masyarakat',
            'email' => 'warga.inv.doc@gmail.com',
            'name' => 'Warga Dokumen Test',
            'no_hp' => '081234567899',
        ]);

        $this->otherMasyarakat = User::factory()->create([
            'role' => 'masyarakat',
            'email' => 'warga.lain.doc@gmail.com',
            'name' => 'Warga Lain',
            'no_hp' => '081234567888',
        ]);

        $this->item = InventoryItem::create([
            'name' => 'Proyektor EPSON HD',
            'category' => 'Elektronik',
            'total_quantity' => 5,
            'available_quantity' => 5,
            'condition' => 'Baik',
            'is_active' => true,
        ]);
    }

    protected function createBorrowing(string $status = InventoryBorrowing::STATUS_DISETUJUI, ?User $user = null): InventoryBorrowing
    {
        $targetUser = $user ?? $this->masyarakat;
        $today = Carbon::today('Asia/Jakarta');

        $borrowing = InventoryBorrowing::create([
            'user_id' => $targetUser->id,
            'request_code' => 'INV-DOC-' . uniqid(),
            'applicant_name' => $targetUser->name,
            'phone' => '081234567899',
            'institution' => 'Forum RT 05 Cikampek',
            'purpose' => 'Rapat Sosialisasi Warga',
            'penanggung_jawab' => 'Warga Dokumen Test',
            'borrow_date' => $today->toDateString(),
            'borrowing_time' => '09:00:00',
            'return_date' => $today->copy()->addDay()->toDateString(),
            'return_time' => '16:00:00',
            'notes' => 'Harap konfirmasi ketersediaan kabel HDMI',
            'status' => $status,
        ]);

        InventoryBorrowingDetail::create([
            'borrowing_id' => $borrowing->id,
            'inventory_item_id' => $this->item->id,
            'quantity' => 1,
        ]);

        return $borrowing;
    }

    public function test_admin_can_generate_and_stream_draft_surat_persetujuan_for_approved_borrowing(): void
    {
        $borrowing = $this->createBorrowing(InventoryBorrowing::STATUS_DISETUJUI);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.peminjaman_inventaris.cetakDraftSurat', $borrowing->id));

        $response->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
    }

    public function test_draft_surat_cannot_be_generated_for_unapproved_borrowing(): void
    {
        $borrowing = $this->createBorrowing(InventoryBorrowing::STATUS_MENUNGGU_PERSETUJUAN);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.peminjaman_inventaris.cetakDraftSurat', $borrowing->id));

        $response->assertRedirect();
        $response->assertSessionHasErrors();
    }

    public function test_masyarakat_cannot_access_admin_draft_surat_route(): void
    {
        $borrowing = $this->createBorrowing(InventoryBorrowing::STATUS_DISETUJUI);

        $response = $this->actingAs($this->masyarakat)
            ->get(route('admin.peminjaman_inventaris.cetakDraftSurat', $borrowing->id));

        $response->assertRedirect('/masyarakat/dashboard');
    }

    public function test_admin_can_upload_surat_final_and_triggers_email_notification(): void
    {
        Storage::fake('local');
        Mail::fake();

        $borrowing = $this->createBorrowing(InventoryBorrowing::STATUS_DISETUJUI);

        $file = UploadedFile::fake()->create('surat_final_resmi.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->admin)
            ->post(route('admin.peminjaman_inventaris.uploadSuratFinal', $borrowing->id), [
                'file_surat_final' => $file,
                'nomor_surat' => '000.1.2/045/Kec-Ckp/2026',
            ]);

        $response->assertRedirect(route('admin.peminjaman_inventaris.show', $borrowing->id));
        $response->assertSessionHas('success');

        // Status transaksi lama tetap Disetujui (tidak berubah jadi status aneh)
        $borrowing->refresh();
        $this->assertEquals(InventoryBorrowing::STATUS_DISETUJUI, $borrowing->status);

        // Dokumen tercatat di tabel inventory_borrowing_documents
        $this->assertDatabaseHas('inventory_borrowing_documents', [
            'inventory_borrowing_id' => $borrowing->id,
            'document_type' => 'final',
            'document_status' => 'Surat Final Tersedia',
            'is_active' => 1,
            'version' => 1,
            'nomor_surat' => '000.1.2/045/Kec-Ckp/2026',
            'uploaded_by' => $this->admin->id,
        ]);

        $activeDoc = $borrowing->activeFinalDocument;
        $this->assertNotNull($activeDoc);
        $this->assertTrue(Storage::disk('local')->exists($activeDoc->file_path));

        // Email surat final terkirim ke pemohon
        Mail::assertSent(InventoryBorrowingSuratFinalMail::class, function ($mail) use ($borrowing) {
            $hasRecipient = $mail->hasTo($borrowing->user->email) &&
                   $mail->borrowing->id === $borrowing->id;
            $correctSubject = $mail->envelope()->subject === 'Surat Persetujuan Peminjaman Inventaris Telah Terbit — SIPAF Kecamatan Cikampek';

            $rendered = $mail->render();
            $containsLocation = str_contains($rendered, 'Kantor Kecamatan Cikampek');
            $containsProof = str_contains($rendered, 'surat persetujuan');
            $containsSteps = str_contains($rendered, 'LANGKAH PENGAMBILAN BARANG');

            return $hasRecipient && $correctSubject && $containsLocation && $containsProof && $containsSteps;
        });

        // Kolom notified_at tercatat
        $this->assertNotNull($activeDoc->notified_at);
    }

    public function test_upload_surat_final_rejected_if_borrowing_is_not_approved(): void
    {
        Storage::fake('local');
        Mail::fake();

        $borrowing = $this->createBorrowing(InventoryBorrowing::STATUS_MENUNGGU_PERSETUJUAN);
        $file = UploadedFile::fake()->create('surat_final.pdf', 300, 'application/pdf');

        $response = $this->actingAs($this->admin)
            ->post(route('admin.peminjaman_inventaris.uploadSuratFinal', $borrowing->id), [
                'file_surat_final' => $file,
            ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors();

        // Tidak ada dokumen yang disimpan
        $this->assertDatabaseMissing('inventory_borrowing_documents', [
            'inventory_borrowing_id' => $borrowing->id,
        ]);

        Mail::assertNothingSent();
    }

    public function test_upload_surat_final_validates_pdf_mimes(): void
    {
        Storage::fake('local');

        $borrowing = $this->createBorrowing(InventoryBorrowing::STATUS_DISETUJUI);
        $file = UploadedFile::fake()->create('dokumen_jahat.exe', 300);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.peminjaman_inventaris.uploadSuratFinal', $borrowing->id), [
                'file_surat_final' => $file,
            ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['file_surat_final']);
    }

    public function test_admin_can_replace_surat_final_and_preserves_old_file_as_history(): void
    {
        Storage::fake('local');
        Mail::fake();

        $borrowing = $this->createBorrowing(InventoryBorrowing::STATUS_DISETUJUI);

        // Upload Versi 1
        $fileV1 = UploadedFile::fake()->create('surat_final_v1.pdf', 400, 'application/pdf');
        $this->actingAs($this->admin)
            ->post(route('admin.peminjaman_inventaris.uploadSuratFinal', $borrowing->id), [
                'file_surat_final' => $fileV1,
                'nomor_surat' => '000.1.2/010/Kec-Ckp/2026',
            ]);

        $docV1 = $borrowing->documents()->where('version', 1)->first();
        $this->assertNotNull($docV1);
        $pathV1 = $docV1->file_path;

        // Upload Penggantian Versi 2
        $fileV2 = UploadedFile::fake()->create('surat_final_v2.pdf', 450, 'application/pdf');
        $this->actingAs($this->admin)
            ->post(route('admin.peminjaman_inventaris.uploadSuratFinal', $borrowing->id), [
                'file_surat_final' => $fileV2,
                'nomor_surat' => '000.1.2/010-REV/Kec-Ckp/2026',
                'kirim_notifikasi_email' => 0, // Tidak spam email saat update
            ]);

        $borrowing->refresh();
        $docV1->refresh();

        // File lama tidak dihapus
        $this->assertTrue(Storage::disk('local')->exists($pathV1));
        // Status file lama menjadi replaced dan is_active false
        $this->assertFalse($docV1->is_active);
        $this->assertEquals(InventoryBorrowingDocument::STATUS_REPLACED, $docV1->document_status);

        // Versi 2 aktif
        $activeDoc = $borrowing->activeFinalDocument;
        $this->assertEquals(2, $activeDoc->version);
        $this->assertTrue($activeDoc->is_active);
        $this->assertEquals('000.1.2/010-REV/Kec-Ckp/2026', $activeDoc->nomor_surat);
        $this->assertTrue(Storage::disk('local')->exists($activeDoc->file_path));

        // Total 2 dokumen arsip di database
        $this->assertEquals(2, $borrowing->documents()->count());
    }

    public function test_masyarakat_can_download_surat_final_for_own_borrowing(): void
    {
        Storage::fake('local');

        $borrowing = $this->createBorrowing(InventoryBorrowing::STATUS_DISETUJUI);

        $fakeContent = '%PDF-1.4 Fake Test Final Letter Content';
        $path = 'private/inventory_documents/final/test_surat.pdf';
        Storage::disk('local')->put($path, $fakeContent);

        InventoryBorrowingDocument::create([
            'inventory_borrowing_id' => $borrowing->id,
            'document_type' => 'final',
            'file_path' => $path,
            'file_name' => 'Surat_Persetujuan_Resmi.pdf',
            'document_status' => 'Surat Final Tersedia',
            'is_active' => true,
            'version' => 1,
            'uploaded_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->masyarakat)
            ->get(route('masyarakat.peminjaman_inventaris.downloadSurat', $borrowing->id));

        $response->assertStatus(200);
        $this->assertStringContainsString('attachment', $response->headers->get('Content-Disposition'));
    }

    public function test_masyarakat_cannot_download_surat_final_if_not_available(): void
    {
        $borrowing = $this->createBorrowing(InventoryBorrowing::STATUS_DISETUJUI);

        $response = $this->actingAs($this->masyarakat)
            ->get(route('masyarakat.peminjaman_inventaris.downloadSurat', $borrowing->id));

        $response->assertRedirect();
        $response->assertSessionHasErrors();
    }

    public function test_masyarakat_cannot_download_other_users_surat_final(): void
    {
        $borrowing = $this->createBorrowing(InventoryBorrowing::STATUS_DISETUJUI, $this->masyarakat);

        $response = $this->actingAs($this->otherMasyarakat)
            ->get(route('masyarakat.peminjaman_inventaris.downloadSurat', $borrowing->id));

        $response->assertStatus(404);
    }

    public function test_ui_shows_correct_document_status_for_admin_and_masyarakat(): void
    {
        $borrowing = $this->createBorrowing(InventoryBorrowing::STATUS_DISETUJUI);

        // Sebelum upload surat final
        $adminView = $this->actingAs($this->admin)
            ->get(route('admin.peminjaman_inventaris.show', $borrowing->id));
        $adminView->assertStatus(200);
        $adminView->assertSee('DOKUMEN PERSETUJUAN PEMINJAMAN');
        $adminView->assertSee('Unduh Draft (PDF)');
        $adminView->assertSee('Unggah Surat Persetujuan Final');

        $masyarakatView = $this->actingAs($this->masyarakat)
            ->get(route('masyarakat.peminjaman_inventaris.show', $borrowing->id));
        $masyarakatView->assertStatus(200);
        $masyarakatView->assertSee('Surat persetujuan sedang diproses oleh pihak Kecamatan.');
        $masyarakatView->assertDontSee('Download Surat Persetujuan');

        // Setelah upload surat final
        InventoryBorrowingDocument::create([
            'inventory_borrowing_id' => $borrowing->id,
            'document_type' => 'final',
            'file_path' => 'dummy/path.pdf',
            'file_name' => 'surat.pdf',
            'document_status' => 'Surat Final Tersedia',
            'is_active' => true,
            'version' => 1,
            'nomor_surat' => '000.1.2/999/Kec-Ckp/2026',
            'uploaded_by' => $this->admin->id,
        ]);

        $masyarakatViewAfter = $this->actingAs($this->masyarakat)
            ->get(route('masyarakat.peminjaman_inventaris.show', $borrowing->id));
        $masyarakatViewAfter->assertStatus(200);
        $masyarakatViewAfter->assertSee('Surat persetujuan telah tersedia.');
        $masyarakatViewAfter->assertSee('Download Surat Persetujuan');
    }

    public function test_restrict_on_delete_prevents_accidental_deletion_of_borrowing_with_documents(): void
    {
        $borrowing = $this->createBorrowing(InventoryBorrowing::STATUS_DISETUJUI);

        InventoryBorrowingDocument::create([
            'inventory_borrowing_id' => $borrowing->id,
            'document_type' => 'final',
            'file_path' => 'private/inventory_documents/final/test.pdf',
            'file_name' => 'test.pdf',
            'document_status' => 'Surat Final Tersedia',
            'is_active' => true,
            'version' => 1,
            'uploaded_by' => $this->admin->id,
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        $borrowing->delete();
    }

    public function test_existing_approval_email_still_works_when_admin_approves(): void
    {
        Mail::fake();

        $borrowing = $this->createBorrowing(InventoryBorrowing::STATUS_MENUNGGU_PERSETUJUAN);

        $response = $this->actingAs($this->admin)
            ->put(route('admin.peminjaman_inventaris.updateStatus', $borrowing->id), [
                'status' => InventoryBorrowing::STATUS_DISETUJUI,
            ]);

        $response->assertRedirect(route('admin.peminjaman_inventaris.show', $borrowing->id));
        $borrowing->refresh();
        $this->assertEquals(InventoryBorrowing::STATUS_DISETUJUI, $borrowing->status);

        // KEBIJAKAN BARU: Email persetujuan lama TIDAK dikirim saat status berubah menjadi Disetujui
        Mail::assertNotSent(\App\Mail\InventoryBorrowingApprovedMail::class);

        // Email surat final juga belum dikirim karena berkas belum diunggah
        Mail::assertNotSent(InventoryBorrowingSuratFinalMail::class);

        // Stok tetap ditahan sesuai logika lama (5 -> 4)
        $detail = $borrowing->details()->first();
        $this->assertEquals(4, $detail->item->fresh()->available_quantity);

        // Draft surat tetap dapat dibuat dan di-stream
        $draftResponse = $this->actingAs($this->admin)
            ->get(route('admin.peminjaman_inventaris.cetakDraftSurat', $borrowing->id));
        $draftResponse->assertStatus(200);
    }

    public function test_failed_upload_does_not_send_email(): void
    {
        Storage::fake('local');
        Mail::fake();

        $borrowing = $this->createBorrowing(InventoryBorrowing::STATUS_DISETUJUI);

        // File non-PDF (validasi gagal)
        $invalidFile = UploadedFile::fake()->create('dokumen_rusak.txt', 200, 'text/plain');

        $response = $this->actingAs($this->admin)
            ->post(route('admin.peminjaman_inventaris.uploadSuratFinal', $borrowing->id), [
                'file_surat_final' => $invalidFile,
            ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['file_surat_final']);

        // Tidak ada email yang terkirim jika upload gagal
        Mail::assertNothingSent();
    }

    public function test_upload_surat_final_smtp_failure_preserves_stored_document(): void
    {
        Storage::fake('local');

        $borrowing = $this->createBorrowing(InventoryBorrowing::STATUS_DISETUJUI);
        $file = UploadedFile::fake()->create('surat_final_smtp_fail.pdf', 400, 'application/pdf');

        // Simulasikan kegagalan SMTP saat pengiriman email surat final
        Mail::shouldReceive('to')
            ->once()
            ->with($this->masyarakat->email)
            ->andReturnSelf();

        Mail::shouldReceive('send')
            ->once()
            ->andThrow(new \Exception('SMTP Connection timeout to smtp.gmail.com:587'));

        Log::shouldReceive('error')
            ->once()
            ->withArgs(function ($message) use ($borrowing) {
                return str_contains($message, 'Gagal mengirim email surat final inventaris')
                    && str_contains($message, $borrowing->request_code);
            });

        Log::shouldReceive('info')->zeroOrMoreTimes();
        Log::shouldReceive('warning')->zeroOrMoreTimes();

        $response = $this->actingAs($this->admin)
            ->post(route('admin.peminjaman_inventaris.uploadSuratFinal', $borrowing->id), [
                'file_surat_final' => $file,
                'nomor_surat' => '000.1.2/088/Kec-Ckp/2026',
            ]);

        $response->assertRedirect(route('admin.peminjaman_inventaris.show', $borrowing->id));
        $response->assertSessionHas('success');

        // Verifikasi dokumen fisik dan database TETAP tersimpan meskipun SMTP gagal
        $activeDoc = $borrowing->activeFinalDocument()->first();
        $this->assertNotNull($activeDoc);
        $this->assertTrue(Storage::disk('local')->exists($activeDoc->file_path));
        $this->assertEquals(1, $activeDoc->version);
        $this->assertEquals('Surat Final Tersedia', $activeDoc->document_status);
        $this->assertNull($activeDoc->notified_at); // Notified_at tetap null karena email gagal terkirim
    }
}
