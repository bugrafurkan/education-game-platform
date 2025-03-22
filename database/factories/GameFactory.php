<?php

namespace Database\Factories;

use App\Models\Game;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class GameFactory extends Factory
{
    protected $model = Game::class;

    public function definition()
    {
        return [
            'name' => $this->faker->sentence(3),
            'type' => $this->faker->randomElement(['jeopardy', 'wheel']),
            'config' => [
                'time_mode' => $this->faker->randomElement(['unlimited', 'fixed', 'decreasing']),
                'surprise_enabled' => $this->faker->boolean,
            ],
            'description' => $this->faker->paragraph,
            'created_by' => User::factory(),
            'is_active' => $this->faker->boolean(80), // 80% aktif olma olasılığı
        ];
    }
}
