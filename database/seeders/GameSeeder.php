<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Game;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class GameSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Veritabanı işlemlerini temiz tutmak için oyun tablosunu temizle
        // DB::table('games')->truncate(); // Mevcut verileri korumak için kapatıldı

        // Admin kullanıcısını bul (varsayılan olarak ilk admin kullanıcıyı al)
        $admin = User::where('role', 'admin')->first();

        if (!$admin) {
            // Admin bulunamazsa, seeder başarısız olur
            $this->command->error('Admin kullanıcısı bulunamadı! Önce UserSeeder çalıştırın.');
            return;
        }

        // Oyunları oluştur
        $games = [
            [
                'name' => 'Matematik Jeopardy',
                'type' => 'jeopardy',
                'description' => 'Matematik konularını içeren Jeopardy tarzı bir bilgi yarışması.',
                'is_active' => true,
                'config' => json_encode([
                    'time_limit' => 60,
                    'points_multiplier' => 1,
                    'team_mode' => true
                ]),
                'created_by' => $admin->id
            ],
            [
                'name' => 'Fen Bilgisi Çarkı',
                'type' => 'wheel',
                'description' => 'Fen bilgisi konularını içeren Bilgi Çarkı tarzı bir oyun.',
                'is_active' => true,
                'config' => json_encode([
                    'time_limit' => 30,
                    'points_multiplier' => 2,
                    'team_mode' => false
                ]),
                'created_by' => $admin->id
            ],
            [
                'name' => 'Tarih Jeopardy',
                'type' => 'jeopardy',
                'description' => 'Türkiye ve Dünya tarihi konularını içeren Jeopardy tarzı bir bilgi yarışması.',
                'is_active' => true,
                'config' => json_encode([
                    'time_limit' => 45,
                    'points_multiplier' => 1.5,
                    'team_mode' => true
                ]),
                'created_by' => $admin->id
            ],
        ];

        // firstOrCreate kullanarak, verileri tekrar eklemeden varlık kontrolü yapılır
        foreach ($games as $gameData) {
            Game::firstOrCreate(
                ['name' => $gameData['name']], // Kontrol edilecek alan
                $gameData // Eğer yoksa eklenecek tüm veriler
            );
        }
    }
}
