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
}
