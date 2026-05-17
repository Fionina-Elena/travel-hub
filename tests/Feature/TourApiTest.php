<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Tour;
use App\Models\TourDate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TourApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_tours(): void
    {
        Tour::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/tours');

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_tours_list_includes_category_images_and_dates(): void
    {
        $tour = Tour::factory()->create();
        TourDate::factory()->create(['tour_id' => $tour->id]);

        $response = $this->getJson('/api/v1/tours');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['category', 'images', 'dates'],
                ],
            ]);
    }

    public function test_can_filter_tours_by_category(): void
    {
        $category = Category::factory()->create();
        Tour::factory()->count(2)->create(['category_id' => $category->id]);
        Tour::factory()->create();

        $response = $this->getJson("/api/v1/tours?category={$category->id}");

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_can_filter_tours_by_min_days(): void
    {
        Tour::factory()->create(['duration_days' => 3]);
        Tour::factory()->create(['duration_days' => 7]);
        Tour::factory()->create(['duration_days' => 10]);

        $response = $this->getJson('/api/v1/tours?min_days=7');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_can_filter_tours_by_max_days(): void
    {
        Tour::factory()->create(['duration_days' => 3]);
        Tour::factory()->create(['duration_days' => 7]);
        Tour::factory()->create(['duration_days' => 10]);

        $response = $this->getJson('/api/v1/tours?max_days=7');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_can_filter_published_only(): void
    {
        Tour::factory()->count(2)->create(['is_published' => true]);
        Tour::factory()->create(['is_published' => false]);

        $response = $this->getJson('/api/v1/tours?published_only=true');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_can_create_tour(): void
    {
        $category = Category::factory()->create();

        $response = $this->postJson('/api/v1/tours', [
            'category_id' => $category->id,
            'title' => 'Mountain Tour',
            'description' => 'Amazing mountains',
            'duration_days' => 5,
            'is_published' => true,
        ]);

        $response->assertCreated()
            ->assertJsonPath('title', 'Mountain Tour')
            ->assertJsonPath('slug', 'mountain-tour');

        $this->assertDatabaseHas('tours', ['title' => 'Mountain Tour']);
    }

    public function test_create_tour_requires_title(): void
    {
        $response = $this->postJson('/api/v1/tours', [
            'category_id' => 1,
            'description' => 'Test',
            'duration_days' => 5,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['title']);
    }

    public function test_can_show_tour(): void
    {
        $tour = Tour::factory()->create(['title' => 'Beach Tour']);

        $response = $this->getJson("/api/v1/tours/{$tour->id}");

        $response->assertOk()
            ->assertJsonPath('title', 'Beach Tour');
    }

    public function test_can_update_tour(): void
    {
        $tour = Tour::factory()->create(['title' => 'Old Title']);

        $response = $this->putJson("/api/v1/tours/{$tour->id}", [
            'title' => 'New Title',
        ]);

        $response->assertOk()
            ->assertJsonPath('title', 'New Title');
    }

    public function test_can_delete_tour(): void
    {
        $tour = Tour::factory()->create();

        $response = $this->deleteJson("/api/v1/tours/{$tour->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('tours', ['id' => $tour->id]);
    }

    public function test_can_search_tours(): void
    {
        Tour::factory()->create(['title' => 'Mountain Adventure', 'is_published' => true]);
        Tour::factory()->create(['title' => 'Beach Relaxation', 'is_published' => true]);
        Tour::factory()->create(['title' => 'Secret Tour', 'is_published' => false]);

        $response = $this->getJson('/api/v1/tours/search?q=Mountain');

        $response->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.title', 'Mountain Adventure');
    }

    public function test_search_requires_query_parameter(): void
    {
        $response = $this->getJson('/api/v1/tours/search');

        $response->assertUnprocessable();
    }
}
