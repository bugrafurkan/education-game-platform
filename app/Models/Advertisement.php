<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Advertisement extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type', // 'image' veya 'video'
        'file_path',
        'file_url',
        'is_active',
        'grade',
        'subject',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
