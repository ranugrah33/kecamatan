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

class InventoryReturnReminderScenarioTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;
    protected InventoryItem $item;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Warga Pengujian Skenario',
            'email' => 'warga.skenario@cikampek.go.id',
            'password' => bcrypt('password123'),
            'role' => 'masyarakat',
            'no_hp' => '081234567899',
        ]);

        $this->item = InventoryItem::create([
            'name' => 'Proyektor Epson EB-E500',
            'category' => 'Elektronik',
            'total_quantity' => 10,
            'available_quantity' => 8,
            'condition' => 'Baik',
            'is_active' => true,
        ]);
    }

    protected function createBorrowing(string $returnTime, ?string $reminderSentAt = null): InventoryBorrowing
    {
        $today = Carbon::today('Asia/Jakarta')->toDateString();

        $borrowing = InventoryBorrowing::create([
            'user_id' => $this->user->id,
            'request_code' => 'INV-SCENARIO-' . uniqid(),
            'applicant_name' => 'Warga Pengujian Skenario',
            'phone' => '081234567899',
            'institution' => 'Karang Taruna Cikampek',
            'purpose' => 'Uji Skenario Reminder',
            'penanggung_jawab' => 'Warga Pengujian Skenario',
            'borrow_date' => $today,
            'borrowing_time' => '08:00:00',
            'return_date' => $today,
            'return_time' => $returnTime,
            'status' => 'Sedang Dipinjam',
            'reminder_sent_at' => $reminderSentAt,
        ]);

        InventoryBorrowingDetail::create([
            'borrowing_id' => $borrowing->id,
            'inventory_item_id' => $this->item->id,
            'quantity' => 1,
        ]);

        return $borrowing;
    }

    /**
     * SKENARIO 1:
     * Jam Pengembalian: 12:15 WIB
     * Waktu Pengujian: 12:14 WIB (1 menit sebelum)
     * Ekspektasi: Sesuai aturan reminder, email dikirim
     */
    public function test_skenario_1_jam_kembali_12_15_uji_12_14_mengirim_email(): void
    {
        Mail::fake();

        $borrowing = $this->createBorrowing('12:15:00');

        $this->artisan('inventory:send-return-reminders', [
            '--id' => $borrowing->id,
            '--time' => '12:14',
        ])->assertSuccessful();

        Mail::assertSent(InventoryReturnReminderMail::class, function ($mail) {
            return $mail->hasTo($this->user->email);
        });

        $this->assertNotNull($borrowing->fresh()->reminder_sent_at);
    }

    /**
     * SKENARIO 2:
     * Jam Pengembalian: 13:00 WIB
     * Waktu Pengujian: 12:55 WIB (5 menit sebelum)
     * Ekspektasi: Sesuai aturan reminder, email dikirim
     */
    public function test_skenario_2_jam_kembali_13_00_uji_12_55_mengirim_email(): void
    {
        Mail::fake();

        $borrowing = $this->createBorrowing('13:00:00');

        $this->artisan('inventory:send-return-reminders', [
            '--id' => $borrowing->id,
            '--time' => '12:55',
        ])->assertSuccessful();

        Mail::assertSent(InventoryReturnReminderMail::class, function ($mail) {
            return $mail->hasTo($this->user->email);
        });

        $this->assertNotNull($borrowing->fresh()->reminder_sent_at);
    }

    /**
     * SKENARIO 3:
     * Jam Pengembalian: 15:30 WIB
     * Waktu Pengujian: 15:00 WIB (30 menit sebelum batas jendela reminder)
     * Ekspektasi: Sesuai aturan reminder, email dikirim
     */
    public function test_skenario_3_jam_kembali_15_30_uji_15_00_mengirim_email(): void
    {
        Mail::fake();

        $borrowing = $this->createBorrowing('15:30:00');

        $this->artisan('inventory:send-return-reminders', [
            '--id' => $borrowing->id,
            '--time' => '15:00',
        ])->assertSuccessful();

        Mail::assertSent(InventoryReturnReminderMail::class, function ($mail) {
            return $mail->hasTo($this->user->email);
        });

        $this->assertNotNull($borrowing->fresh()->reminder_sent_at);
    }

    /**
     * SKENARIO 4:
     * Jam Pengembalian: 12:15 WIB
     * Waktu Pengujian: 12:16 WIB (1 menit setelah batas pengembalian terlewat)
     * Ekspektasi: Tidak mengirim reminder sebelum pengembalian jika waktunya sudah terlewat
     */
    public function test_skenario_4_jam_kembali_12_15_uji_12_16_tidak_mengirim_reminder(): void
    {
        Mail::fake();

        $borrowing = $this->createBorrowing('12:15:00');

        $this->artisan('inventory:send-return-reminders', [
            '--id' => $borrowing->id,
            '--time' => '12:16',
        ])->assertSuccessful();

        // Tidak boleh mengirim reminder sebelum pengembalian karena sudah terlewat
        Mail::assertNothingSent();
        $this->assertNull($borrowing->fresh()->reminder_sent_at);
    }

    /**
     * SKENARIO 5:
     * Jam Pengembalian: 12:15 WIB
     * Waktu Pengujian: 12:14 WIB, pengujian ulang saat reminder sudah terkirim hari ini
     * Ekspektasi: Tidak mengirim email duplikat jika reminder sudah berhasil dikirim
     */
    public function test_skenario_5_jam_kembali_12_15_uji_12_14_ulang_tidak_mengirim_duplikat(): void
    {
        Mail::fake();

        $alreadySentTime = Carbon::now('Asia/Jakarta')->subMinutes(5);
        $borrowing = $this->createBorrowing('12:15:00', $alreadySentTime);

        $this->artisan('inventory:send-return-reminders', [
            '--id' => $borrowing->id,
            '--time' => '12:14',
        ])->assertSuccessful();

        // Tidak boleh mengirim email duplikat
        Mail::assertNothingSent();
    }

    /**
     * SKENARIO TAMBAHAN:
     * Command testing 'inventory:test-reminder' dapat berjalan dengan aman
     * tanpa merubah status peminjaman, tanggal/jam pengembalian, atau reminder_sent_at di database.
     */
    public function test_command_test_reminder_aman_tanpa_merubah_data_database(): void
    {
        Mail::fake();

        $borrowing = $this->createBorrowing('12:15:00');
        $originalStatus = $borrowing->status;
        $originalReturnDate = $borrowing->return_date;
        $originalReturnTime = $borrowing->return_time;
        $originalReminderSentAt = $borrowing->reminder_sent_at;

        $this->artisan('inventory:test-reminder', [
            '--id' => $borrowing->id,
            '--email' => 'admin.test@cikampek.go.id',
            '--scenario' => '1',
            '--send-mail' => true,
        ])->assertSuccessful();

        Mail::assertSent(InventoryReturnReminderMail::class, function ($mail) {
            return $mail->hasTo('admin.test@cikampek.go.id')
                && $mail->isTest === true
                && str_contains($mail->envelope()->subject, '[TEST SIPAF]');
        });

        // Verifikasi tidak ada data di database yang diubah
        $fresh = $borrowing->fresh();
        $this->assertEquals($originalStatus, $fresh->status);
        $this->assertEquals($originalReturnDate, $fresh->return_date);
        $this->assertEquals($originalReturnTime, $fresh->return_time);
        $this->assertEquals($originalReminderSentAt, $fresh->reminder_sent_at);
    }
}

