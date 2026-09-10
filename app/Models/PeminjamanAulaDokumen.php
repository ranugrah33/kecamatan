<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PeminjamanAulaDokumen extends Model
{
    use HasFactory;

    protected $fillable = [
        'peminjaman_aula_id',
        'nama_dokumen',
        'path_dokumen'
    ];

    public function peminjamanAula()
    {
        return $this->belongsTo(PeminjamanAula::class);
    }
}
