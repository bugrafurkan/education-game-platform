<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\QuestionCategory;
use Illuminate\Support\Facades\DB;

class QuestionCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Veritabanı işlemlerini temiz tutmak için kategori tablosunu temizle
        //DB::table('question_categories')->truncate();

        // Kategorileri oluştur
        $categories = [
            [
                'name' => 'Matematik',
                'grade' => '5-8',
                'subject' => 'Matematik',
                'unit' => 'Cebir'
            ],
            [
                'name' => 'Fen Bilgisi',
                'grade' => '5-8',
                'subject' => 'Fen Bilgisi',
                'unit' => 'Fizik'
            ],
            [
                'name' => 'Tarih',
                'grade' => '9-12',
                'subject' => 'Sosyal Bilgiler',
                'unit' => 'Türkiye Cumhuriyeti Tarihi'
            ],
            [
                'name' => 'Türkçe',
                'grade' => '5-8',
                'subject' => 'Türkçe',
                'unit' => 'Dilbilgisi'
            ],
            [
                'name' => 'İngilizce',
                'grade' => '5-8',
                'subject' => 'Yabancı Dil',
                'unit' => 'Gramer'
            ],
        ];

        foreach ($categories as $category) {
            QuestionCategory::create($category);
        }
    }
}
