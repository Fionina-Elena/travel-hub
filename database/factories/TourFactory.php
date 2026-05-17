<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Tour;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TourFactory extends Factory
{
    protected $model = Tour::class;

    public function definition(): array
    {
        $title = fake()->sentence(3);

        return [
            'category_id' => Category::factory(),
            'title' => $title,
            'slug' => Str::slug($title),
            'description' => fake()->paragraph(5),
            'duration_days' => fake()->numberBetween(1, 14),
            'route_points' => [
                ['name' => fake()->city(), 'lat' => fake()->latitude(), 'lng' => fake()->longitude()],
                ['name' => fake()->city(), 'lat' => fake()->latitude(), 'lng' => fake()->longitude()],
            ],
            'highlights' => [fake()->word(), fake()->word(), fake()->word()],
            'included' => fake()->sentence(),
            'excluded' => fake()->sentence(),
            'is_published' => true,
        ];
    }

    public function unpublished(): static
    {
        return $this->state(fn (array $attributes) => ['is_published' => false]);
    }
}
