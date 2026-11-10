<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\InventoryItem;
use App\Models\InventoryBorrowing;
use App\Models\InventoryBorrowingDetail;
use App\Mail\InventoryBorrowingApprovedMail;
use App\Mail\InventoryBorrowingRejectedMail;
use App\Mail\InventoryReturnReminderMail;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class InventoryBorrowingNotificationMailTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;
    protected User $masyarakat;
    protected InventoryItem $item;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin.test@cikampek.go.id',
        ]);

        $this->masyarakat = User::factory()->create([
            'role' => 'masyarakat',
            'email' => 'warga.inventaris@gmail.com',
            'name' => 'Budi Santoso',
            'no_hp' => '081234567890',
        ]);

        $this->item = InventoryItem::create([
            'name' => 'Sound Portable Wireless',
            'category' => 'Elektronik',
            'total_quantity' => 10,
            'available_quantity' => 10,
            'condition' => 'Baik',
            'is_active' => true,
        ]);
    }

    /**
     * Helper untuk membuat transaksi peminjaman inventaris
     */
    protected function createBorrowing(string $status = 'Menunggu Persetujuan', array $attributes = [], ?User $user = null): InventoryBorrowing
    {
        $targetUser = $user ?? $this->masyarakat;
        $today = Carbon::today('Asia/Jakarta');

        $borrowing = InventoryBorrowing::create(array_merge([
            'user_id' => $targetUser->id,
            'request_code' => 'INV-NOTIF-' . uniqid(),
            'applicant_name' => $targetUser->name,
            'phone' => '081234567890',
            'institution' => 'Karang Taruna Dusun II',
            'purpose' => 'Kegiatan Senam Bersama Warga',
            'penanggung_jawab' => 'Budi Santoso',
            'borrow_date' => $today->toDateString(),
            'borrowing_time' => '08:00:00',
            'return_date' => $today->copy()->addDay()->toDateString(),
            'return_time' => '16:00:00',
            'status' => $status,
            'rejection_reason' => null,
            'reminder_sent_at' => null,
        ], $attributes));

        InventoryBorrowingDetail::create([
            'borrowing_id' => $borrowing->id,
            'inventory_item_id' => $this->item->id,
            'quantity' => 2,
        ]);

        return $borrowing;
    }

    /**
     * Skenario 1: Pengajuan berubah menjadi Disetujui -> email persetujuan terkirim.
     */
    public function test_skenario_1_pengajuan_disetujui_mengirimkan_email_persetujuan(): void
    {
        Mail::fake();

        $borrowing = $this->createBorrowing(InventoryBorrowing::STATUS_MENUNGGU_PERSETUJUAN);

        $response = $this->actingAs($this->admin)
            ->put(route('admin.peminjaman_inventaris.updateStatus', $borrowing->id), [
                'status' => InventoryBorrowing::STATUS_DISETUJUI,
            ]);

        $response->assertRedirect(route('admin.peminjaman_inventaris.show', $borrowing->id));
        $response->assertSessionHas('success');

        $fresh = $borrowing->fresh();
        $this->assertEquals(InventoryBorrowing::STATUS_DISETUJUI, $fresh->status);

        // Verifikasi email dikirim ke email pemohon, bukan admin
        Mail::assertSent(InventoryBorrowingApprovedMail::class, function ($mail) use ($borrowing) {
            $hasRecipient = $mail->hasTo('warga.inventaris@gmail.com');
            $notAdmin = !$mail->hasTo('admin.test@cikampek.go.id');
            $correctCode = $mail->borrowing->id === $borrowing->id;

            // Render view email dan verifikasi seluruh komponen wajib
            $rendered = $mail->render();
            $containsApplicant = str_contains($rendered, $borrowing->applicant_name);
            $containsCode = str_contains($rendered, $borrowing->request_code);
            $containsItem = str_contains($rendered, 'Sound Portable Wireless');
            $containsQuantity = str_contains($rendered, '2 Unit');
            $containsBorrowDate = str_contains($rendered, 'Tanggal Peminjaman');
            $containsReturnInfo = str_contains($rendered, 'Tanggal Pengembalian');
            $containsApprovalText = str_contains($rendered, 'DISETUJUI');
            $containsGuide = str_contains($rendered, 'Petunjuk Pengambilan Barang');

            return $hasRecipient && $notAdmin && $correctCode
                && $containsApplicant && $containsCode && $containsItem
                && $containsQuantity && $containsBorrowDate && $containsReturnInfo
                && $containsApprovalText && $containsGuide;
        });

        Mail::assertNotSent(InventoryBorrowingRejectedMail::class);
    }

    /**
     * Skenario 2: Pengajuan berubah menjadi Ditolak -> email penolakan terkirim beserta alasan asli.
     */
    public function test_skenario_2_pengajuan_ditolak_mengirimkan_email_penolakan_beserta_alasan(): void
    {
        Mail::fake();

        $borrowing = $this->createBorrowing(InventoryBorrowing::STATUS_MENUNGGU_PERSETUJUAN);
        $alasanPenolakan = 'Alat sedang dalam perbaikan dan kalibrasi rutin di dinas.';

        $response = $this->actingAs($this->admin)
            ->put(route('admin.peminjaman_inventaris.updateStatus', $borrowing->id), [
                'status' => InventoryBorrowing::STATUS_DITOLAK,
                'rejection_reason' => $alasanPenolakan,
            ]);

        $response->assertRedirect(route('admin.peminjaman_inventaris.show', $borrowing->id));
        $response->assertSessionHas('success');

        $fresh = $borrowing->fresh();
        $this->assertEquals(InventoryBorrowing::STATUS_DITOLAK, $fresh->status);
        $this->assertEquals($alasanPenolakan, $fresh->rejection_reason);

        // Verifikasi email dikirim ke email pemohon, bukan admin
        Mail::assertSent(InventoryBorrowingRejectedMail::class, function ($mail) use ($borrowing, $alasanPenolakan) {
            $hasRecipient = $mail->hasTo('warga.inventaris@gmail.com');
            $notAdmin = !$mail->hasTo('admin.test@cikampek.go.id');
            $correctCode = $mail->borrowing->id === $borrowing->id;

            // Render view email dan verifikasi seluruh komponen wajib
            $rendered = $mail->render();
            $containsApplicant = str_contains($rendered, $borrowing->applicant_name);
            $containsCode = str_contains($rendered, $borrowing->request_code);
            $containsItem = str_contains($rendered, 'Sound Portable Wireless');
            $containsQuantity = str_contains($rendered, '2 Unit');
            $containsRejectionText = str_contains($rendered, 'tidak dapat disetujui');
            $containsExactReason = str_contains($rendered, $alasanPenolakan);
            $containsContactInfo = str_contains($rendered, 'Kantor Kecamatan Cikampek');

            return $hasRecipient && $notAdmin && $correctCode
                && $containsApplicant && $containsCode && $containsItem
                && $containsQuantity && $containsRejectionText
                && $containsExactReason && $containsContactInfo;
        });

        Mail::assertNotSent(InventoryBorrowingApprovedMail::class);
    }

    /**
     * Skenario 3: Status Disetujui disimpan ulang -> tidak mengirim email duplikat.
     */
    public function test_skenario_3_status_disetujui_disimpan_ulang_tidak_mengirim_email_duplikat(): void
    {
        Mail::fake();

        // Transaksi yang statusnya sudah Disetujui
        $borrowing = $this->createBorrowing(InventoryBorrowing::STATUS_DISETUJUI);

        // Admin menyimpan ulang dengan status yang sama
        $response = $this->actingAs($this->admin)
            ->put(route('admin.peminjaman_inventaris.updateStatus', $borrowing->id), [
                'status' => InventoryBorrowing::STATUS_DISETUJUI,
                'rejection_reason' => null,
            ]);

        $response->assertRedirect(route('admin.peminjaman_inventaris.show', $borrowing->id));
        $response->assertSessionHas('success');

        // Pastikan tidak ada email apapun yang dikirim
        Mail::assertNothingSent();
    }

    /**
     * Skenario 4: Status Ditolak disimpan ulang -> tidak mengirim email duplikat.
     */
    public function test_skenario_4_status_ditolak_disimpan_ulang_tidak_mengirim_email_duplikat(): void
    {
        Mail::fake();

        // Transaksi yang statusnya sudah Ditolak
        $borrowing = $this->createBorrowing(InventoryBorrowing::STATUS_DITOLAK, [
            'rejection_reason' => 'Alasan awal',
        ]);

        // Admin menyimpan ulang status yang sama
        $response = $this->actingAs($this->admin)
            ->put(route('admin.peminjaman_inventaris.updateStatus', $borrowing->id), [
                'status' => InventoryBorrowing::STATUS_DITOLAK,
                'rejection_reason' => 'Alasan awal diperbarui catatan',
            ]);

        $response->assertRedirect(route('admin.peminjaman_inventaris.show', $borrowing->id));

        // Pastikan tidak ada email duplikat yang dikirim
        Mail::assertNothingSent();
    }

    /**
     * Skenario 5: Email pemohon kosong atau tidak valid -> sistem mencatat kegagalan tanpa merusak transaksi.
     */
    public function test_skenario_5_email_pemohon_tidak_valid_mencatat_log_tanpa_merusak_transaksi(): void
    {
        Mail::fake();

        // Akun pemohon tanpa email valid
        $userTanpaEmail = User::factory()->create([
            'role' => 'masyarakat',
            'email' => 'bukan-email-valid',
        ]);

        $borrowing = $this->createBorrowing(
            InventoryBorrowing::STATUS_MENUNGGU_PERSETUJUAN,
            [],
            $userTanpaEmail
        );

        Log::shouldReceive('warning')
            ->once()
            ->withArgs(function ($message) use ($borrowing) {
                return str_contains($message, 'email pemohon kosong atau tidak valid')
                    && str_contains($message, $borrowing->request_code);
            });

        // Abaikan log lain jika ada
        Log::shouldReceive('info')->zeroOrMoreTimes();
        Log::shouldReceive('error')->zeroOrMoreTimes();

        $response = $this->actingAs($this->admin)
            ->put(route('admin.peminjaman_inventaris.updateStatus', $borrowing->id), [
                'status' => InventoryBorrowing::STATUS_DISETUJUI,
            ]);

        // Transaksi tetap sukses diperbarui di database
        $response->assertRedirect(route('admin.peminjaman_inventaris.show', $borrowing->id));
        $response->assertSessionHas('success');
        $this->assertEquals(InventoryBorrowing::STATUS_DISETUJUI, $borrowing->fresh()->status);

        // Tidak ada email yang terkirim
        Mail::assertNothingSent();
    }

    /**
     * Skenario 5B: Kegagalan SMTP saat pengiriman email tidak membatalkan update transaksi.
     */
    public function test_skenario_5b_kegagalan_smtp_tidak_membatalkan_transaksi(): void
    {
        $borrowing = $this->createBorrowing(InventoryBorrowing::STATUS_MENUNGGU_PERSETUJUAN);

        // Simulasikan kegagalan Mail::send (SMTP error)
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
                return str_contains($message, 'Gagal mengirim email persetujuan')
                    && str_contains($message, $borrowing->request_code)
                    && str_contains($message, 'SMTP Connection timeout');
            });

        // Abaikan log lain jika ada
        Log::shouldReceive('info')->zeroOrMoreTimes();
        Log::shouldReceive('warning')->zeroOrMoreTimes();

        $response = $this->actingAs($this->admin)
            ->put(route('admin.peminjaman_inventaris.updateStatus', $borrowing->id), [
                'status' => InventoryBorrowing::STATUS_DISETUJUI,
            ]);

        // Status transaksi TETAP tersimpan sebagai Disetujui
        $response->assertRedirect(route('admin.peminjaman_inventaris.show', $borrowing->id));
        $response->assertSessionHas('success');
        $this->assertEquals(InventoryBorrowing::STATUS_DISETUJUI, $borrowing->fresh()->status);
    }

    /**
     * Skenario 6: Reminder pengembalian tetap berfungsi seperti sebelumnya (tidak terganggu).
     */
    public function test_skenario_6_reminder_pengembalian_tetap_berfungsi(): void
    {
        Mail::fake();

        $today = Carbon::today('Asia/Jakarta');

        // Buat transaksi Sedang Dipinjam yang jatuh tempo hari ini
        $borrowingSedangDipinjam = $this->createBorrowing(
            InventoryBorrowing::STATUS_SEDANG_DIPINJAM,
            [
                'borrow_date' => $today->copy()->subDay()->toDateString(),
                'return_date' => $today->toDateString(),
                'return_time' => '23:59:00',
                'reminder_sent_at' => null,
            ]
        );

        // Buat transaksi Disetujui (tidak boleh dikirimi reminder pengembalian)
        $borrowingDisetujui = $this->createBorrowing(
            InventoryBorrowing::STATUS_DISETUJUI,
            [
                'return_date' => $today->toDateString(),
                'reminder_sent_at' => null,
            ]
        );

        // Jalankan command reminder
        $this->artisan('inventory:send-return-reminders', ['--all-day' => true])
            ->assertExitCode(0);

        // Reminder hanya dikirim ke transaksi Sedang Dipinjam
        Mail::assertSent(InventoryReturnReminderMail::class, function ($mail) use ($borrowingSedangDipinjam) {
            return $mail->borrowing->id === $borrowingSedangDipinjam->id;
        });

        // Transaksi Disetujui tidak dikirimi reminder pengembalian
        Mail::assertNotSent(InventoryReturnReminderMail::class, function ($mail) use ($borrowingDisetujui) {
            return $mail->borrowing->id === $borrowingDisetujui->id;
        });

        $this->assertNotNull($borrowingSedangDipinjam->fresh()->reminder_sent_at);
        $this->assertNull($borrowingDisetujui->fresh()->reminder_sent_at);
    }

    /**
     * Skenario 7: Stok inventaris tetap berjalan normal sesuai aturan.
     */
    public function test_skenario_7_stok_inventaris_berjalan_normal(): void
    {
        Mail::fake();

        $initialStock = $this->item->fresh()->available_quantity; // 10
        $borrowing = $this->createBorrowing(InventoryBorrowing::STATUS_MENUNGGU_PERSETUJUAN);

        // 1. Menyetujui: stok berkurang 2 unit (10 -> 8)
        $this->actingAs($this->admin)
            ->put(route('admin.peminjaman_inventaris.updateStatus', $borrowing->id), [
                'status' => InventoryBorrowing::STATUS_DISETUJUI,
            ]);
        $this->assertEquals($initialStock - 2, $this->item->fresh()->available_quantity);

        // 2. Transisi Disetujui -> Sedang Dipinjam: stok tetap ditahan (8)
        $this->actingAs($this->admin)
            ->put(route('admin.peminjaman_inventaris.updateStatus', $borrowing->id), [
                'status' => InventoryBorrowing::STATUS_SEDANG_DIPINJAM,
            ]);
        $this->assertEquals($initialStock - 2, $this->item->fresh()->available_quantity);

        // 3. Transisi Sedang Dipinjam -> Selesai: stok kembali dipulihkan (8 -> 10)
        $this->actingAs($this->admin)
            ->put(route('admin.peminjaman_inventaris.updateStatus', $borrowing->id), [
                'status' => InventoryBorrowing::STATUS_SELESAI,
            ]);
        $this->assertEquals($initialStock, $this->item->fresh()->available_quantity);
    }
}
