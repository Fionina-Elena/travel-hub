<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Tour;
use App\Models\TourDate;
use App\Models\TourImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TourTest extends TestCase
{
    use RefreshDatabase;

    public function test_tour_has_title(): void
    {
        $tour = Tour::factory()->create(['title' => 'Mountain Adventure']);

        $this->assertEquals('Mountain Adventure', $tour->title);
    }

    public function test_tour_generates_slug_from_title(): void
    {
        $tour = Tour::factory()->create([
            'title' => 'Mountain Adventure',
            'slug' => null,
        ]);

        $this->assertEquals('mountain-adventure', $tour->slug);
    }

    public function test_tour_has_duration_days(): void
    {
        $tour = Tour::factory()->create(['duration_days' => 7]);

        $this->assertEquals(7, $tour->duration_days);
    }

    public function test_tour_casts_route_points_as_array(): void
    {
        $routePoints = [['name' => 'Moscow', 'lat' => 55.75, 'lng' => 37.61]];
        $tour = Tour::factory()->create(['route_points' => $routePoints]);

        $tour->refresh();
        $this->assertIsArray($tour->route_points);
        $this->assertEquals('Moscow', $tour->route_points[0]['name']);
    }

    public function test_tour_casts_highlights_as_array(): void
    {
        $highlights = ['Mountain views', 'Local cuisine', 'Historical sites'];
        $tour = Tour::factory()->create(['highlights' => $highlights]);

        $tour->refresh();
        $this->assertIsArray($tour->highlights);
        $this->assertCount(3, $tour->highlights);
    }

    public function test_tour_casts_is_published_as_boolean(): void
    {
        $tour = Tour::factory()->create(['is_published' => true]);

        $tour->refresh();
        $this->assertIsBool($tour->is_published);
        $this->assertTrue($tour->is_published);
    }

    public function test_tour_belongs_to_category(): void
    {
        $category = Category::factory()->create();
        $tour = Tour::factory()->create(['category_id' => $category->id]);

        $this->assertInstanceOf(Category::class, $tour->category);
        $this->assertEquals($category->id, $tour->category->id);
    }

    public function test_tour_has_many_images(): void
    {
        $tour = Tour::factory()->create();
        TourImage::factory()->count(3)->create(['tour_id' => $tour->id]);

        $this->assertCount(3, $tour->images);
    }

    public function test_tour_images_are_ordered_by_sort_order(): void
    {
        $tour = Tour::factory()->create();
        TourImage::factory()->create(['tour_id' => $tour->id, 'sort_order' => 2]);
        TourImage::factory()->create(['tour_id' => $tour->id, 'sort_order' => 1]);
        TourImage::factory()->create(['tour_id' => $tour->id, 'sort_order' => 3]);

        $tour->load('images');
        $this->assertEquals(1, $tour->images->first()->sort_order);
    }

    public function test_tour_has_many_dates(): void
    {
        $tour = Tour::factory()->create();
        TourDate::factory()->count(2)->create(['tour_id' => $tour->id]);

        $this->assertCount(2, $tour->dates);
    }

    public function test_tour_dates_only_returns_active_dates(): void
    {
        $tour = Tour::factory()->create();
        TourDate::factory()->create(['tour_id' => $tour->id, 'is_active' => true]);
        TourDate::factory()->create(['tour_id' => $tour->id, 'is_active' => false]);
        TourDate::factory()->create(['tour_id' => $tour->id, 'is_active' => true]);

        $this->assertCount(2, $tour->dates);
    }

    public function test_tour_all_dates_returns_all_dates(): void
    {
        $tour = Tour::factory()->create();
        TourDate::factory()->count(3)->create(['tour_id' => $tour->id]);

        $this->assertCount(3, $tour->allDates);
    }

    public function test_tour_can_be_unpublished(): void
    {
        $tour = Tour::factory()->unpublished()->create();

        $this->assertFalse($tour->is_published);
    }
}
