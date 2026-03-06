<?php

namespace Database\Factories;

use App\Models\PredictiveModel;
use Illuminate\Database\Eloquent\Factories\Factory;

class PredictiveModelRunResultFactory extends Factory
{
    public function definition(): array
    {
        $predicted = $this->faker->randomFloat(4, 10, 100);
        $actual = $this->faker->optional()->randomFloat(4, 10, 100);

        return [
            'model_id'  => PredictiveModel::factory(),
            'inputs'    => json_encode([
                'flow1' => $this->faker->randomFloat(2, 100, 1000),
                'flow2' => $this->faker->randomFloat(2, 100, 1000),
                'temp'  => $this->faker->randomFloat(2, 0, 100),
            ]),
            'result'    => json_encode((string) $predicted),
            'actual'    => $actual ? json_encode((string) $actual) : null,
        ];
    }
}
