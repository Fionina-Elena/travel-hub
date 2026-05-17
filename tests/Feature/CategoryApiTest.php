<?php

namespace Tests\Feature;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_categories(): void
    {
        Category::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/categories');

        $response->assertOk()
            ->assertJsonCount(3);
    }

    public function test_can_create_category(): void
    {
        $response = $this->postJson('/api/v1/categories', [
            'name' => 'Adventure',
            'description' => 'Exciting tours',
        ]);

        $response->assertCreated()
            ->assertJsonPath('name', 'Adventure')
            ->assertJsonPath('slug', 'adventure');

        $this->assertDatabaseHas('categories', [
            'name' => 'Adventure',
            'slug' => 'adventure',
        ]);
    }

    public function test_create_category_requires_name(): void
    {
        $response = $this->postJson('/api/v1/categories', [
            'description' => 'Some description',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_can_show_category(): void
    {
        $category = Category::factory()->create(['name' => 'Beach']);

        $response = $this->getJson("/api/v1/categories/{$category->id}");

        $response->assertOk()
            ->assertJsonPath('name', 'Beach');
    }

    public function test_can_update_category(): void
    {
        $category = Category::factory()->create(['name' => 'Old Name']);

        $response = $this->putJson("/api/v1/categories/{$category->id}", [
            'name' => 'New Name',
        ]);

        $response->assertOk()
            ->assertJsonPath('name', 'New Name')
            ->assertJsonPath('slug', 'new-name');
    }

    public function test_can_delete_category(): void
    {
        $category = Category::factory()->create();

        $response = $this->deleteJson("/api/v1/categories/{$category->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }
}
