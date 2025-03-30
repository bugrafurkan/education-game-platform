<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,             // Önce kullanıcıları oluştur
            QuestionCategorySeeder::class, // Sonra kategorileri oluştur
            GameSeeder::class,             // Sonra oyunları oluştur
        ]);
    }
}
