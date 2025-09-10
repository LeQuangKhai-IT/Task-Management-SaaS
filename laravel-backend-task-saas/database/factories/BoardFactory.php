<?php

namespace Database\Factories;

use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Board>
 */
class BoardFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->optional()->paragraph,
            'workspace_id' => $this->faker->optional()->randomElement(Workspace::pluck('id')->toArray()),
            'background' => $this->faker->randomElement(['#3498db', '#e74c3c', '#2ecc71', 'https://example.com/image.jpg']),
            'visibility' => $this->faker->randomElement(['public', 'workspace', 'private']),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
