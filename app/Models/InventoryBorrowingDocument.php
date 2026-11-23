<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryBorrowingDocument extends Model
{
    use HasFactory;

    public const TYPE_DRAFT = 'draft';
    public const TYPE_FINAL = 'final';

    public const STATUS_FINAL_AVAILABLE = 'Surat Final Tersedia';
    public const STATUS_REPLACED = 'replaced';
    public const STATUS_ARCHIVED = 'archived';
    public const STATUS_DRAFT = 'draft';

    protected $fillable = [
        'inventory_borrowing_id',
        'document_type',
        'file_path',
        'file_name',
        'document_status',
        'is_active',
        'version',
        'nomor_surat',
        'uploaded_by',
        'notified_at',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'version' => 'integer',
        'notified_at' => 'datetime',
    ];

    public function borrowing()
    {
        return $this->belongsTo(InventoryBorrowing::class, 'inventory_borrowing_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
