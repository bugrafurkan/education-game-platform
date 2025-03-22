<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'type',
        'config',
        'description',
        'created_by',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'config' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Oyunu oluşturan kullanıcı
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Oyundaki sorular
     */
    public function questions()
    {
        return $this->belongsToMany(Question::class, 'game_questions')
            ->withPivot('points', 'order', 'category_label', 'special_effects')
            ->withTimestamps();
    }

    /**
     * Oyun exportları
     */
    public function exports()
    {
        return $this->hasMany(Export::class);
    }

    /**
     * Oyun türüne göre filtreleme
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Aktif oyunları filtreleme
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * JSON yapılandırma dosyasını oluştur
     */
    public function generateJsonConfig()
    {
        $config = [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'settings' => $this->config,
            'questions' => []
        ];

        // Soruları ve cevapları ekle
        foreach ($this->questions()->with('answers')->get() as $question) {
            $questionData = [
                'id' => $question->id,
                'text' => $question->question_text,
                'type' => $question->question_type,
                'image' => $question->image_path,
                'points' => $question->pivot->points,
                'category_label' => $question->pivot->category_label,
                'special_effects' => json_decode($question->pivot->special_effects),
                'answers' => []
            ];

            foreach ($question->answers as $answer) {
                $questionData['answers'][] = [
                    'id' => $answer->id,
                    'text' => $answer->answer_text,
                    'is_correct' => $answer->is_correct,
                    'image' => $answer->image_path
                ];
            }

            $config['questions'][] = $questionData;
        }

        return json_encode($config, JSON_PRETTY_PRINT);
    }
}
