<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class QuestionGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'question_type',
        'game_id',
        'category_id', // Yeni kategori ID'si eklendi
        'created_by',
        'image_path', // Görsel yolu eklendi
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'image_url',
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
     * Görsel URL'sini döndüren erişimci
     *
     * @return string|null
     */
    public function getImageUrlAttribute()
    {
        if (!$this->image_path) {
            return null;
        }

        // Eğer image_path zaten tam bir URL ise
        if (filter_var($this->image_path, FILTER_VALIDATE_URL)) {
            return $this->image_path;
        }

        // Eğer image_path 'public/' ile başlıyorsa
        if (strpos($this->image_path, 'public/') === 0) {
            return Storage::url(str_replace('public/', '', $this->image_path));
        }

        // Değilse normal yolla URL oluştur
        return Storage::url($this->image_path);
    }

    /**
     * Oyun ilişkisi
     */
    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    /**
     * Kategori ilişkisi
     */
    public function category()
    {
        return $this->belongsTo(QuestionCategory::class, 'category_id');
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
