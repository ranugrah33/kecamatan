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
        'applicant_name',
        'nik',
        'phone',
        'document_type',
        'activity_name',
        'activity_theme',
        'activity_date',
        'activity_place',
        'purpose',
        'description',
        'application_file',
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
