<?php

namespace Database\Factories;

use App\Models\Game;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class TopStreamFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'twitch_id' => $this->faker->unique()->numberBetween(100000),
            'title' => $this->faker->words(5, true),
            'game_id' => Game::factory(),
            'broadcaster_id' => User::factory(),
            'language' => $this->faker->locale(),
            'viewer_count' => $this->faker->numberBetween(4000),
            'started_at' => Carbon::now()
        ];
    }
}
