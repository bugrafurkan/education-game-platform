<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Advertisement extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'type',
        'media_path',
        'target_grade',
        'target_subject',
        'target_game_type',
        'link_url',
        'start_date',
        'end_date',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Aktif reklamları filtreleme
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function($q) {
                $q->whereNull('start_date')
                    ->orWhere('start_date', '<=', now());
            })
            ->where(function($q) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            });
    }

    /**
     * Belirli kriterlere göre reklamları filtreleme
     */
    public function scopeTargeted($query, $grade = null, $subject = null, $gameType = null)
    {
        if ($grade) {
            $query->where(function($q) use ($grade) {
                $q->whereNull('target_grade')
                    ->orWhere('target_grade', $grade);
            });
        }

        if ($subject) {
            $query->where(function($q) use ($subject) {
                $q->whereNull('target_subject')
                    ->orWhere('target_subject', $subject);
            });
        }

        if ($gameType) {
            $query->where(function($q) use ($gameType) {
                $q->whereNull('target_game_type')
                    ->orWhere('target_game_type', $gameType);
            });
        }

        return $query;
    }
}
