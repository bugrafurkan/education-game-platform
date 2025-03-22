<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'category_id',
        'question_text',
        'question_type',
        'difficulty',
        'image_path',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * Sorunun ait olduğu kategori
     */
    public function category()
    {
        return $this->belongsTo(QuestionCategory::class, 'category_id');
    }

    /**
     * Sorunun cevapları
     */
    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    /**
     * Sorunun bulunduğu oyunlar
     */
    public function games()
    {
        return $this->belongsToMany(Game::class, 'game_questions')
            ->withPivot('points', 'order', 'category_label', 'special_effects')
            ->withTimestamps();
    }

    /**
     * Doğru cevap
     */
    public function correctAnswer()
    {
        return $this->answers()->where('is_correct', true)->first();
    }

    /**
     * Belirli kriterlere göre soruları filtreleme
     */
    public function scopeFilter($query, $type = null, $difficulty = null, $category = null)
    {
        if ($type) {
            $query->where('question_type', $type);
        }

        if ($difficulty) {
            $query->where('difficulty', $difficulty);
        }

        if ($category) {
            $query->where('category_id', $category);
        }

        return $query;
    }
}
