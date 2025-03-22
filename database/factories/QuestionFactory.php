<?php

namespace Database\Factories;

use App\Models\Question;
use App\Models\QuestionCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuestionFactory extends Factory
{
    protected $model = Question::class;

    public function definition()
    {
        return [
            'category_id' => QuestionCategory::factory(),
            'question_text' => $this->faker->sentence(6, true) . '?',
            'question_type' => $this->faker->randomElement(['multiple_choice', 'true_false', 'qa']),
            'difficulty' => $this->faker->randomElement(['easy', 'medium', 'hard']),
            'image_path' => null,
            'metadata' => null,
        ];
    }
}
