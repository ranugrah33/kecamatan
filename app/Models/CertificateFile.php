<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CertificateFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'certificate_request_id',
        'file_path',
        'original_filename',
        'uploaded_by',
    ];

    public function certificateRequest()
    {
        return $this->belongsTo(CertificateRequest::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
