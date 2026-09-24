<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryBorrowing extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'request_code',
        'applicant_name',
        'nik',
        'phone',
        'institution',
        'purpose',
        'penanggung_jawab',
        'borrow_date',
        'return_date',
        'notes',
        'application_file',
        'status',
        'rejection_reason',
    ];

    protected $casts = [
        'borrow_date' => 'date',
        'return_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function details()
    {
        return $this->hasMany(InventoryBorrowingDetail::class, 'borrowing_id');
    }
}
