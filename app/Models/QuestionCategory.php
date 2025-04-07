<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionCategory extends Model
{
    protected $fillable = [
        'name',
        'grade_id',
        'subject_id',
        'unit_id',
        'topic_id',
    ];

    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class, 'category_id');
    }

    /**
     * Filtreleme scope'u – yeni ID yapısına göre.
     */
    public function scopeFilter($query, $gradeId = null, $subjectId = null)
    {
        if ($gradeId) {
            $query->where('grade_id', $gradeId);
        }

        if ($subjectId) {
            $query->where('subject_id', $subjectId);
        }

        return $query;
    }
}
