<?php

namespace Database\Factories;

use App\Models\Board;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Activity>
 */
class ActivityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'board_id' => Board::factory(),
            'user_id' => User::factory(),
            'type' => $this->faker->randomElement(['card_moved', 'comment_added', 'card_created']),
            'data' => json_encode(['from_list' => rand(1, 10), 'to_list' => rand(1, 10)]),
            'created_at' => now(),
        ];
    }
}
