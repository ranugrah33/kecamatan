<?php

namespace Tests\Feature;

use App\Models\InventoryBorrowing;
use App\Models\InventoryBorrowingDetail;
use App\Models\InventoryItem;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class InventoryBorrowingNotificationSpamTest extends TestCase
{
    use DatabaseTransactions;

    protected User $masyarakat;
    protected InventoryItem $item;

    protected function setUp(): void
    {
        parent::setUp();

        $this->masyarakat = User::factory()->create([
            'role' => 'masyarakat',
            'email' => 'warga.inventaris.test@example.com',
            'name' => 'Warga Inventaris Test',
            'no_hp' => '081299887766',
        ]);

        $this->item = InventoryItem::create([
            'name' => 'Proyektor HD Epson Test',
            'category' => 'Elektronik',
            'total_quantity' => 10,
            'available_quantity' => 10,
            'condition' => 'Baik',
            'is_active' => true,
        ]);
    }

    /**
     * 1. Halaman formulir create peminjaman inventaris menampilkan info pengiriman email dan spam.
     */
    public function test_halaman_create_inventory_borrowing_menampilkan_pemberitahuan_email_dan_spam(): void
    {
        $response = $this->actingAs($this->masyarakat)
            ->get(route('masyarakat.peminjaman_inventaris.create'));

        $response->assertStatus(200);
        $response->assertSee('Pemberitahuan Pengiriman Notifikasi & Status');
        $response->assertSee('Spam');
        $response->assertSee('warga.inventaris.test@example.com');
    }

    /**
     * 2. Pengajuan peminjaman inventaris berhasil dan redirect dengan flash session success mengenai email dan spam.
     */
    public function test_masyarakat_submit_inventory_borrowing_redirects_with_email_spam_notice(): void
    {
        $payload = [
            'applicant_name' => 'Warga Inventaris Test',
            'phone' => '081299887766',
            'institution' => 'Komunitas Pemuda Cikampek',
            'penanggung_jawab' => 'Warga Inventaris Test',
            'purpose' => 'Presentasi Program Inovasi Desa',
            'borrow_date' => now()->addDays(5)->format('Y-m-d'),
            'borrowing_time' => '09:00',
            'return_date' => now()->addDays(5)->format('Y-m-d'),
            'return_time' => '13:00',
            'notes' => 'Harap kabel HDMI disertakan',
            'items' => [
                [
                    'id' => $this->item->id,
                    'quantity' => 2,
                ],
            ],
        ];

        $response = $this->actingAs($this->masyarakat)
            ->post(route('masyarakat.peminjaman_inventaris.store'), $payload);

        $response->assertSessionHasNoErrors();

        $borrowing = InventoryBorrowing::where('applicant_name', 'Warga Inventaris Test')->latest('id')->first();
        $this->assertNotNull($borrowing);

        $response->assertRedirect(route('masyarakat.peminjaman_inventaris.sukses', $borrowing->id));
        $response->assertSessionHas('success');

        $sessionSuccess = session('success');
        $this->assertStringContainsString('spam', strtolower($sessionSuccess));
        $this->assertStringContainsString('email', strtolower($sessionSuccess));
    }

    /**
     * 3. Halaman sukses peminjaman inventaris memuat pop-up modal dan pemberitahuan mengenai email dan spam.
     */
    public function test_halaman_sukses_inventory_borrowing_menampilkan_popup_dan_notifikasi_spam(): void
    {
        $borrowing = InventoryBorrowing::create([
            'user_id' => $this->masyarakat->id,
            'request_code' => 'INV-TEST-0001',
            'applicant_name' => 'Warga Inventaris Test',
            'phone' => '081299887766',
            'penanggung_jawab' => 'Warga Inventaris Test',
            'purpose' => 'Presentasi Program Inovasi Desa',
            'borrow_date' => now()->addDays(5)->format('Y-m-d'),
            'borrowing_time' => '09:00',
            'return_date' => now()->addDays(5)->format('Y-m-d'),
            'return_time' => '13:00',
            'status' => InventoryBorrowing::STATUS_MENUNGGU_PERSETUJUAN,
        ]);

        InventoryBorrowingDetail::create([
            'borrowing_id' => $borrowing->id,
            'inventory_item_id' => $this->item->id,
            'quantity' => 2,
        ]);

        $response = $this->actingAs($this->masyarakat)
            ->get(route('masyarakat.peminjaman_inventaris.sukses', $borrowing->id));

        $response->assertStatus(200);

        // Memastikan modal dan teks petunjuk ada
        $response->assertSee('showEmailModal');
        $response->assertSee('Pemberitahuan Penting');
        $response->assertSee('Cek Email & Periksa Folder Spam Anda');
        $response->assertSee('warga.inventaris.test@example.com');
        $response->assertSee('Spam');
        $response->assertSee('Bukan Spam');
        $response->assertSee('Mengerti, Saya Paham');

        // Memastikan banner notifikasi permanen di halaman juga ada
        $response->assertSee('Informasi Pengecekan Email & Folder Spam');
        $response->assertSee('Buka Pop-up Petunjuk');
    }

    /**
     * 4. Halaman detail (show) peminjaman inventaris memuat pengingat notifikasi email dan spam.
     */
    public function test_halaman_show_inventory_borrowing_menampilkan_pengingat_email_dan_spam(): void
    {
        $borrowing = InventoryBorrowing::create([
            'user_id' => $this->masyarakat->id,
            'request_code' => 'INV-TEST-0002',
            'applicant_name' => 'Warga Inventaris Test',
            'phone' => '081299887766',
            'penanggung_jawab' => 'Warga Inventaris Test',
            'purpose' => 'Presentasi Program Inovasi Desa',
            'borrow_date' => now()->addDays(5)->format('Y-m-d'),
            'borrowing_time' => '09:00',
            'return_date' => now()->addDays(5)->format('Y-m-d'),
            'return_time' => '13:00',
            'status' => InventoryBorrowing::STATUS_MENUNGGU_PERSETUJUAN,
        ]);

        InventoryBorrowingDetail::create([
            'borrowing_id' => $borrowing->id,
            'inventory_item_id' => $this->item->id,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($this->masyarakat)
            ->get(route('masyarakat.peminjaman_inventaris.show', $borrowing->id));

        $response->assertStatus(200);
        $response->assertSee('Pemberitahuan Email & Folder Spam');
        $response->assertSee('warga.inventaris.test@example.com');
        $response->assertSee('Spam');
    }
}
