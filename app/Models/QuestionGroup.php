<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class QuestionGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'question_type',
        'game_id',
        'created_by',
    ];

    /**
     * Model oluşturulurken benzersiz kod üret
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($group) {
            // Eğer kod belirtilmemişse, otomatik üret
            if (!$group->code) {
                $group->code = self::generateUniqueCode();
            }
        });
    }

    /**
     * Benzersiz kod üretme
     */
    public static function generateUniqueCode()
    {
        $code = Str::random(8); // 8 karakterlik rastgele bir kod

        // Kod benzersiz olana kadar yeni kodlar üretmeye devam et
        while (self::where('code', $code)->exists()) {
            $code = Str::random(8);
        }

        return $code;
    }

    /**
     * Oyun ilişkisi
     */
    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    /**
     * Oluşturan kullanıcı ilişkisi
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Sorular ilişkisi
     */
    public function questions()
    {
        return $this->belongsToMany(Question::class, 'question_group_questions')
            ->withPivot('order')
            ->withTimestamps()
            ->orderBy('order');
    }
}
