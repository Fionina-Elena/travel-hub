<?php

namespace Database\Factories;

use App\Models\Tour;
use App\Models\TourImage;
use Illuminate\Database\Eloquent\Factories\Factory;

class TourImageFactory extends Factory
{
    protected $model = TourImage::class;

    public function definition(): array
    {
        return [
            'tour_id' => Tour::factory(),
            'url' => 'tours/'.fake()->uuid().'.jpg',
            'alt' => fake()->optional()->sentence(),
            'sort_order' => 0,
        ];
    }
}
