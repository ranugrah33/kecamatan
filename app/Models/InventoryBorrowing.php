<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\InventoryBorrowingApprovedMail;
use App\Mail\InventoryBorrowingRejectedMail;

class InventoryBorrowing extends Model
{
    use HasFactory;

    // 5 Status Baru Peminjaman Barang Inventaris
    public const STATUS_MENUNGGU_PERSETUJUAN = 'Menunggu Persetujuan';
    public const STATUS_DISETUJUI = 'Disetujui';
    public const STATUS_DITOLAK = 'Ditolak';
    public const STATUS_SEDANG_DIPINJAM = 'Sedang Dipinjam';
    public const STATUS_SELESAI = 'Selesai';

    // Status Legacy (untuk kompatibilitas data lama)
    public const STATUS_DIAJUKAN = 'Diajukan';
    public const STATUS_DIPROSES = 'Diproses';
    public const STATUS_DIKEMBALIKAN = 'Dikembalikan';

    public const STATUSES = [
        self::STATUS_MENUNGGU_PERSETUJUAN,
        self::STATUS_DISETUJUI,
        self::STATUS_DITOLAK,
        self::STATUS_SEDANG_DIPINJAM,
        self::STATUS_SELESAI,
    ];

    /**
     * Pemetaan status lama ke status baru
     */
    public static function mapLegacyStatus(?string $status): ?string
    {
        return match ($status) {
            'Diajukan', 'Diproses' => self::STATUS_MENUNGGU_PERSETUJUAN,
            'Dikembalikan' => self::STATUS_SELESAI,
            default => $status,
        };
    }

    /**
     * Cek apakah status merupakan status akhir (final)
     */
    public function isFinalStatus(): bool
    {
        return in_array($this->status, [self::STATUS_DITOLAK, self::STATUS_SELESAI, 'Dikembalikan']);
    }

    /**
     * Label status yang disederhanakan untuk tampilan (backward-compatible)
     */
    public function getStatusLabelAttribute(): string
    {
        return self::mapLegacyStatus($this->status) ?? self::STATUS_MENUNGGU_PERSETUJUAN;
    }

    /**
     * Konfigurasi warna badge status
     */
    public function getStatusBadgeColorAttribute(): string
    {
        return match ($this->status_label) {
            self::STATUS_MENUNGGU_PERSETUJUAN => 'blue',
            self::STATUS_DISETUJUI => 'emerald',
            self::STATUS_SEDANG_DIPINJAM => 'amber',
            self::STATUS_DITOLAK => 'rose',
            self::STATUS_SELESAI => 'emerald',
            default => 'gray',
        };
    }

    protected $fillable = [
        'user_id',
        'request_code',
        'applicant_name',
        'phone',
        'institution',
        'purpose',
        'penanggung_jawab',
        'borrow_date',
        'borrowing_time',
        'return_date',
        'return_time',
        'notes',
        'application_file',
        'status',
        'rejection_reason',
        'reminder_sent_at',
    ];

    protected $casts = [
        'borrow_date' => 'date',
        'return_date' => 'date',
        'reminder_sent_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function details()
    {
        return $this->hasMany(InventoryBorrowingDetail::class, 'borrowing_id');
    }

    /**
     * Tampilan jam peminjaman format 24 jam WIB (contoh: 08:00 WIB)
     */
    public function getFormattedBorrowingTimeAttribute(): string
    {
        if (empty($this->borrowing_time)) {
            return '-';
        }
        return \Carbon\Carbon::parse($this->borrowing_time)->format('H:i') . ' WIB';
    }

    /**
     * Tampilan jam pengembalian format 24 jam WIB (contoh: 15:30 WIB)
     */
    public function getFormattedReturnTimeAttribute(): string
    {
        if (empty($this->return_time)) {
            return '-';
        }
        return \Carbon\Carbon::parse($this->return_time)->format('H:i') . ' WIB';
    }

    /**
     * Nilai jam peminjaman format HH:mm 24 jam murni
     */
    public function getBorrowingTime24Attribute(): ?string
    {
        if (empty($this->borrowing_time)) {
            return null;
        }
        return \Carbon\Carbon::parse($this->borrowing_time)->format('H:i');
    }

    /**
     * Nilai jam pengembalian format HH:mm 24 jam murni
     */
    public function getReturnTime24Attribute(): ?string
    {
        if (empty($this->return_time)) {
            return null;
        }
        return \Carbon\Carbon::parse($this->return_time)->format('H:i');
    }

    /**
     * Waktu pengajuan dalam format Indonesia WIB (contoh: 03 November 2026, 10:30 WIB)
     */
    public function getFormattedCreatedAtAttribute(): string
    {
        if (empty($this->created_at)) {
            return '-';
        }
        return $this->created_at->translatedFormat('d F Y, H:i') . ' WIB';
    }

    /**
     * Kirim email notifikasi persetujuan peminjaman barang inventaris ke pemohon
     */
    public function sendApprovalNotification(): bool
    {
        $this->loadMissing(['user', 'details.item']);

        $recipientEmail = $this->user->email ?? null;
        if (empty($recipientEmail) || !filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
            Log::warning("Pengiriman email persetujuan inventaris dilewati: email pemohon kosong atau tidak valid pada transaksi {$this->request_code} (ID: {$this->id}, User ID: {$this->user_id}).");
            return false;
        }

        try {
            Mail::to($recipientEmail)->send(new InventoryBorrowingApprovedMail($this));
            Log::info("Email persetujuan peminjaman inventaris [{$this->request_code}] berhasil dikirim ke {$recipientEmail}.");
            return true;
        } catch (\Throwable $e) {
            Log::error("Gagal mengirim email persetujuan peminjaman inventaris [{$this->request_code}] ke {$recipientEmail}: " . $e->getMessage(), [
                'borrowing_id' => $this->id,
                'request_code' => $this->request_code,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Kirim email notifikasi penolakan peminjaman barang inventaris ke pemohon
     */
    public function sendRejectionNotification(): bool
    {
        $this->loadMissing(['user', 'details.item']);

        $recipientEmail = $this->user->email ?? null;
        if (empty($recipientEmail) || !filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
            Log::warning("Pengiriman email penolakan inventaris dilewati: email pemohon kosong atau tidak valid pada transaksi {$this->request_code} (ID: {$this->id}, User ID: {$this->user_id}).");
            return false;
        }

        try {
            Mail::to($recipientEmail)->send(new InventoryBorrowingRejectedMail($this));
            Log::info("Email penolakan peminjaman inventaris [{$this->request_code}] berhasil dikirim ke {$recipientEmail}.");
            return true;
        } catch (\Throwable $e) {
            Log::error("Gagal mengirim email penolakan peminjaman inventaris [{$this->request_code}] ke {$recipientEmail}: " . $e->getMessage(), [
                'borrowing_id' => $this->id,
                'request_code' => $this->request_code,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
