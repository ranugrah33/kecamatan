<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiKnowledge extends Model
{
    use HasFactory;

    protected $table = 'ai_knowledges';

    protected $fillable = [
        'category',
        'title',
        'question',
        'answer',
        'keywords',
        'priority',
        'is_active',
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
    ];
}
