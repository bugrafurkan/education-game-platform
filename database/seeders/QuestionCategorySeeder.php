<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuestionCategorySeeder extends Seeder
{
    public function run()
    {
        $this->createGrades();
        $this->createSubjects();
        $this->createUnits();
        $this->createTopics();
    }

    private function createGrades()
    {
        $grades = ['1-4', '5-8', '9-12', 'Üniversite', 'Anaokulu'];

        foreach ($grades as $index => $grade) {
            DB::table('grades')->updateOrInsert(
                ['id' => $index + 1],
                [
                    'name' => $grade,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    private function createSubjects()
    {
        $subjects = [
            'Matematik', 'Fen Bilgisi', 'Sosyal Bilgiler', 'Türkçe',
            'Yabancı Dil', 'Değerler', 'İngilizce', 'Tarih', 'Fizik',
            'özel', 'Çok özel'
        ];

        foreach ($subjects as $index => $subject) {
            DB::table('subjects')->updateOrInsert(
                ['id' => $index + 1],
                [
                    'name' => $subject,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    private function createUnits()
    {
        $units = [
            'Cebir', 'Fizik', 'Türkiye Cumhuriyeti Tarihi', 'Dilbilgisi',
            'Gramer', 'Doğal Taşlar', 'Bölüm 3 doğal sayılar', 'taş'
        ];

        foreach ($units as $index => $unit) {
            DB::table('units')->updateOrInsert(
                ['id' => $index + 1],
                [
                    'name' => $unit,
                    'grade_id' => 1,      // geçici
                    'subject_id' => 1,    // geçici
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    private function createTopics()
    {
        $topics = ['Temel Konular', 'İleri Konular', 'Özel Konular'];

        foreach ($topics as $index => $topic) {
            DB::table('topics')->updateOrInsert(
                ['id' => $index + 1],
                [
                    'name' => $topic,
                    'unit_id' => 1, // geçici
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

}
