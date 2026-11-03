<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\InventoryItem;
use App\Models\InventoryBorrowing;
use App\Models\InventoryBorrowingDetail;
use App\Mail\InventoryReturnReminderMail;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class InventoryReturnReminderTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;
    protected InventoryItem $item1;
    protected InventoryItem $item2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Warga Uji Coba',
            'email' => 'warga.uji@cikampek.go.id',
            'password' => bcrypt('password123'),
            'role' => 'masyarakat',
            'no_hp' => '081234567899',
        ]);

        $this->item1 = InventoryItem::create([
            'name' => 'Proyektor Epson EB-E500',
            'category' => 'Elektronik',
            'total_quantity' => 10,
            'available_quantity' => 8,
            'condition' => 'Baik',
            'is_active' => true,
        ]);

        $this->item2 = InventoryItem::create([
            'name' => 'Layar Proyektor Portable',
            'category' => 'Perlengkapan',
            'total_quantity' => 5,
            'available_quantity' => 4,
            'condition' => 'Baik',
            'is_active' => true,
        ]);
    }

    /**
     * Helper untuk membuat transaksi peminjaman inventaris
     */
    protected function createBorrowing(array $attributes = []): InventoryBorrowing
    {
        $today = Carbon::today('Asia/Jakarta');

        $borrowing = InventoryBorrowing::create(array_merge([
            'user_id' => $this->user->id,
            'request_code' => 'INV-TEST-' . uniqid(),
            'applicant_name' => 'Warga Uji Coba',
            'phone' => '081234567899',
            'institution' => 'Karang Taruna Cikampek',
            'purpose' => 'Rapat Koordinasi Kepemudaan',
            'penanggung_jawab' => 'Warga Uji Coba',
            'borrow_date' => $today->copy()->subDays(2)->toDateString(),
            'return_date' => $today->toDateString(),
            'notes' => 'Harap konfirmasi pengembalian tepat waktu',
            'status' => 'Sedang Dipinjam',
            'reminder_sent_at' => null,
        ], $attributes));

        InventoryBorrowingDetail::create([
            'borrowing_id' => $borrowing->id,
            'inventory_item_id' => $this->item1->id,
            'quantity' => 2,
        ]);

        return $borrowing;
    }

    /**
     * TEST 1 — Barang sedang dipinjam dan tanggal pengembalian hari ini
     * Hasil: Email terkirim dan reminder_sent_at diperbarui.
     */
    public function test_1_barang_sedang_dipinjam_jatuh_tempo_hari_ini_mengirim_email(): void
    {
        Mail::fake();

        $today = Carbon::today('Asia/Jakarta');
        $borrowing = $this->createBorrowing([
            'status' => 'Sedang Dipinjam',
            'return_date' => $today->toDateString(),
            'reminder_sent_at' => null,
        ]);

        $this->artisan('inventory:send-return-reminders')
            ->assertSuccessful();

        Mail::assertSent(InventoryReturnReminderMail::class, function ($mail) {
            return $mail->hasTo($this->user->email);
        });

        $this->assertNotNull($borrowing->fresh()->reminder_sent_at);
        $this->assertEquals($today->toDateString(), $borrowing->fresh()->reminder_sent_at->toDateString());
    }

    /**
     * TEST 2 — Barang sedang dipinjam dan tanggal pengembalian kemarin (terlambat)
     * Hasil: Email terkirim.
     */
    public function test_2_barang_sedang_dipinjam_tanggal_pengembalian_kemarin_mengirim_email(): void
    {
        Mail::fake();

        $yesterday = Carbon::today('Asia/Jakarta')->subDay();
        $borrowing = $this->createBorrowing([
            'status' => 'Sedang Dipinjam',
            'return_date' => $yesterday->toDateString(),
            'reminder_sent_at' => null,
        ]);

        $this->artisan('inventory:send-return-reminders')
            ->assertSuccessful();

        Mail::assertSent(InventoryReturnReminderMail::class, function ($mail) {
            return $mail->hasTo($this->user->email);
        });

        $this->assertNotNull($borrowing->fresh()->reminder_sent_at);
    }

    /**
     * TEST 3 — Barang sedang dipinjam dan tanggal pengembalian besok (belum jatuh tempo)
     * Hasil: Email tidak terkirim.
     */
    public function test_3_barang_sedang_dipinjam_tanggal_pengembalian_besok_tidak_mengirim_email(): void
    {
        Mail::fake();

        $tomorrow = Carbon::today('Asia/Jakarta')->addDay();
        $borrowing = $this->createBorrowing([
            'status' => 'Sedang Dipinjam',
            'return_date' => $tomorrow->toDateString(),
            'reminder_sent_at' => null,
        ]);

        $this->artisan('inventory:send-return-reminders')
            ->assertSuccessful();

        Mail::assertNothingSent();
        $this->assertNull($borrowing->fresh()->reminder_sent_at);
    }

    /**
     * TEST 4 — Barang belum disetujui / belum diserahkan (Diajukan, Diproses, Disetujui)
     * Hasil: Email tidak terkirim.
     */
    public function test_4_barang_belum_diserahkan_tidak_mengirim_email(): void
    {
        Mail::fake();

        $today = Carbon::today('Asia/Jakarta');

        $b1 = $this->createBorrowing(['status' => 'Diajukan', 'return_date' => $today->toDateString()]);
        $b2 = $this->createBorrowing(['status' => 'Diproses', 'return_date' => $today->toDateString()]);
        $b3 = $this->createBorrowing(['status' => 'Disetujui', 'return_date' => $today->toDateString()]);

        $this->artisan('inventory:send-return-reminders')
            ->assertSuccessful();

        Mail::assertNothingSent();
        $this->assertNull($b1->fresh()->reminder_sent_at);
        $this->assertNull($b2->fresh()->reminder_sent_at);
        $this->assertNull($b3->fresh()->reminder_sent_at);
    }

    /**
     * TEST 5 — Barang sudah dikembalikan
     * Hasil: Email tidak terkirim.
     */
    public function test_5_barang_sudah_dikembalikan_tidak_mengirim_email(): void
    {
        Mail::fake();

        $borrowing = $this->createBorrowing([
            'status' => 'Dikembalikan',
            'return_date' => Carbon::today('Asia/Jakarta')->toDateString(),
            'reminder_sent_at' => null,
        ]);

        $this->artisan('inventory:send-return-reminders')
            ->assertSuccessful();

        Mail::assertNothingSent();
        $this->assertNull($borrowing->fresh()->reminder_sent_at);
    }

    /**
     * TEST 6 — Barang sudah selesai
     * Hasil: Email tidak terkirim.
     */
    public function test_6_barang_sudah_selesai_tidak_mengirim_email(): void
    {
        Mail::fake();

        $borrowing = $this->createBorrowing([
            'status' => 'Selesai',
            'return_date' => Carbon::today('Asia/Jakarta')->toDateString(),
            'reminder_sent_at' => null,
        ]);

        $this->artisan('inventory:send-return-reminders')
            ->assertSuccessful();

        Mail::assertNothingSent();
        $this->assertNull($borrowing->fresh()->reminder_sent_at);
    }

    /**
     * TEST 7 — Barang ditolak atau dibatalkan
     * Hasil: Email tidak terkirim.
     */
    public function test_7_barang_ditolak_atau_dibatalkan_tidak_mengirim_email(): void
    {
        Mail::fake();

        $today = Carbon::today('Asia/Jakarta');
        $b1 = $this->createBorrowing(['status' => 'Ditolak', 'return_date' => $today->toDateString()]);
        $b2 = $this->createBorrowing(['status' => 'Dibatalkan', 'return_date' => $today->toDateString()]);

        $this->artisan('inventory:send-return-reminders')
            ->assertSuccessful();

        Mail::assertNothingSent();
        $this->assertNull($b1->fresh()->reminder_sent_at);
        $this->assertNull($b2->fresh()->reminder_sent_at);
    }

    /**
     * TEST 8 — Email sudah dikirim hari ini: tidak dikirim ulang pada hari yang sama
     * Hasil: Mencegah duplikasi email pada hari yang sama.
     */
    public function test_8_email_sudah_dikirim_hari_ini_tidak_dikirim_ulang(): void
    {
        Mail::fake();

        $today = Carbon::today('Asia/Jakarta');
        $borrowing = $this->createBorrowing([
            'status' => 'Sedang Dipinjam',
            'return_date' => $today->toDateString(),
            'reminder_sent_at' => Carbon::now('Asia/Jakarta')->subHours(1),
        ]);

        $this->artisan('inventory:send-return-reminders')
            ->assertSuccessful();

        Mail::assertNothingSent();
    }

    /**
     * TEST 9 — Email sudah dikirim kemarin dan barang belum dikembalikan: email boleh dikirim kembali hari ini
     * Hasil: Mengirim reminder susulan hari ini dan memperbarui reminder_sent_at.
     */
    public function test_9_email_sudah_dikirim_kemarin_boleh_dikirim_kembali_hari_ini(): void
    {
        Mail::fake();

        $today = Carbon::today('Asia/Jakarta');
        $yesterday = $today->copy()->subDay();

        $borrowing = $this->createBorrowing([
            'status' => 'Sedang Dipinjam',
            'return_date' => $yesterday->toDateString(),
            'reminder_sent_at' => $yesterday->copy()->setHour(8)->setMinute(0),
        ]);

        $this->artisan('inventory:send-return-reminders')
            ->assertSuccessful();

        Mail::assertSent(InventoryReturnReminderMail::class, function ($mail) {
            return $mail->hasTo($this->user->email);
        });

        $this->assertEquals($today->toDateString(), $borrowing->fresh()->reminder_sent_at->toDateString());
    }

    /**
     * TEST 10 — Pengiriman gagal: reminder_sent_at tidak diperbarui
     * Hasil: Error tercatat dan timestamp tidak berubah.
     */
    public function test_10_pengiriman_gagal_reminder_sent_at_tidak_diperbarui(): void
    {
        $borrowing = $this->createBorrowing([
            'status' => 'Sedang Dipinjam',
            'return_date' => Carbon::today('Asia/Jakarta')->toDateString(),
            'reminder_sent_at' => null,
        ]);

        Mail::shouldReceive('to')
            ->once()
            ->andReturnSelf();
        Mail::shouldReceive('send')
            ->once()
            ->andThrow(new \Exception('Connection to smtp.gmail.com:587 failed: Connection timed out'));

        $this->artisan('inventory:send-return-reminders')
            ->assertFailed();

        $this->assertNull($borrowing->fresh()->reminder_sent_at);
    }

    /**
     * TEST 11 — Email kosong atau tidak valid: transaksi dilewati tanpa membuat command crash
     * Hasil: Command tetap selesai dengan sukses tanpa crash fatal.
     */
    public function test_11_email_kosong_atau_tidak_valid_dilewati_tanpa_crash(): void
    {
        Mail::fake();

        $invalidUser = User::create([
            'name' => 'Warga Tanpa Email Valid',
            'email' => 'format-salah-tanpa-domain',
            'password' => bcrypt('password'),
            'role' => 'masyarakat',
        ]);

        $borrowing = $this->createBorrowing([
            'user_id' => $invalidUser->id,
            'status' => 'Sedang Dipinjam',
            'return_date' => Carbon::today('Asia/Jakarta')->toDateString(),
            'reminder_sent_at' => null,
        ]);

        $this->artisan('inventory:send-return-reminders')
            ->assertSuccessful();

        Mail::assertNothingSent();
        $this->assertNull($borrowing->fresh()->reminder_sent_at);
    }

    /**
     * TEST 12 — Satu transaksi memiliki beberapa barang: seluruh barang dan jumlahnya muncul di email
     * Hasil: Blade template merender seluruh barang, jumlah, serta keterangan keterlambatan/jatuh tempo.
     */
    public function test_12_beberapa_barang_dan_keterangan_muncul_di_email(): void
    {
        $today = Carbon::today('Asia/Jakarta');
        $borrowing = $this->createBorrowing([
            'status' => 'Sedang Dipinjam',
            'return_date' => $today->copy()->subDays(2)->toDateString(), // Terlambat 2 hari
        ]);

        InventoryBorrowingDetail::create([
            'borrowing_id' => $borrowing->id,
            'inventory_item_id' => $this->item2->id,
            'quantity' => 3,
        ]);

        $borrowing->load(['user', 'details.item']);

        $mailable = new InventoryReturnReminderMail($borrowing, $today);
        $rendered = $mailable->render();

        $this->assertStringContainsString('Pengingat Pengembalian Barang Inventaris', $rendered);
        $this->assertStringContainsString('Proyektor Epson EB-E500', $rendered);
        $this->assertStringContainsString('2 Unit', $rendered);
        $this->assertStringContainsString('Layar Proyektor Portable', $rendered);
        $this->assertStringContainsString('3 Unit', $rendered);
        $this->assertStringContainsString($borrowing->request_code, $rendered);
        // Memastikan informasi keterlambatan muncul saat melewati batas tanggal pengembalian
        $this->assertStringContainsString('telah melewati batas tanggal pengembalian', $rendered);
        $this->assertStringContainsString('Terlambat 2 Hari', $rendered);
    }

    /**
     * TEST 13 — Fitur lama SIPAF tetap berfungsi
     * Hasil: Halaman admin, masyarakat, beranda, dan panduan tetap dapat diakses normal.
     */
    public function test_13_fitur_lama_sipaf_tetap_berfungsi(): void
    {
        $admin = User::where('role', 'admin')->first() ?? User::create([
            'name' => 'Admin Test',
            'email' => 'admin.test@cikampek.go.id',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $borrowing = $this->createBorrowing([
            'status' => 'Sedang Dipinjam',
            'reminder_sent_at' => Carbon::now('Asia/Jakarta'),
        ]);

        // Admin melihat detail inventaris
        $response = $this->actingAs($admin)->get(route('admin.peminjaman_inventaris.show', $borrowing->id));
        $response->assertStatus(200);
        $response->assertSee('Status Reminder Email');
        $response->assertSee('Berhasil dikirim');
        $response->assertSee('Waktu Pengiriman Terakhir');

        // Admin melihat daftar pengajuan inventaris
        $this->actingAs($admin)->get(route('admin.peminjaman_inventaris.index'))->assertStatus(200);

        // Masyarakat melihat riwayat inventaris
        $this->actingAs($this->user)->get(route('masyarakat.peminjaman_inventaris.index'))->assertStatus(200);

        // Halaman umum tetap berfungsi
        $this->get('/')->assertStatus(200);
        $this->get('/panduan')->assertStatus(200);
    }
}
