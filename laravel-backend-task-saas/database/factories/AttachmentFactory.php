<?php

namespace Database\Factories;

use App\Models\Card;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Attachment>
 */
class AttachmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'card_id' => Card::factory(),
            'url' => $this->faker->url,
            'name' => $this->faker->word . '.pdf',
            'type' => $this->faker->randomElement(['image', 'pdf', 'document']),
            'uploaded_by' => User::factory(),
            'created_at' => now(),
        ];
    }
}
