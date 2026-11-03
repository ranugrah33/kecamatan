<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\InventoryBorrowing;
use App\Mail\InventoryReturnReminderMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SendInventoryReturnReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:send-return-reminders 
                            {--date= : Tanggal acuan pemeriksaan (YYYY-MM-DD), default hari ini WIB}
                            {--time= : Jam acuan pemeriksaan (HH:mm WIB), default waktu saat ini WIB}
                            {--window=30 : Jendela pengingat sebelum batas jam pengembalian dalam menit (default: 30 menit)}
                            {--id= : ID peminjaman spesifik yang ingin diproses (opsional)}
                            {--all-day : Mengabaikan batasan jam dan memproses seluruh pengembalian hari ini}
                            {--dry-run : Menampilkan transaksi yang memenuhi kriteria tanpa mengirim email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim email pengingat pengembalian barang inventaris otomatis sebelum waktu pengembalian dan harian jika terlambat';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $timezone = config('app.timezone', 'Asia/Jakarta');
        $dateParam = $this->option('date');
        $timeParam = $this->option('time');
        $windowMinutes = (int) ($this->option('window') ?: 30);
        $specificId = $this->option('id');
        $isAllDay = (bool) $this->option('all-day');
        $isDryRun = (bool) $this->option('dry-run');

        $now = Carbon::now($timezone);
        $checkDate = $dateParam 
            ? Carbon::parse($dateParam, $timezone)->startOfDay() 
            : $now->copy()->startOfDay();

        $checkDateStr = $checkDate->toDateString();

        // Penentuan waktu pemeriksaan acuan (jam & menit)
        if ($timeParam) {
            $checkDateTime = Carbon::parse($checkDateStr . ' ' . $timeParam, $timezone);
        } elseif ($dateParam && $checkDateStr !== $now->toDateString()) {
            $checkDateTime = $checkDate->copy()->setHour(8)->setMinute(0)->setSecond(0);
        } else {
            $checkDateTime = $now->copy();
        }

        $this->info("=== EMAIL REMINDER PENGEMBALIAN BARANG INVENTARIS ===");
        $this->info("Waktu Pemeriksaan: " . $checkDateTime->format('Y-m-d H:i:s') . " ({$timezone})");
        $this->info("Tanggal Acuan Pemeriksaan: " . $checkDateStr);
        $this->info("Jendela Reminder Sebelum Pengembalian: {$windowMinutes} menit");
        if ($specificId) {
            $this->info("Filter ID Transaksi: {$specificId}");
        }
        if ($isAllDay) {
            $this->warn("[MODE ALL-DAY: Filter jam pengembalian dinonaktifkan]");
        }
        if ($isDryRun) {
            $this->warn("[MODE DRY-RUN AKTIF: Tidak ada email yang akan dikirim]");
        }

        // Kriteria Query:
        // 1. Status HANYA 'Sedang Dipinjam' (barang sudah diserahkan dan belum dikembalikan)
        // 2. Tanggal pengembalian sudah tiba atau terlewati (return_date <= checkDate)
        // 3. reminder_sent_at masih NULL (belum pernah) ATAU tanggalnya < checkDate (belum dikirim hari ini)
        $query = InventoryBorrowing::with(['user', 'details.item'])
            ->where('status', 'Sedang Dipinjam')
            ->whereDate('return_date', '<=', $checkDateStr)
            ->where(function ($q) use ($checkDateStr) {
                $q->whereNull('reminder_sent_at')
                  ->orWhereDate('reminder_sent_at', '<', $checkDateStr);
            })
            ->orderBy('id');

        if ($specificId) {
            $query->where('id', $specificId);
        }

        $totalMatching = $query->count();
        $this->info("Ditemukan {$totalMatching} transaksi berstatus 'Sedang Dipinjam' dengan return_date <= {$checkDateStr}.");

        if ($totalMatching === 0) {
            $this->info("Tidak ada transaksi inventaris yang memerlukan pengiriman reminder saat ini.");
            return Command::SUCCESS;
        }

        $sentCount = 0;
        $failedCount = 0;
        $skippedCount = 0;

        $query->chunkById(50, function ($borrowings) use (
            $isDryRun, 
            $checkDate, 
            $checkDateStr, 
            $checkDateTime, 
            $windowMinutes, 
            $isAllDay, 
            $timezone, 
            &$sentCount, 
            &$failedCount, 
            &$skippedCount
        ) {
            foreach ($borrowings as $borrowing) {
                // Ambil state data terbaru secara langsung dari database
                $current = InventoryBorrowing::with(['user', 'details.item'])->find($borrowing->id);

                if (!$current || $current->status !== 'Sedang Dipinjam') {
                    $statusNow = $current->status ?? 'NULL';
                    $this->warn("  -> SKIP [{$borrowing->request_code}]: Status bukan 'Sedang Dipinjam' (status saat ini: {$statusNow})");
                    $skippedCount++;
                    continue;
                }

                $recipientEmail = $current->user->email ?? null;
                $recipientName = $current->applicant_name ?? ($current->user->name ?? 'Pemohon');
                $requestCode = $current->request_code;
                $returnDateStr = $current->return_date ? $current->return_date->format('Y-m-d') : '-';
                $borrowingReturnTime = $current->return_time ? Carbon::parse($current->return_time)->format('H:i') : null;

                $itemNames = $current->details->map(function ($d) {
                    $name = $d->item->name ?? 'Barang Inventaris';
                    return "{$name} ({$d->quantity} Unit)";
                })->implode(', ');

                $logContext = [
                    'borrowing_id' => $current->id,
                    'request_code' => $requestCode,
                    'applicant_name' => $recipientName,
                    'recipient_email' => $recipientEmail,
                    'items' => $itemNames ?: 'Tidak ada detail barang',
                    'return_date' => $returnDateStr,
                    'return_time' => $borrowingReturnTime ? $borrowingReturnTime . ' WIB' : '-',
                    'checked_at' => $checkDateTime->format('Y-m-d H:i:s') . ' WIB',
                ];

                // Cek ulang apakah reminder sudah pernah dikirim pada tanggal pemeriksaan yang sama (Pencegahan Duplikasi)
                if ($current->reminder_sent_at) {
                    $sentDateStr = Carbon::parse($current->reminder_sent_at, $timezone)->toDateString();
                    if ($sentDateStr >= $checkDateStr) {
                        $this->warn("  -> SKIP [{$current->request_code}]: Reminder hari ini sudah terkirim pada {$current->reminder_sent_at}");
                        Log::info("Pemeriksaan reminder: Transaksi [{$requestCode}] dilewati (sudah pernah dikirim hari ini).", array_merge($logContext, [
                            'status' => 'DILEWATI_SUDAH_KIRIM_HARI_INI',
                            'reminder_sent_at' => $current->reminder_sent_at->format('Y-m-d H:i:s') . ' WIB',
                        ]));
                        $skippedCount++;
                        continue;
                    }
                }

                $this->line("--------------------------------------------------");
                $this->line("Memproses [{$requestCode}] - {$recipientName} (Return: {$returnDateStr}" . ($borrowingReturnTime ? " {$borrowingReturnTime} WIB" : "") . ")");

                // Validasi email penerima
                if (empty($recipientEmail) || !filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
                    $msg = "Transaksi [{$requestCode}] ID: {$current->id} dilewati: email penerima tidak valid atau kosong ('{$recipientEmail}').";
                    $this->warn("  -> SKIP: " . $msg);
                    Log::warning($msg, array_merge($logContext, [
                        'status' => 'DILEWATI_EMAIL_TIDAK_VALID',
                    ]));
                    $skippedCount++;
                    continue;
                }

                $isDueToday = ($returnDateStr === $checkDateStr);

                // Logika Waktu: Jika jatuh tempo hari ini dan memiliki jam pengembalian (return_time)
                if ($isDueToday && $borrowingReturnTime && !$isAllDay) {
                    $returnDateTime = Carbon::parse($checkDateStr . ' ' . $borrowingReturnTime, $timezone);

                    // Skenario 4: Jika waktu pengembalian telah terlewat pada waktu pemeriksaan
                    if ($checkDateTime->greaterThan($returnDateTime)) {
                        $msg = "Waktu pengembalian ({$borrowingReturnTime} WIB) telah terlewat pada waktu pemeriksaan ({$checkDateTime->format('H:i')} WIB). Reminder sebelum pengembalian tidak dikirim.";
                        $this->warn("  -> SKIP [{$requestCode}]: {$msg}");
                        Log::info("Pemeriksaan reminder: Transaksi [{$requestCode}] dilewati: {$msg}", array_merge($logContext, [
                            'status' => 'DILEWATI_WAKTU_LEWAT',
                        ]));
                        $skippedCount++;
                        continue;
                    }

                    // Skenario 1, 2, 3: Periksa apakah waktu pemeriksaan berada di dalam jendela reminder
                    // (returnDateTime - windowMinutes <= checkDateTime <= returnDateTime)
                    $windowStart = $returnDateTime->copy()->subMinutes($windowMinutes);
                    if ($checkDateTime->lessThan($windowStart)) {
                        $diffMinutes = $checkDateTime->diffInMinutes($returnDateTime, false);
                        $msg = "Belum masuk jendela reminder ({$diffMinutes} menit lagi sebelum {$borrowingReturnTime} WIB, jendela reminder: {$windowMinutes} menit).";
                        $this->warn("  -> SKIP [{$requestCode}]: {$msg}");
                        Log::info("Pemeriksaan reminder: Transaksi [{$requestCode}] dilewati: {$msg}", array_merge($logContext, [
                            'status' => 'DILEWATI_BELUM_MASUK_JENDELA',
                            'diff_minutes' => $diffMinutes,
                            'window_minutes' => $windowMinutes,
                        ]));
                        $skippedCount++;
                        continue;
                    }
                }

                if ($isDryRun) {
                    $this->info("  -> DRY-RUN: Siap kirim ke {$recipientEmail}");
                    $sentCount++;
                    continue;
                }

                try {
                    // Kirim email reminder menggunakan Mailable dengan checkDate
                    Mail::to($recipientEmail)->send(new InventoryReturnReminderMail($current, $checkDate));

                    // Perbarui reminder_sent_at dengan waktu pengiriman terbaru
                    $current->update([
                        'reminder_sent_at' => Carbon::now($timezone),
                    ]);

                    $successMsg = "Email reminder berhasil dikirim ke {$recipientEmail} untuk transaksi {$requestCode}.";
                    $this->info("  -> BERHASIL: " . $successMsg);
                    Log::info($successMsg, array_merge($logContext, [
                        'status' => 'BERHASIL_DIKIRIM',
                        'reminder_sent_at' => Carbon::now($timezone)->format('Y-m-d H:i:s') . ' WIB',
                    ]));
                    $sentCount++;
                } catch (\Throwable $e) {
                    // Jika gagal, jangan update reminder_sent_at dan catat ke Log
                    $errorMsg = "Gagal mengirim email reminder ke {$recipientEmail} untuk transaksi {$requestCode} (ID: {$current->id}): " . $e->getMessage();
                    $this->error("  -> GAGAL: " . $errorMsg);
                    Log::error($errorMsg, array_merge($logContext, [
                        'status' => 'GAGAL_DIKIRIM',
                        'failure_reason' => $e->getMessage(),
                    ]));
                    $failedCount++;
                }
            }
        });

        $this->line("==================================================");
        $this->info("Ringkasan Proses:");
        $this->info("- Berhasil dikirim : {$sentCount}");
        $this->info("- Gagal dikirim    : {$failedCount}");
        $this->info("- Dilewati (skip)  : {$skippedCount}");
        $this->info("==================================================");

        return $failedCount > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
