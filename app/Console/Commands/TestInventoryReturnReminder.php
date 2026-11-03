<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\InventoryBorrowing;
use App\Models\InventoryBorrowingDetail;
use App\Models\InventoryItem;
use App\Models\User;
use App\Mail\InventoryReturnReminderMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TestInventoryReturnReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:test-reminder 
                            {--id= : ID peminjaman yang digunakan untuk pengujian (opsional)}
                            {--email= : Alamat email tujuan pengujian (contoh: keccikampek0@gmail.com)}
                            {--scenario= : Nomor skenario uji (1, 2, 3, 4, 5)}
                            {--return-time= : Simulasi jam pengembalian (HH:mm WIB)}
                            {--check-time= : Simulasi jam pemeriksaan (HH:mm WIB)}
                            {--window=30 : Jendela pengingat sebelum pengembalian dalam menit (default: 30 menit)}
                            {--send-mail : Kirim email nyata ke alamat email tujuan}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mekanisme pengujian aman email reminder pengembalian inventaris SIPAF tanpa mengubah data produksi';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $timezone = config('app.timezone', 'Asia/Jakarta');
        $idParam = $this->option('id');
        $emailParam = $this->option('email');
        $scenario = $this->option('scenario');
        $returnTimeParam = $this->option('return-time');
        $checkTimeParam = $this->option('check-time');
        $windowMinutes = (int) ($this->option('window') ?: 30);
        $shouldSendMail = (bool) ($this->option('send-mail') || $emailParam);

        $this->info("==========================================================");
        $this->info("  SIPAF KECAMATAN CIKAMPEK - TESTING EMAIL REMINDER");
        $this->info("==========================================================");

        // 1. Ambil atau siapkan data peminjaman untuk pengujian
        $borrowing = null;
        if ($idParam) {
            $borrowing = InventoryBorrowing::with(['user', 'details.item'])->find($idParam);
            if (!$borrowing) {
                $this->error("Peminjaman dengan ID {$idParam} tidak ditemukan di database.");
                return Command::FAILURE;
            }
            $this->info("Menggunakan data peminjaman dari database (ID: {$borrowing->id}, Kode: {$borrowing->request_code})");
        } else {
            // Coba ambil peminjaman aktif terakhir
            $borrowing = InventoryBorrowing::with(['user', 'details.item'])->latest()->first();
            if ($borrowing) {
                $this->info("Menggunakan data peminjaman terbaru di database (ID: {$borrowing->id}, Kode: {$borrowing->request_code})");
            } else {
                // Buat mock objek in-memory jika database kosong (tidak disimpan ke database)
                $borrowing = new InventoryBorrowing([
                    'id' => 9999,
                    'request_code' => 'INV-TEST-SIMULASI',
                    'applicant_name' => 'Warga Simulasi Pengujian',
                    'phone' => '081234567890',
                    'institution' => 'Kecamatan Cikampek (Testing)',
                    'purpose' => 'Pengujian Fitur Reminder Inventaris',
                    'borrow_date' => Carbon::now($timezone)->toDateString(),
                    'borrowing_time' => '08:00:00',
                    'return_date' => Carbon::now($timezone)->toDateString(),
                    'return_time' => '12:15:00',
                    'status' => 'Sedang Dipinjam',
                ]);
                $this->info("Menggunakan data dummy in-memory khusus testing.");
            }
        }

        // Tentukan parameter waktu berdasarkan skenario yang dipilih atau input manual
        $todayStr = Carbon::now($timezone)->toDateString();
        $isDuplicateSimulated = false;

        if ($scenario) {
            $this->info("--> Mengaktifkan Mode Skenario {$scenario}:");
            switch ((int)$scenario) {
                case 1:
                    // Skenario 1: Return 12:15 WIB, Waktu Uji 12:14 WIB (selisih 1 menit) -> Kirim
                    $returnTime = '12:15';
                    $checkTime = '12:14';
                    $scenarioDesc = 'Return 12:15 WIB | Uji 12:14 WIB (1 menit sebelum) | Ekspektasi: EMAIL DIKIRIM';
                    break;
                case 2:
                    // Skenario 2: Return 13:00 WIB, Waktu Uji 12:55 WIB (selisih 5 menit) -> Kirim
                    $returnTime = '13:00';
                    $checkTime = '12:55';
                    $scenarioDesc = 'Return 13:00 WIB | Uji 12:55 WIB (5 menit sebelum) | Ekspektasi: EMAIL DIKIRIM';
                    break;
                case 3:
                    // Skenario 3: Return 15:30 WIB, Waktu Uji 15:00 WIB (selisih 30 menit) -> Kirim
                    $returnTime = '15:30';
                    $checkTime = '15:00';
                    $scenarioDesc = 'Return 15:30 WIB | Uji 15:00 WIB (30 menit sebelum batas jendela) | Ekspektasi: EMAIL DIKIRIM';
                    break;
                case 4:
                    // Skenario 4: Return 12:15 WIB, Waktu Uji 12:16 WIB (lewat 1 menit) -> Jangan kirim
                    $returnTime = '12:15';
                    $checkTime = '12:16';
                    $scenarioDesc = 'Return 12:15 WIB | Uji 12:16 WIB (lewat waktu) | Ekspektasi: TIDAK MENGIRIM REMINDER SEBELUM PENGEMBALIAN';
                    break;
                case 5:
                    // Skenario 5: Return 12:15 WIB, Uji 12:14 WIB (pengujian ulang, sudah pernah kirim) -> Jangan kirim
                    $returnTime = '12:15';
                    $checkTime = '12:14';
                    $isDuplicateSimulated = true;
                    $scenarioDesc = 'Return 12:15 WIB | Uji 12:14 WIB (Pengujian ulang saat reminder sudah terkirim) | Ekspektasi: TIDAK MENGIRIM EMAIL DUPLIKAT';
                    break;
                default:
                    $this->error("Nomor skenario tidak valid! Pilih antara 1 sampai 5.");
                    return Command::FAILURE;
            }
            $this->comment("    {$scenarioDesc}");
        } else {
            $returnTime = $returnTimeParam 
                ?: ($borrowing->return_time ? Carbon::parse($borrowing->return_time)->format('H:i') : '12:15');
            $checkTime = $checkTimeParam 
                ?: Carbon::now($timezone)->format('H:i');
        }

        // Hitung evaluasi logika waktu
        $returnDateTime = Carbon::parse($todayStr . ' ' . $returnTime, $timezone);
        $checkDateTime = Carbon::parse($todayStr . ' ' . $checkTime, $timezone);
        $windowStart = $returnDateTime->copy()->subMinutes($windowMinutes);

        // Ambil email dari relasi user transaksi asli
        $citizenEmail = $borrowing->user->email ?? null;
        $recipientEmail = $emailParam ?: $citizenEmail;

        if (empty($recipientEmail)) {
            $this->error("Email akun masyarakat tidak ditemukan pada transaksi ini dan opsi --email tidak diisi.");
            return Command::FAILURE;
        }

        $this->table(
            ['Parameter', 'Nilai'],
            [
                ['ID Transaksi', $borrowing->id ?? 'Mock ID'],
                ['Kode Permohonan', $borrowing->request_code ?? '-'],
                ['Nama Pemohon', $borrowing->applicant_name ?? 'Pemohon'],
                ['Email Akun Masyarakat (DB)', $citizenEmail ?? '-'],
                ['Email Tujuan Pengujian (Mail::to)', $recipientEmail . ($emailParam ? ' (Override via --email)' : ' (Otomatis dari Akun Masyarakat)')],
                ['Tanggal Pengembalian', $todayStr],
                ['Jam Batas Pengembalian', $returnTime . ' WIB'],
                ['Waktu Pemeriksaan Pengujian', $checkTime . ' WIB (' . $timezone . ')'],
                ['Jendela Reminder', "{$windowMinutes} menit ({$windowStart->format('H:i')} - {$returnTime} WIB)"],
                ['Status Database Riil', $borrowing->status ?? 'Sedang Dipinjam'],
                ['Mode Pengujian', 'Aman (Tidak mengubah status/data produksi)'],
            ]
        );

        // Evaluasi Kriteria
        $canSend = true;
        $reason = '';

        if ($isDuplicateSimulated) {
            $canSend = false;
            $reason = 'Simulasi duplikasi: Reminder hari ini sudah tercatat terkirim sebelumnya. Sistem mencegah pengiriman berulang.';
        } elseif ($checkDateTime->greaterThan($returnDateTime)) {
            $canSend = false;
            $reason = "Waktu pengembalian ({$returnTime} WIB) telah terlewat pada waktu pemeriksaan ({$checkTime} WIB). Reminder sebelum pengembalian tidak dikirim.";
        } elseif ($checkDateTime->lessThan($windowStart)) {
            $canSend = false;
            $diff = $checkDateTime->diffInMinutes($returnDateTime, false);
            $reason = "Belum masuk jendela reminder ({$diff} menit sebelum waktu pengembalian, jendela: {$windowMinutes} menit).";
        }

        // Tampilkan Hasil Evaluasi Logika
        if ($canSend) {
            $this->info("HASIL EVALUASI LOGIKA: MEMENUHI SYARAT PENGIRIMAN REMINDER");
            $this->line("  -> Transaksi berada di dalam jendela waktu reminder ({$windowMinutes} menit sebelum {$returnTime} WIB).");
        } else {
            $this->warn("HASIL EVALUASI LOGIKA: TIDAK DIKIRIM (SESUAI ATURAN SISTEM)");
            $this->line("  -> Alasan: {$reason}");
        }

        // Buat clone mock peminjaman dengan data simulasi agar tampilan email akurat tanpa merusak database
        $simulatedBorrowing = clone $borrowing;
        $simulatedBorrowing->return_date = Carbon::parse($todayStr);
        $simulatedBorrowing->return_time = $returnTime . ':00';

        // Pastikan relasi details tersedia untuk template
        if (!$simulatedBorrowing->relationLoaded('details') || $simulatedBorrowing->details->isEmpty()) {
            $sampleItem = InventoryItem::first();
            $detailMock = new InventoryBorrowingDetail([
                'inventory_item_id' => $sampleItem->id ?? 1,
                'quantity' => 1,
            ]);
            $detailMock->setRelation('item', $sampleItem ?? new InventoryItem(['name' => 'Proyektor Epson EB-E500']));
            $simulatedBorrowing->setRelation('details', collect([$detailMock]));
        }

        $itemNames = $simulatedBorrowing->details->map(function ($d) {
            $name = $d->item->name ?? 'Barang Inventaris';
            return "{$name} ({$d->quantity} Unit)";
        })->implode(', ');

        $logContext = [
            'borrowing_id' => $simulatedBorrowing->id,
            'request_code' => $simulatedBorrowing->request_code,
            'applicant_name' => $simulatedBorrowing->applicant_name,
            'recipient_email' => $recipientEmail,
            'items' => $itemNames,
            'return_date' => $todayStr,
            'return_time' => $returnTime . ' WIB',
            'checked_at' => $checkDateTime->format('Y-m-d H:i:s') . ' WIB',
            'is_test' => true,
            'scenario' => $scenario ?? 'manual',
        ];

        // Eksekusi pengiriman email jika memenuhi syarat dan opsi kirim aktif
        if ($canSend && $shouldSendMail) {
            $this->line("----------------------------------------------------------");
            $this->info("Mengirim email pengujian via SMTP ke: {$recipientEmail}...");
            try {
                // Gunakan Mailable dengan flag isTest = true
                Mail::to($recipientEmail)->send(new InventoryReturnReminderMail(
                    $simulatedBorrowing, 
                    Carbon::parse($todayStr, $timezone)->startOfDay(), 
                    true // isTest = true
                ));

                $this->info("BERHASIL: Email pengujian berhasil dikirim ke {$recipientEmail}!");
                $this->info("Subjek: [TEST SIPAF] Reminder Pengembalian Barang Inventaris");
                Log::info("Email testing reminder berhasil dikirim ke {$recipientEmail}.", array_merge($logContext, [
                    'status' => 'BERHASIL_DIKIRIM',
                ]));
            } catch (\Throwable $e) {
                $this->error("GAGAL: Pengiriman email gagal: " . $e->getMessage());
                Log::error("Gagal mengirim email testing reminder ke {$recipientEmail}: " . $e->getMessage(), array_merge($logContext, [
                    'status' => 'GAGAL_DIKIRIM',
                    'failure_reason' => $e->getMessage(),
                ]));
                return Command::FAILURE;
            }
        } elseif (!$canSend) {
            $this->line("----------------------------------------------------------");
            $this->info("Pengiriman email dilewati sesuai ekspektasi aturan ({$reason}).");
            Log::info("Email testing reminder dilewati sesuai aturan sistem: {$reason}", array_merge($logContext, [
                'status' => 'DILEWATI_SESUAI_ATURAN',
                'reason' => $reason,
            ]));
        } else {
            $this->line("----------------------------------------------------------");
            $this->comment("Simulasi selesai. Tambahkan opsi --send-mail atau --email=<alamat> untuk mengirimkan email nyata.");
        }

        $this->info("==========================================================");
        $this->info("  PENGUJIAN SELESAI TANPA MERUBAH DATA PRODUKSI");
        $this->info("==========================================================");

        return Command::SUCCESS;
    }
}

