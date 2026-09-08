<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CityFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->city,
            'code' => strtoupper($this->faker->unique()->lexify('???')),
            'company' => $this->faker->company,
        ];
    }
}
