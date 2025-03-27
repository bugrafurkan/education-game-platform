<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('category');
            $table->string('key');
            $table->text('value')->nullable();
            $table->timestamps();

            $table->unique(['category', 'key']);
        });

        // Varsayılan ayarları ekle
        $this->seedDefaultSettings();
    }

    public function down()
    {
        Schema::dropIfExists('settings');
    }

    private function seedDefaultSettings()
    {
        $settings = [
            // Genel ayarlar
            ['category' => 'general', 'key' => 'app_name', 'value' => 'Eğitim Oyun Platformu'],
            ['category' => 'general', 'key' => 'logo_url', 'value' => '/assets/logo.png'],
            ['category' => 'general', 'key' => 'theme_color', 'value' => '#1a1a27'],

            // Fernus ayarları
            ['category' => 'fernus', 'key' => 'fernus_enabled', 'value' => 'true'],
            ['category' => 'fernus', 'key' => 'fernus_api_key', 'value' => ''],
            ['category' => 'fernus', 'key' => 'fernus_api_url', 'value' => 'https://api.fernus.example.com'],

            // Reklam ayarları
            ['category' => 'advertisements', 'key' => 'ads_enabled', 'value' => 'true'],
            ['category' => 'advertisements', 'key' => 'default_banner', 'value' => '/assets/default-banner.png'],
        ];

        DB::table('settings')->insert($settings);
    }
};
