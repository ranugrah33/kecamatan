<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Aula extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'nama',
        'lokasi',
        'kapasitas',
        'fasilitas',
        'jam_buka',
        'jam_tutup',
        'ketentuan'
    ];

    protected $casts = [
        'fasilitas' => 'array',
    ];

    public function peminjamanAulas()
    {
        return $this->hasMany(PeminjamanAula::class);
    }
}
