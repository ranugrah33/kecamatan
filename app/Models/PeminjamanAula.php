<?php

namespace App\Models;

use App\Mail\PeminjamanAulaSuratFinalMail;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class PeminjamanAula extends Model
{
    use HasFactory;

    // 6 Status Resmi Baru
    public const STATUS_MENUNGGU_VERIFIKASI = 'Menunggu Verifikasi';
    public const STATUS_PERLU_PERBAIKAN = 'Perlu Perbaikan';
    public const STATUS_SURAT_DIPROSES = 'Surat Diproses';
    public const STATUS_SURAT_TERSEDIA = 'Surat Tersedia';
    public const STATUS_SELESAI = 'Selesai';
    public const STATUS_DITOLAK = 'Ditolak';

    // Status Legacy (kompatibilitas data lama)
    public const STATUS_DISETUJUI = 'Disetujui';
    public const STATUS_DIPROSES = 'Diproses';
    public const STATUS_DIBATALKAN = 'Dibatalkan';

    protected $fillable = [
        'user_id',
        'aula_id',
        'nomor_pengajuan',
        'instansi',
        'penanggung_jawab',
        'nama_kegiatan',
        'jenis_kegiatan',
        'deskripsi_kegiatan',
        'jumlah_peserta',
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'fasilitas_dibutuhkan',
        'catatan_tambahan',
        'status',
        'catatan_petugas',
        'nomor_surat_permohonan',
        'file_surat_permohonan',
        'nomor_surat_izin',
        'file_surat_final',
        'surat_notified_at',
    ];

    protected $casts = [
        'fasilitas_dibutuhkan' => 'array',
        'tanggal' => 'date',
        'surat_notified_at' => 'datetime',
    ];

    /**
     * Daftar 6 pilihan status yang disederhanakan beserta label tampilan administrasinya.
     */
    public static function getSimplifiedStatuses(): array
    {
        return [
            self::STATUS_MENUNGGU_VERIFIKASI => 'Menunggu Verifikasi',
            self::STATUS_PERLU_PERBAIKAN => 'Perlu Perbaikan',
            self::STATUS_SURAT_DIPROSES => 'Surat Diproses (Draft)',
            self::STATUS_SURAT_TERSEDIA => 'Surat Tersedia (Final)',
            self::STATUS_SELESAI => 'Selesai (Kegiatan Rampung)',
            self::STATUS_DITOLAK => 'Ditolak',
        ];
    }

    /**
     * Dapatkan label yang user-friendly untuk suatu status.
     */
    public function getStatusLabelAttribute(): string
    {
        $statuses = self::getSimplifiedStatuses();
        if (isset($statuses[$this->status])) {
            return $statuses[$this->status];
        }

        // Penanganan status legacy
        return match ($this->status) {
            self::STATUS_DISETUJUI => 'Setujui Pengajuan (Legacy)',
            self::STATUS_DIPROSES => 'Diproses (Legacy)',
            self::STATUS_DIBATALKAN => 'Dibatalkan (Legacy)',
            default => (string) $this->status,
        };
    }

    protected static function booted()
    {
        static::retrieved(function ($peminjaman) {
            $peminjaman->checkAndUpdateFinishedStatus();
        });
    }

    /**
     * Otomatis update status pengajuan yang telah disetujui/surat tersedia menjadi 'Selesai'
     * jika tanggal dan jam_selesai + 30 menit jeda toleransi telah terlewati.
     */
    public static function updateExpiredToSelesai()
    {
        // Spare waktu 30 menit: CONCAT(tanggal, ' ', jam_selesai) <= now() - 30 menit
        $threshold = now()->subMinutes(30)->format('Y-m-d H:i:s');

        return static::whereIn('status', [self::STATUS_SURAT_TERSEDIA, self::STATUS_DISETUJUI])
            ->whereRaw("CONCAT(tanggal, ' ', jam_selesai) <= ?", [$threshold])
            ->update([
                'status' => self::STATUS_SELESAI,
                'updated_at' => now(),
            ]);
    }

    /**
     * Cek apakah data peminjaman aula ini sudah melewati jam selesai + 30 menit.
     * Jika ya, perbarui status menjadi 'Selesai'.
     */
    public function checkAndUpdateFinishedStatus()
    {
        if (in_array($this->status, [self::STATUS_SURAT_TERSEDIA, self::STATUS_DISETUJUI])) {
            $tanggalStr = $this->tanggal instanceof Carbon
                ? $this->tanggal->format('Y-m-d')
                : substr((string)$this->tanggal, 0, 10);
            
            $rawJam = (string) ($this->jam_selesai ?? '');
            $jamSelesai = strlen($rawJam) === 5 ? $rawJam . ':00' : $rawJam;

            if (!empty($tanggalStr) && !empty($jamSelesai)) {
                $waktuSelesaiPlusJeda = Carbon::parse($tanggalStr . ' ' . $jamSelesai)->addMinutes(30);

                if (now()->greaterThanOrEqualTo($waktuSelesaiPlusJeda)) {
                    DB::table('peminjaman_aulas')
                        ->where('id', $this->id)
                        ->update([
                            'status' => self::STATUS_SELESAI,
                            'updated_at' => now(),
                        ]);
                    $this->status = self::STATUS_SELESAI;
                }
            }
        }
        return $this;
    }

    /**
     * Kirim email notifikasi surat izin final otomatis hanya jika:
     * 1. Status pengajuan adalah 'Surat Tersedia'
     * 2. Berkas file_surat_final berhasil disimpan & ada pada storage publik
     * 3. Belum pernah dinotifikasikan sebelumnya (surat_notified_at IS NULL)
     */
    public function sendSuratFinalNotificationIfNeeded(): bool
    {
        if ($this->status !== self::STATUS_SURAT_TERSEDIA) {
            return false;
        }

        if (empty($this->file_surat_final) || !Storage::disk('public')->exists($this->file_surat_final)) {
            return false;
        }

        if ($this->surat_notified_at !== null) {
            return false;
        }

        $recipientEmail = $this->user->email ?? null;
        if (empty($recipientEmail) || !filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
            Log::warning("Pengiriman email notifikasi surat izin aula dilewati: email pemohon kosong atau tidak valid pada pengajuan {$this->nomor_pengajuan} (User ID: {$this->user_id}).");
            return false;
        }

        try {
            Mail::to($recipientEmail)->send(new PeminjamanAulaSuratFinalMail($this));

            $this->update(['surat_notified_at' => now()]);
            Log::info("Email notifikasi surat izin aula {$this->nomor_pengajuan} berhasil dikirim ke {$recipientEmail}.");
            return true;
        } catch (\Throwable $e) {
            Log::error("Gagal mengirim email notifikasi surat izin aula {$this->nomor_pengajuan} ke {$recipientEmail}: " . $e->getMessage());
            return false;
        }
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function aula()
    {
        return $this->belongsTo(Aula::class);
    }

    public function dokumens()
    {
        return $this->hasMany(PeminjamanAulaDokumen::class);
    }
}
