<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Export extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'game_id',
        'version',
        'status',
        'download_url',
        'uploaded_to_fernus',
        'fernus_url',
        'created_by',
        'config_snapshot',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'uploaded_to_fernus' => 'boolean',
        'config_snapshot' => 'array',
    ];

    /**
     * Export edilen oyun
     */
    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    /**
     * Export işlemini başlatan kullanıcı
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Konfigürasyon dosyasını oluştur
     */
    public function generateConfigFile()
    {
        // Config dosyasını storage'a kaydet
        $config = $this->config_snapshot ?: $this->game->generateJsonConfig();
        $fileName = "game_{$this->game_id}_v{$this->version}.json";

        // Storage path
        $path = storage_path("app/exports/{$fileName}");

        // Klasör yoksa oluştur
        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        // Dosyayı kaydet
        file_put_contents($path, $config);

        return $path;
    }

    /**
     * Fernus'a yükleme işlemi
     */
    public function uploadToFernus()
    {
        // TODO: Fernus API entegrasyonu
        // Bu örnek bir simulasyon

        $this->update([
            'uploaded_to_fernus' => true,
            'fernus_url' => "https://fernus.example.com/games/{$this->game_id}/v{$this->version}",
            'status' => 'completed'
        ]);

        return true;
    }
}
