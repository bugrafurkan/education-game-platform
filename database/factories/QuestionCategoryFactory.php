<?php

namespace Database\Factories;

use App\Models\QuestionCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuestionCategoryFactory extends Factory
{
    protected $model = QuestionCategory::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word,
            'grade' => $this->faker->randomElement(['5', '6', '7', '8', '9']),
            'subject' => $this->faker->randomElement(['Matematik', 'Fen', 'Türkçe', 'İngilizce']),
            'unit' => $this->faker->sentence(2),
            'description' => $this->faker->paragraph,
        ];
    }
}
