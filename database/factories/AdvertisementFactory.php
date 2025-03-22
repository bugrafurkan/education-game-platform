<?php

namespace Database\Factories;

use App\Models\Advertisement;
use Illuminate\Database\Eloquent\Factories\Factory;

class AdvertisementFactory extends Factory
{
    protected $model = Advertisement::class;

    public function definition()
    {
        return [
            'title' => $this->faker->sentence(4),
            'type' => $this->faker->randomElement(['banner', 'video']),
            'media_path' => '/storage/ads-banners/default.jpg',
            'target_grade' => $this->faker->randomElement([null, '5', '6', '7', '8', '9']),
            'target_subject' => $this->faker->randomElement([null, 'Matematik', 'Fen', 'Türkçe']),
            'target_game_type' => $this->faker->randomElement([null, 'jeopardy', 'wheel']),
            'link_url' => $this->faker->url,
            'start_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'end_date' => $this->faker->dateTimeBetween('now', '+3 months'),
            'is_active' => $this->faker->boolean(80),
        ];
    }
}
