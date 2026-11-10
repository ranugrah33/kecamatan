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

class InventoryBorrowingStatusSimplificationTest extends TestCase
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
        ]);

        $this->masyarakat = User::factory()->create([
            'role' => 'masyarakat',
            'email' => 'warga.test.simplifikasi@cikampek.go.id',
            'no_hp' => '081234567800',
        ]);

        $this->item = InventoryItem::create([
            'name' => 'Proyektor BenQ MX550',
            'category' => 'Elektronik',
            'total_quantity' => 5,
            'available_quantity' => 5,
            'condition' => 'Baik',
            'is_active' => true,
        ]);
    }

    /**
     * Helper untuk membuat transaksi peminjaman inventaris
     */
    protected function createBorrowing(string $status = 'Menunggu Persetujuan', array $attributes = []): InventoryBorrowing
    {
        $today = Carbon::today('Asia/Jakarta');

        $borrowing = InventoryBorrowing::create(array_merge([
            'user_id' => $this->masyarakat->id,
            'request_code' => 'INV-SIMP-' . uniqid(),
            'applicant_name' => 'Pemohon Uji Sederhana',
            'phone' => '081234567800',
            'institution' => 'Kecamatan Cikampek',
            'purpose' => 'Uji Penyederhanaan Status',
            'penanggung_jawab' => 'Penanggung Jawab Uji',
            'borrow_date' => $today->toDateString(),
            'borrowing_time' => '09:00:00',
            'return_date' => $today->toDateString(),
            'return_time' => '15:00:00',
            'status' => $status,
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
     * 1. Pengajuan baru menghasilkan status 'Menunggu Persetujuan'.
     */
    public function test_1_pengajuan_baru_menghasilkan_status_menunggu_persetujuan(): void
    {
        $today = Carbon::today('Asia/Jakarta')->toDateString();

        $payload = [
            'applicant_name' => 'Pemohon Baru',
            'phone' => '081234567800',
            'institution' => 'Organisasi Pemuda',
            'purpose' => 'Rapat Koordinasi',
            'penanggung_jawab' => 'Ketua Pemuda',
            'borrow_date' => $today,
            'borrowing_time' => '08:30',
            'return_date' => $today,
            'return_time' => '12:00',
            'items' => [
                ['id' => $this->item->id, 'quantity' => 1],
            ],
        ];

        $response = $this->actingAs($this->masyarakat)
            ->post(route('masyarakat.peminjaman_inventaris.store'), $payload);

        $response->assertSessionHasNoErrors();

        $borrowing = InventoryBorrowing::where('applicant_name', 'Pemohon Baru')->latest()->first();
        $this->assertNotNull($borrowing);
        $this->assertEquals(InventoryBorrowing::STATUS_MENUNGGU_PERSETUJUAN, $borrowing->status);
        $this->assertEquals(InventoryBorrowing::STATUS_MENUNGGU_PERSETUJUAN, $borrowing->status_label);
    }

    /**
     * 2. Petugas dapat menyetujui pengajuan ('Menunggu Persetujuan' -> 'Disetujui') dan stok berkurang.
     */
    public function test_2_petugas_dapat_menyetujui_pengajuan_dan_stok_berkurang(): void
    {
        $borrowing = $this->createBorrowing(InventoryBorrowing::STATUS_MENUNGGU_PERSETUJUAN);
        $initialStock = $this->item->fresh()->available_quantity;

        $response = $this->actingAs($this->admin)
            ->put(route('admin.peminjaman_inventaris.updateStatus', $borrowing->id), [
                'status' => InventoryBorrowing::STATUS_DISETUJUI,
            ]);

        $response->assertRedirect(route('admin.peminjaman_inventaris.show', $borrowing->id));
        $this->assertEquals(InventoryBorrowing::STATUS_DISETUJUI, $borrowing->fresh()->status);
        // Stok berkurang 2 unit (5 -> 3)
        $this->assertEquals($initialStock - 2, $this->item->fresh()->available_quantity);
    }

    /**
     * 3. Petugas dapat menolak pengajuan ('Menunggu Persetujuan' -> 'Ditolak').
     */
    public function test_3_petugas_dapat_menolak_pengajuan_dengan_alasan(): void
    {
        $borrowing = $this->createBorrowing(InventoryBorrowing::STATUS_MENUNGGU_PERSETUJUAN);

        $response = $this->actingAs($this->admin)
            ->put(route('admin.peminjaman_inventaris.updateStatus', $borrowing->id), [
                'status' => InventoryBorrowing::STATUS_DITOLAK,
                'rejection_reason' => 'Barang sedang dalam masa pemeliharaan berkala.',
            ]);

        $response->assertRedirect(route('admin.peminjaman_inventaris.show', $borrowing->id));
        $fresh = $borrowing->fresh();
        $this->assertEquals(InventoryBorrowing::STATUS_DITOLAK, $fresh->status);
        $this->assertEquals('Barang sedang dalam masa pemeliharaan berkala.', $fresh->rejection_reason);
    }

    /**
     * 4. Status 'Disetujui' dapat berubah menjadi 'Sedang Dipinjam' (barang diserahkan).
     */
    public function test_4_status_disetujui_dapat_berubah_menjadi_sedang_dipinjam(): void
    {
        $borrowing = $this->createBorrowing(InventoryBorrowing::STATUS_DISETUJUI);
        // Kurangi stok seperti saat disetujui
        $this->item->decrement('available_quantity', 2);
        $stockAfterApprove = $this->item->fresh()->available_quantity;

        $response = $this->actingAs($this->admin)
            ->put(route('admin.peminjaman_inventaris.updateStatus', $borrowing->id), [
                'status' => InventoryBorrowing::STATUS_SEDANG_DIPINJAM,
            ]);

        $response->assertRedirect(route('admin.peminjaman_inventaris.show', $borrowing->id));
        $this->assertEquals(InventoryBorrowing::STATUS_SEDANG_DIPINJAM, $borrowing->fresh()->status);
        // Stok tetap ditahan (tidak berkurang ganda)
        $this->assertEquals($stockAfterApprove, $this->item->fresh()->available_quantity);
    }

    /**
     * 5. Pengembalian barang mengubah status menjadi 'Selesai' dan stok dipulihkan.
     */
    public function test_5_pengembalian_barang_mengubah_status_menjadi_selesai_dan_stok_pulih(): void
    {
        $borrowing = $this->createBorrowing(InventoryBorrowing::STATUS_SEDANG_DIPINJAM);
        // Simulasi barang sedang dipinjam (stok berkurang 2: 5 -> 3)
        $this->item->decrement('available_quantity', 2);
        $this->assertEquals(3, $this->item->fresh()->available_quantity);

        $response = $this->actingAs($this->admin)
            ->put(route('admin.peminjaman_inventaris.updateStatus', $borrowing->id), [
                'status' => InventoryBorrowing::STATUS_SELESAI,
            ]);

        $response->assertRedirect(route('admin.peminjaman_inventaris.show', $borrowing->id));
        $this->assertEquals(InventoryBorrowing::STATUS_SELESAI, $borrowing->fresh()->status);
        // Stok dipulihkan kembali ke total 5
        $this->assertEquals(5, $this->item->fresh()->available_quantity);
    }

    /**
     * 6. Status 'Ditolak' dan 'Selesai' terkunci dan tidak dapat diubah kembali.
     */
    public function test_6_status_ditolak_dan_selesai_merupakan_status_akhir_terkunci(): void
    {
        $borrowingDitolak = $this->createBorrowing(InventoryBorrowing::STATUS_DITOLAK);
        $borrowingSelesai = $this->createBorrowing(InventoryBorrowing::STATUS_SELESAI);

        // Percobaan mengubah status Ditolak kembali ke Menunggu Persetujuan -> GAGAL
        $response1 = $this->actingAs($this->admin)
            ->put(route('admin.peminjaman_inventaris.updateStatus', $borrowingDitolak->id), [
                'status' => InventoryBorrowing::STATUS_MENUNGGU_PERSETUJUAN,
            ]);
        $response1->assertSessionHasErrors();
        $this->assertEquals(InventoryBorrowing::STATUS_DITOLAK, $borrowingDitolak->fresh()->status);

        // Percobaan mengubah status Selesai kembali ke Sedang Dipinjam -> GAGAL
        $response2 = $this->actingAs($this->admin)
            ->put(route('admin.peminjaman_inventaris.updateStatus', $borrowingSelesai->id), [
                'status' => InventoryBorrowing::STATUS_SEDANG_DIPINJAM,
            ]);
        $response2->assertSessionHasErrors();
        $this->assertEquals(InventoryBorrowing::STATUS_SELESAI, $borrowingSelesai->fresh()->status);
    }

    /**
     * 7. Data transaksi lama (Diajukan, Diproses, Dikembalikan) tetap terbaca dan ditampilkan dengan label baru.
     */
    public function test_7_transaksi_lama_tetap_dapat_dibaca_dan_ditampilkan_dengan_benar(): void
    {
        $legacyDiajukan = $this->createBorrowing('Diajukan');
        $legacyDiproses = $this->createBorrowing('Diproses');
        $legacyDikembalikan = $this->createBorrowing('Dikembalikan');

        // Model attribute mapping
        $this->assertEquals('Menunggu Persetujuan', $legacyDiajukan->status_label);
        $this->assertEquals('Menunggu Persetujuan', $legacyDiproses->status_label);
        $this->assertEquals('Selesai', $legacyDikembalikan->status_label);

        // Detail masyarakat
        $respMasy = $this->actingAs($this->masyarakat)
            ->get(route('masyarakat.peminjaman_inventaris.show', $legacyDiajukan->id));
        $respMasy->assertStatus(200);
        $respMasy->assertSee('Menunggu Persetujuan');

        // Detail admin
        $respAdmin = $this->actingAs($this->admin)
            ->get(route('admin.peminjaman_inventaris.show', $legacyDikembalikan->id));
        $respAdmin->assertStatus(200);
        $respAdmin->assertSee('Selesai');
    }

    /**
     * 8. Filter status admin menampilkan 5 status baru dan memetakan data lama dengan konsisten.
     */
    public function test_8_filter_status_admin_bekerja_dengan_5_status_baru(): void
    {
        $this->createBorrowing(InventoryBorrowing::STATUS_MENUNGGU_PERSETUJUAN, ['request_code' => 'INV-NEW-PENDING']);
        $this->createBorrowing('Diajukan', ['request_code' => 'INV-OLD-DIAJUKAN']);
        $this->createBorrowing(InventoryBorrowing::STATUS_SELESAI, ['request_code' => 'INV-NEW-SELESAI']);
        $this->createBorrowing('Dikembalikan', ['request_code' => 'INV-OLD-KEMBALI']);

        // Filter 'Menunggu Persetujuan' harus memuat baik data baru maupun data lama 'Diajukan'
        $respPending = $this->actingAs($this->admin)
            ->get(route('admin.peminjaman_inventaris.index', ['status' => 'Menunggu Persetujuan']));
        $respPending->assertStatus(200);
        $respPending->assertSee('INV-NEW-PENDING');
        $respPending->assertSee('INV-OLD-DIAJUKAN');
        $respPending->assertDontSee('INV-NEW-SELESAI');

        // Filter 'Selesai' harus memuat baik 'Selesai' maupun 'Dikembalikan'
        $respSelesai = $this->actingAs($this->admin)
            ->get(route('admin.peminjaman_inventaris.index', ['status' => 'Selesai']));
        $respSelesai->assertStatus(200);
        $respSelesai->assertSee('INV-NEW-SELESAI');
        $respSelesai->assertSee('INV-OLD-KEMBALI');
        $respSelesai->assertDontSee('INV-NEW-PENDING');
    }

    /**
     * 9. Status 'Selesai' tidak lagi dianggap sebagai transaksi aktif (jadwal tidak bentrok).
     */
    public function test_9_status_selesai_tidak_dianggap_sebagai_transaksi_aktif(): void
    {
        $today = Carbon::today('Asia/Jakarta')->toDateString();

        // Buat peminjaman berstatus Selesai yang menggunakan seluruh 5 unit stok pada jam 08:00 - 12:00
        $borrowingSelesai = InventoryBorrowing::create([
            'user_id' => $this->masyarakat->id,
            'request_code' => 'INV-DONE-CLASH',
            'applicant_name' => 'Pemohon Selesai',
            'phone' => '081234567800',
            'purpose' => 'Kegiatan Lampau',
            'penanggung_jawab' => 'PJ Lampau',
            'borrow_date' => $today,
            'borrowing_time' => '08:00',
            'return_date' => $today,
            'return_time' => '12:00',
            'status' => InventoryBorrowing::STATUS_SELESAI,
        ]);

        InventoryBorrowingDetail::create([
            'borrowing_id' => $borrowingSelesai->id,
            'inventory_item_id' => $this->item->id,
            'quantity' => 5,
        ]);

        // Pengajuan baru di rentang waktu yang sama (08:00 - 12:00) HARUS BERHASIL karena status Selesai tidak aktif
        $payload = [
            'applicant_name' => 'Pemohon Jam Sama',
            'phone' => '081234567800',
            'purpose' => 'Kegiatan Baru',
            'penanggung_jawab' => 'PJ Baru',
            'borrow_date' => $today,
            'borrowing_time' => '08:00',
            'return_date' => $today,
            'return_time' => '12:00',
            'items' => [
                ['id' => $this->item->id, 'quantity' => 3],
            ],
        ];

        $response = $this->actingAs($this->masyarakat)
            ->post(route('masyarakat.peminjaman_inventaris.store'), $payload);

        $response->assertSessionHasNoErrors();
    }

    /**
     * 10. Email reminder hanya menargetkan status 'Sedang Dipinjam'.
     */
    public function test_10_reminder_email_tetap_hanya_menargetkan_transaksi_sedang_dipinjam(): void
    {
        Mail::fake();

        $today = Carbon::today('Asia/Jakarta');

        $borrowing = $this->createBorrowing(InventoryBorrowing::STATUS_SEDANG_DIPINJAM, [
            'return_date' => $today->toDateString(),
            'return_time' => '16:00:00',
            'reminder_sent_at' => null,
        ]);

        $this->artisan('inventory:send-return-reminders', ['--time' => '15:45'])
            ->assertSuccessful();

        Mail::assertSent(InventoryReturnReminderMail::class, function ($mail) {
            return $mail->hasTo($this->masyarakat->email);
        });

        $this->assertNotNull($borrowing->fresh()->reminder_sent_at);
    }

    /**
     * 11. Reminder tidak terkirim untuk transaksi 'Selesai' maupun 'Menunggu Persetujuan'.
     */
    public function test_11_reminder_tidak_terkirim_untuk_transaksi_selesai_dan_menunggu_persetujuan(): void
    {
        Mail::fake();

        $today = Carbon::today('Asia/Jakarta');

        $bSelesai = $this->createBorrowing(InventoryBorrowing::STATUS_SELESAI, [
            'return_date' => $today->toDateString(),
            'return_time' => '16:00:00',
        ]);

        $bPending = $this->createBorrowing(InventoryBorrowing::STATUS_MENUNGGU_PERSETUJUAN, [
            'return_date' => $today->toDateString(),
            'return_time' => '16:00:00',
        ]);

        $this->artisan('inventory:send-return-reminders', ['--time' => '15:45'])
            ->assertSuccessful();

        Mail::assertNothingSent();
        $this->assertNull($bSelesai->fresh()->reminder_sent_at);
        $this->assertNull($bPending->fresh()->reminder_sent_at);
    }

    /**
     * 12. Dropdown dan filter admin hanya menampilkan 5 status baru.
     */
    public function test_12_dropdown_dan_filter_admin_hanya_menampilkan_lima_status_baru(): void
    {
        $borrowing = $this->createBorrowing(InventoryBorrowing::STATUS_MENUNGGU_PERSETUJUAN);

        // Halaman Detail
        $respShow = $this->actingAs($this->admin)
            ->get(route('admin.peminjaman_inventaris.show', $borrowing->id));
        $respShow->assertStatus(200);

        // Pastikan 5 status baru ada di opsi
        $respShow->assertSee('Menunggu Persetujuan');
        $respShow->assertSee('Disetujui');
        $respShow->assertSee('Ditolak');
        $respShow->assertSee('Sedang Dipinjam');
        $respShow->assertSee('Selesai');

        // Pastikan opsi lama tidak muncul di dropdown
        $respShow->assertDontSee('<option value="Diproses"', false);
        $respShow->assertDontSee('<option value="Dikembalikan"', false);

        // Halaman Index (Filter)
        $respIndex = $this->actingAs($this->admin)
            ->get(route('admin.peminjaman_inventaris.index'));
        $respIndex->assertStatus(200);
        $respIndex->assertSee('status=Menunggu%20Persetujuan', false);
        $respIndex->assertDontSee('status=Diproses', false);
        $respIndex->assertDontSee('status=Dikembalikan', false);
    }

    /**
     * 13. Halaman laporan admin menghitung status 'Menunggu Persetujuan' dan data lama 'Diajukan'/'Diproses'.
     */
    public function test_13_laporan_admin_menghitung_status_menunggu_tanpa_duplikasi(): void
    {
        $this->createBorrowing(InventoryBorrowing::STATUS_MENUNGGU_PERSETUJUAN, ['request_code' => 'INV-LAP-NEW']);
        $this->createBorrowing('Diajukan', ['request_code' => 'INV-LAP-OLD1']);
        $this->createBorrowing('Diproses', ['request_code' => 'INV-LAP-OLD2']);

        $response = $this->actingAs($this->admin)->get(route('admin.laporan'));
        $response->assertStatus(200);

        // Nilai $menunggu yang diteruskan ke view harus mencakup minimal ketiga transaksi ini
        $menungguCount = $response->viewData('menunggu');
        $this->assertGreaterThanOrEqual(3, $menungguCount);

        // Pastikan tabel memuat seluruh nomor pengajuan
        $response->assertSee('INV-LAP-NEW');
        $response->assertSee('INV-LAP-OLD1');
        $response->assertSee('INV-LAP-OLD2');
    }

    /**
     * 14. Pemetaan warna badge laporan admin benar untuk seluruh 5 status dan data Dikembalikan tampil Selesai.
     */
    public function test_14_warna_badge_laporan_admin_dan_pemetaan_dikembalikan_ke_selesai(): void
    {
        $b1 = $this->createBorrowing(InventoryBorrowing::STATUS_MENUNGGU_PERSETUJUAN, ['request_code' => 'INV-BADGE-1']);
        $b2 = $this->createBorrowing(InventoryBorrowing::STATUS_DISETUJUI, ['request_code' => 'INV-BADGE-2']);
        $b3 = $this->createBorrowing(InventoryBorrowing::STATUS_DITOLAK, ['request_code' => 'INV-BADGE-3']);
        $b4 = $this->createBorrowing(InventoryBorrowing::STATUS_SEDANG_DIPINJAM, ['request_code' => 'INV-BADGE-4']);
        $b5 = $this->createBorrowing(InventoryBorrowing::STATUS_SELESAI, ['request_code' => 'INV-BADGE-5']);
        $bOld = $this->createBorrowing('Dikembalikan', ['request_code' => 'INV-BADGE-OLD']);

        $response = $this->actingAs($this->admin)->get(route('admin.laporan'));
        $response->assertStatus(200);

        // Pastikan data lama Dikembalikan tampil sebagai 'Selesai' di laporan
        $response->assertSee('INV-BADGE-OLD');
        $response->assertDontSee('>Dikembalikan<', false);

        // Pastikan badge warna Tailwind sesuai
        // Menunggu Persetujuan -> bg-blue-100 text-blue-800
        $response->assertSee('bg-blue-100 text-blue-800');
        // Sedang Dipinjam -> bg-amber-100 text-amber-800
        $response->assertSee('bg-amber-100 text-amber-800');
        // Disetujui / Selesai -> bg-green-100 text-green-800
        $response->assertSee('bg-green-100 text-green-800');
        // Ditolak -> bg-red-100 text-red-800
        $response->assertSee('bg-red-100 text-red-800');
    }
}
