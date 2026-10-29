<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PeminjamanAula extends Model
{
    use HasFactory;

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
        'file_surat_final'
    ];

    protected $casts = [
        'fasilitas_dibutuhkan' => 'array',
        'tanggal' => 'date',
    ];

    protected static function booted()
    {
        static::retrieved(function ($peminjaman) {
            $peminjaman->checkAndUpdateFinishedStatus();
        });
    }

    /**
     * Otomatis update status pengajuan yang telah disetujui menjadi 'Selesai'
     * jika tanggal dan jam_selesai + 30 menit jeda toleransi telah terlewati.
     * Misal: jam_selesai 12:00, maka sistem akan mengubahnya menjadi Selesai pada pukul 12:30.
     */
    public static function updateExpiredToSelesai()
    {
        // Spare waktu 30 menit: CONCAT(tanggal, ' ', jam_selesai) <= now() - 30 menit
        $threshold = now()->subMinutes(30)->format('Y-m-d H:i:s');

        return static::whereIn('status', ['Disetujui', 'Surat Tersedia'])
            ->whereRaw("CONCAT(tanggal, ' ', jam_selesai) <= ?", [$threshold])
            ->update([
                'status' => 'Selesai',
                'updated_at' => now(),
            ]);
    }

    /**
     * Cek apakah data peminjaman aula ini sudah melewati jam selesai + 30 menit.
     * Jika ya, perbarui status menjadi 'Selesai'.
     */
    public function checkAndUpdateFinishedStatus()
    {
        if (in_array($this->status, ['Disetujui', 'Surat Tersedia'])) {
            $tanggalStr = $this->tanggal instanceof \Carbon\Carbon
                ? $this->tanggal->format('Y-m-d')
                : substr((string)$this->tanggal, 0, 10);
            
            $jamSelesai = strlen($this->jam_selesai) == 5 ? $this->jam_selesai . ':00' : $this->jam_selesai;
            $waktuSelesaiPlusJeda = \Carbon\Carbon::parse($tanggalStr . ' ' . $jamSelesai)->addMinutes(30);

            if (now()->greaterThanOrEqualTo($waktuSelesaiPlusJeda)) {
                \Illuminate\Support\Facades\DB::table('peminjaman_aulas')
                    ->where('id', $this->id)
                    ->update([
                        'status' => 'Selesai',
                        'updated_at' => now(),
                    ]);
                $this->status = 'Selesai';
            }
        }
        return $this;
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
