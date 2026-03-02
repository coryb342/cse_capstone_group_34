<?php

namespace Database\Factories;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\PredictiveModel>
 */
class PredictiveModelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'path' => fake()->filePath(),
            'name' => fake()->word(),
            'required_parameters' => json_encode(fake()->word()),
            'target' => fake()->word(),
            'description' => fake()->sentence(),
            'type' => fake()->word(),
            'status' => 'active',
            'last_trained_on' => fake()->date(),
            'accuracy' => fake()->randomFloat(2, 1, 100),
        ];
    }
}
