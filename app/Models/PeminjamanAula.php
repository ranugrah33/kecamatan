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
        'catatan_petugas'
    ];

    protected $casts = [
        'fasilitas_dibutuhkan' => 'array',
        'tanggal' => 'date',
    ];

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
