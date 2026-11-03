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

class InventoryBorrowingDateTimeTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;
    protected User $admin;
    protected InventoryItem $item;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Pemohon Uji Jam',
            'email' => 'pemohon.jam@cikampek.go.id',
            'password' => bcrypt('password123'),
            'role' => 'masyarakat',
            'no_hp' => '081299998888',
        ]);

        $this->admin = User::where('role', 'admin')->first() ?? User::create([
            'name' => 'Admin Penguji',
            'email' => 'admin.uji@cikampek.go.id',
            'password' => bcrypt('password123'),
            'role' => 'admin',
        ]);

        $this->item = InventoryItem::create([
            'name' => 'Kamera DSLR Dokumentasi',
            'category' => 'Elektronik',
            'total_quantity' => 2,
            'available_quantity' => 2,
            'condition' => 'Baik',
            'is_active' => true,
        ]);
    }

    /**
     * 1. Pengajuan dengan tanggal dan jam lengkap berhasil.
     */
    public function test_1_pengajuan_dengan_tanggal_dan_jam_lengkap_berhasil(): void
    {
        $today = Carbon::today('Asia/Jakarta')->format('Y-m-d');

        $payload = [
            'applicant_name' => 'Pemohon Uji Jam',
            'phone' => '081299998888',
            'purpose' => 'Liputan Kegiatan Kecamatan',
            'penanggung_jawab' => 'Pemohon Uji Jam',
            'borrow_date' => $today,
            'borrowing_time' => '09:00',
            'return_date' => $today,
            'return_time' => '15:00',
            'items' => [
                ['id' => $this->item->id, 'quantity' => 1],
            ],
        ];

        $response = $this->actingAs($this->user)
            ->post(route('masyarakat.peminjaman_inventaris.store'), $payload);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('inventory_borrowings', [
            'applicant_name' => 'Pemohon Uji Jam',
            'borrow_date' => $today,
            'borrowing_time' => '09:00:00',
            'return_date' => $today,
            'return_time' => '15:00:00',
        ]);
    }

    /**
     * 2. Pengajuan tanpa jam peminjaman ditolak.
     */
    public function test_2_pengajuan_tanpa_jam_peminjaman_ditolak(): void
    {
        $today = Carbon::today('Asia/Jakarta')->format('Y-m-d');

        $payload = [
            'applicant_name' => 'Pemohon Uji Jam',
            'phone' => '081299998888',
            'purpose' => 'Liputan Kegiatan',
            'penanggung_jawab' => 'Pemohon Uji Jam',
            'borrow_date' => $today,
            'borrowing_time' => '', // Kosong
            'return_date' => $today,
            'return_time' => '15:00',
            'items' => [
                ['id' => $this->item->id, 'quantity' => 1],
            ],
        ];

        $response = $this->actingAs($this->user)
            ->post(route('masyarakat.peminjaman_inventaris.store'), $payload);

        $response->assertSessionHasErrors('borrowing_time');
    }

    /**
     * 3. Pengajuan tanpa jam pengembalian ditolak.
     */
    public function test_3_pengajuan_tanpa_jam_pengembalian_ditolak(): void
    {
        $today = Carbon::today('Asia/Jakarta')->format('Y-m-d');

        $payload = [
            'applicant_name' => 'Pemohon Uji Jam',
            'phone' => '081299998888',
            'purpose' => 'Liputan Kegiatan',
            'penanggung_jawab' => 'Pemohon Uji Jam',
            'borrow_date' => $today,
            'borrowing_time' => '09:00',
            'return_date' => $today,
            'return_time' => '', // Kosong
            'items' => [
                ['id' => $this->item->id, 'quantity' => 1],
            ],
        ];

        $response = $this->actingAs($this->user)
            ->post(route('masyarakat.peminjaman_inventaris.store'), $payload);

        $response->assertSessionHasErrors('return_time');
    }

    /**
     * 4. Jam pengembalian lebih awal dari jam peminjaman pada tanggal yang sama ditolak.
     */
    public function test_4_jam_pengembalian_lebih_awal_dari_jam_peminjaman_ditolak(): void
    {
        $today = Carbon::today('Asia/Jakarta')->format('Y-m-d');

        $payload = [
            'applicant_name' => 'Pemohon Uji Jam',
            'phone' => '081299998888',
            'purpose' => 'Liputan Kegiatan',
            'penanggung_jawab' => 'Pemohon Uji Jam',
            'borrow_date' => $today,
            'borrowing_time' => '14:00',
            'return_date' => $today,
            'return_time' => '10:00', // Lebih awal dari 14:00 di hari yang sama
            'items' => [
                ['id' => $this->item->id, 'quantity' => 1],
            ],
        ];

        $response = $this->actingAs($this->user)
            ->post(route('masyarakat.peminjaman_inventaris.store'), $payload);

        $response->assertSessionHasErrors('return_time');
    }

    /**
     * 5. Pengembalian pada tanggal yang sama dengan jam lebih besar berhasil.
     */
    public function test_5_pengembalian_tanggal_sama_dengan_jam_lebih_besar_berhasil(): void
    {
        $today = Carbon::today('Asia/Jakarta')->format('Y-m-d');

        $payload = [
            'applicant_name' => 'Pemohon Uji Jam',
            'phone' => '081299998888',
            'purpose' => 'Rapat Siang Hari',
            'penanggung_jawab' => 'Pemohon Uji Jam',
            'borrow_date' => $today,
            'borrowing_time' => '08:00',
            'return_date' => $today,
            'return_time' => '16:00', // Lebih besar pada hari yang sama
            'items' => [
                ['id' => $this->item->id, 'quantity' => 1],
            ],
        ];

        $response = $this->actingAs($this->user)
            ->post(route('masyarakat.peminjaman_inventaris.store'), $payload);

        $response->assertSessionHasNoErrors();
    }

    /**
     * 6. Pengembalian pada tanggal berbeda berhasil.
     */
    public function test_6_pengembalian_tanggal_berbeda_berhasil(): void
    {
        $today = Carbon::today('Asia/Jakarta')->format('Y-m-d');
        $nextDay = Carbon::today('Asia/Jakarta')->addDays(2)->format('Y-m-d');

        $payload = [
            'applicant_name' => 'Pemohon Uji Jam',
            'phone' => '081299998888',
            'purpose' => 'Kegiatan Multi Hari',
            'penanggung_jawab' => 'Pemohon Uji Jam',
            'borrow_date' => $today,
            'borrowing_time' => '10:00',
            'return_date' => $nextDay,
            'return_time' => '09:00',
            'items' => [
                ['id' => $this->item->id, 'quantity' => 1],
            ],
        ];

        $response = $this->actingAs($this->user)
            ->post(route('masyarakat.peminjaman_inventaris.store'), $payload);

        $response->assertSessionHasNoErrors();
    }

    /**
     * 7. Data lama yang tidak memiliki jam tetap dapat ditampilkan tanpa error.
     */
    public function test_7_data_lama_tanpa_jam_tetap_dapat_ditampilkan(): void
    {
        $oldBorrowing = InventoryBorrowing::create([
            'user_id' => $this->user->id,
            'request_code' => 'INV-OLD-' . uniqid(),
            'applicant_name' => 'Warga Data Lama',
            'phone' => '081200001111',
            'purpose' => 'Peminjaman Lama',
            'penanggung_jawab' => 'Warga Data Lama',
            'borrow_date' => '2026-10-01',
            'borrowing_time' => null, // Data lama NULL
            'return_date' => '2026-10-03',
            'return_time' => null, // Data lama NULL
            'status' => 'Selesai',
        ]);

        // Halaman detail masyarakat
        $responseMasy = $this->actingAs($this->user)
            ->get(route('masyarakat.peminjaman_inventaris.show', $oldBorrowing->id));
        $responseMasy->assertStatus(200);

        // Halaman detail admin
        $responseAdmin = $this->actingAs($this->admin)
            ->get(route('admin.peminjaman_inventaris.show', $oldBorrowing->id));
        $responseAdmin->assertStatus(200);
    }

    /**
     * 8. Detail peminjaman menampilkan tanggal dan jam dengan benar.
     */
    public function test_8_detail_peminjaman_menampilkan_tanggal_dan_jam_dengan_benar(): void
    {
        $borrowing = InventoryBorrowing::create([
            'user_id' => $this->user->id,
            'request_code' => 'INV-TIME-' . uniqid(),
            'applicant_name' => 'Warga Dengan Jam',
            'phone' => '081299998888',
            'purpose' => 'Uji Tampil Waktu',
            'penanggung_jawab' => 'Warga Dengan Jam',
            'borrow_date' => '2026-11-05',
            'borrowing_time' => '08:00:00',
            'return_date' => '2026-11-05',
            'return_time' => '16:00:00',
            'status' => 'Sedang Dipinjam',
        ]);

        // Detail masyarakat
        $responseMasy = $this->actingAs($this->user)
            ->get(route('masyarakat.peminjaman_inventaris.show', $borrowing->id));
        $responseMasy->assertStatus(200);
        $responseMasy->assertSee('08:00 WIB');
        $responseMasy->assertSee('16:00 WIB');

        // Detail admin
        $responseAdmin = $this->actingAs($this->admin)
            ->get(route('admin.peminjaman_inventaris.show', $borrowing->id));
        $responseAdmin->assertStatus(200);
        $responseAdmin->assertSee('08:00 WIB');
        $responseAdmin->assertSee('16:00 WIB');
    }

    /**
     * 9. Email reminder menampilkan tanggal dan jam batas pengembalian.
     */
    public function test_9_email_reminder_menampilkan_tanggal_dan_jam_pengembalian(): void
    {
        $today = Carbon::today('Asia/Jakarta');

        $borrowing = InventoryBorrowing::create([
            'user_id' => $this->user->id,
            'request_code' => 'INV-MAIL-' . uniqid(),
            'applicant_name' => 'Warga Penerima Email',
            'phone' => '081299998888',
            'purpose' => 'Uji Template Email',
            'penanggung_jawab' => 'Warga Penerima Email',
            'borrow_date' => $today->toDateString(),
            'borrowing_time' => '08:00:00',
            'return_date' => $today->toDateString(),
            'return_time' => '16:00:00',
            'status' => 'Sedang Dipinjam',
        ]);

        InventoryBorrowingDetail::create([
            'borrowing_id' => $borrowing->id,
            'inventory_item_id' => $this->item->id,
            'quantity' => 1,
        ]);

        $borrowing->load(['user', 'details.item']);

        $mailable = new InventoryReturnReminderMail($borrowing, $today);
        $rendered = $mailable->render();

        $this->assertStringContainsString('Batas Waktu Pengembalian', $rendered);
        $this->assertStringContainsString('16:00 WIB', $rendered);
        $this->assertStringContainsString('08:00 WIB', $rendered);
    }

    /**
     * 10. Reminder opsi C tetap berjalan seperti sebelumnya (jadwal harian 08:00).
     */
    public function test_10_reminder_opsi_c_tetap_berjalan_seperti_sebelumnya(): void
    {
        Mail::fake();

        $today = Carbon::today('Asia/Jakarta');

        $borrowing = InventoryBorrowing::create([
            'user_id' => $this->user->id,
            'request_code' => 'INV-OPT-C-' . uniqid(),
            'applicant_name' => 'Warga Opsi C',
            'phone' => '081299998888',
            'purpose' => 'Uji Opsi C',
            'penanggung_jawab' => 'Warga Opsi C',
            'borrow_date' => $today->toDateString(),
            'borrowing_time' => '08:00:00',
            'return_date' => $today->toDateString(),
            'return_time' => '16:00:00',
            'status' => 'Sedang Dipinjam',
            'reminder_sent_at' => null,
        ]);

        InventoryBorrowingDetail::create([
            'borrowing_id' => $borrowing->id,
            'inventory_item_id' => $this->item->id,
            'quantity' => 1,
        ]);

        // Jalankan pengingat dengan waktu acuan berada dalam jendela reminder (contoh: 15:45 WIB sebelum 16:00 WIB)
        $this->artisan('inventory:send-return-reminders', ['--time' => '15:45'])
            ->assertSuccessful();

        Mail::assertSent(InventoryReturnReminderMail::class, function ($mail) {
            return $mail->hasTo($this->user->email);
        });

        $this->assertNotNull($borrowing->fresh()->reminder_sent_at);
    }

    /**
     * 11. Status Dikembalikan dan Selesai tetap menghentikan reminder.
     */
    public function test_11_status_dikembalikan_dan_selesai_tetap_menghentikan_reminder(): void
    {
        Mail::fake();

        $today = Carbon::today('Asia/Jakarta');

        $b1 = InventoryBorrowing::create([
            'user_id' => $this->user->id,
            'request_code' => 'INV-RET-1-' . uniqid(),
            'applicant_name' => 'Warga Kembali',
            'purpose' => 'Uji Berhenti Reminder',
            'penanggung_jawab' => 'Warga Kembali',
            'borrow_date' => $today->toDateString(),
            'return_date' => $today->toDateString(),
            'status' => 'Dikembalikan',
        ]);

        $b2 = InventoryBorrowing::create([
            'user_id' => $this->user->id,
            'request_code' => 'INV-RET-2-' . uniqid(),
            'applicant_name' => 'Warga Selesai',
            'purpose' => 'Uji Berhenti Reminder',
            'penanggung_jawab' => 'Warga Selesai',
            'borrow_date' => $today->toDateString(),
            'return_date' => $today->toDateString(),
            'status' => 'Selesai',
        ]);

        // Jalankan pengingat dengan waktu acuan berada dalam jendela reminder (contoh: 15:45 WIB sebelum 16:00 WIB)
        $this->artisan('inventory:send-return-reminders', ['--time' => '15:45'])
            ->assertSuccessful();

        Mail::assertNothingSent();
    }

    /**
     * 12. Pengecekan bentrok jadwal tetap berjalan sesuai aturan sistem.
     */
    public function test_12_pengecekan_bentrok_jadwal_berjalan_sesuai_aturan_sistem(): void
    {
        $today = Carbon::today('Asia/Jakarta')->format('Y-m-d');

        // Item hanya memiliki total_quantity = 2
        // Buat peminjaman aktif pertama dengan quantity = 2 pada jam 08:00 - 12:00
        $activeBorrowing = InventoryBorrowing::create([
            'user_id' => $this->user->id,
            'request_code' => 'INV-ACT-' . uniqid(),
            'applicant_name' => 'Peminjam Pertama',
            'phone' => '081211112222',
            'purpose' => 'Acara Pagi',
            'penanggung_jawab' => 'Peminjam Pertama',
            'borrow_date' => $today,
            'borrowing_time' => '08:00',
            'return_date' => $today,
            'return_time' => '12:00',
            'status' => 'Disetujui',
        ]);

        InventoryBorrowingDetail::create([
            'borrowing_id' => $activeBorrowing->id,
            'inventory_item_id' => $this->item->id,
            'quantity' => 2, // Menghabiskan 2 unit pada rentang 08:00 - 12:00
        ]);

        // Coba ajukan di rentang yang bertabrakan (10:00 - 14:00) -> HARUS DITOLAK
        $clashPayload = [
            'applicant_name' => 'Peminjam Kedua',
            'phone' => '081233334444',
            'purpose' => 'Acara Bentrok',
            'penanggung_jawab' => 'Peminjam Kedua',
            'borrow_date' => $today,
            'borrowing_time' => '10:00',
            'return_date' => $today,
            'return_time' => '14:00',
            'items' => [
                ['id' => $this->item->id, 'quantity' => 1],
            ],
        ];

        $responseClash = $this->actingAs($this->user)
            ->post(route('masyarakat.peminjaman_inventaris.store'), $clashPayload);

        $responseClash->assertSessionHasErrors();

        // Coba ajukan di rentang waktu yang TIDAK bertabrakan (13:00 - 17:00) -> HARUS BERHASIL
        // (Pastikan available_quantity cukup untuk permohonan)
        $validPayload = [
            'applicant_name' => 'Peminjam Sore',
            'phone' => '081255556666',
            'purpose' => 'Acara Sore Bebas Bentrok',
            'penanggung_jawab' => 'Peminjam Sore',
            'borrow_date' => $today,
            'borrowing_time' => '13:00',
            'return_date' => $today,
            'return_time' => '17:00',
            'items' => [
                ['id' => $this->item->id, 'quantity' => 1],
            ],
        ];

        $responseValid = $this->actingAs($this->user)
            ->post(route('masyarakat.peminjaman_inventaris.store'), $validPayload);

        $responseValid->assertSessionHasNoErrors();
    }

    /**
     * 13. Form peminjaman barang inventaris menggunakan format 24 jam tanpa AM/PM.
     */
    public function test_13_form_peminjaman_menggunakan_timepicker_24_jam_tanpa_am_pm(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('masyarakat.peminjaman_inventaris.create'));

        $response->assertStatus(200);
        $content = $response->getContent();

        // Pastikan tidak menggunakan <input type="time"> yang memicu AM/PM browser bawaan
        $this->assertStringNotContainsString('<input type="time"', $content);

        // Pastikan input menggunakan flatpickr dengan konfigurasi 24 jam
        $this->assertStringContainsString('name="borrowing_time"', $content);
        $this->assertStringContainsString('name="return_time"', $content);
        $this->assertStringContainsString('time_24hr: true', $content);
        $this->assertStringContainsString('Format 24 jam WIB', $content);
    }
}

