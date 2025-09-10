<?php

namespace Database\Factories;

use App\Models\TaskList;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Card>
 */
class CardFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'list_id' => TaskList::factory(),
            'name' => $this->faker->sentence(4),
            'description' => $this->faker->optional()->paragraph,
            'position' => $this->faker->randomFloat(2, 0, 100),
            'due_date' => $this->faker->optional()->dateTimeBetween('now', '+1 month'),
            'archived' => $this->faker->boolean(10),
            'created_at' => now(),
        ];
    }
}
