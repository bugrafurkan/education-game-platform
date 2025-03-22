<?php

namespace Database\Factories;

use App\Models\Answer;
use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

class AnswerFactory extends Factory
{
    protected $model = Answer::class;

    public function definition()
    {
        return [
            'question_id' => Question::factory(),
            'answer_text' => $this->faker->sentence,
            'is_correct' => $this->faker->boolean(25),
            'image_path' => null,
        ];
    }
}
