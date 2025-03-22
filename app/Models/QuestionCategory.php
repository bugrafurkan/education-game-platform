<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionCategory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'grade',
        'subject',
        'unit',
        'description',
    ];

    /**
     * Kategorideki sorular
     */
    public function questions()
    {
        return $this->hasMany(Question::class, 'category_id');
    }

    /**
     * Belirli bir sınıf ve derse göre kategorileri filtreleme
     */
    public function scopeFilter($query, $grade = null, $subject = null)
    {
        if ($grade) {
            $query->where('grade', $grade);
        }

        if ($subject) {
            $query->where('subject', $subject);
        }

        return $query;
    }
}
