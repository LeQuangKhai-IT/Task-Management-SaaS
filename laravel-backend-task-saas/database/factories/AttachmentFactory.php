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
            'title' => $this->faker->word,
            'type' => $this->faker->randomElement(['file', 'url']),
            'file_name' => $this->faker->slug(3, true) . '.' . $this->faker->fileExtension(),
            'file_path' => 'files/' . $this->faker->uuid() . '/' . $this->faker->slug(3, true) . '.' . $this->faker->fileExtension(),
            'file_type' => $this->faker->mimeType(),
            'file_size' => $this->faker->numberBetween(1024, 5242880),
            'uploaded_by' => User::factory(),
            'created_at' => now(),
        ];
    }
}
