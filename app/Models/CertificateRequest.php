<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CertificateRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'request_code',
        'nomor_sertifikat',
        'applicant_name',
        'certificate_name',
        'phone',
        'document_type',
        'activity_name',
        'activity_theme',
        'activity_date',
        'activity_place',
        'certificate_text',
        'purpose',
        'description',
        'application_file',
        'file_sertifikat_final',
        'status',
        'rejection_reason',
    ];

    protected $casts = [
        'activity_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function files()
    {
        return $this->hasMany(CertificateFile::class);
    }

    /**
     * Get the latest uploaded certificate file.
     */
    public function latestFile()
    {
        return $this->hasOne(CertificateFile::class)->latestOfMany();
    }
}
