<?php

namespace Database\Factories;

use App\Models\Tour;
use App\Models\TourDate;
use Illuminate\Database\Eloquent\Factories\Factory;

class TourDateFactory extends Factory
{
    protected $model = TourDate::class;

    public function definition(): array
    {
        return [
            'tour_id' => Tour::factory(),
            'date' => fake()->dateTimeBetween('+1 week', '+3 months')->format('Y-m-d'),
            'price' => fake()->numberBetween(10000, 100000),
            'available_slots' => fake()->numberBetween(5, 30),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => ['is_active' => false]);
    }
}
