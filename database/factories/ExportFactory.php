<?php

namespace Database\Factories;

use App\Models\Export;
use App\Models\Game;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExportFactory extends Factory
{
    protected $model = Export::class;

    public function definition()
    {
        return [
            'game_id' => Game::factory(),
            'version' => '1.' . $this->faker->numberBetween(0, 9),
            'status' => $this->faker->randomElement(['pending', 'processing', 'completed', 'failed']),
            'download_url' => $this->faker->url,
            'uploaded_to_fernus' => $this->faker->boolean,
            'fernus_url' => $this->faker->boolean ? $this->faker->url : null,
            'created_by' => User::factory(),
            'config_snapshot' => null,
        ];
    }
}
