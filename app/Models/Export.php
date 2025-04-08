<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Export extends Model
{
    protected $fillable = [
        'question_group_id',
        'game_id',
        'status',
        'output_url',
        'config_snapshot',
        'requested_at',
        'completed_at',
        'error_message',
    ];

    protected $casts = [
        'config_snapshot' => 'array',
        'requested_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function questionGroup()
    {
        return $this->belongsTo(QuestionGroup::class);
    }
}
