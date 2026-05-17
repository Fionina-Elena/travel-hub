<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Tour;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_has_name(): void
    {
        $category = Category::factory()->create(['name' => 'Adventure']);

        $this->assertEquals('Adventure', $category->name);
    }

    public function test_category_has_slug(): void
    {
        $category = Category::factory()->create([
            'name' => 'Adventure Tours',
            'slug' => 'adventure-tours',
        ]);

        $this->assertEquals('adventure-tours', $category->slug);
    }

    public function test_category_can_have_description(): void
    {
        $category = Category::factory()->create(['description' => 'Exciting adventures']);

        $this->assertEquals('Exciting adventures', $category->description);
    }

    public function test_category_can_have_null_description(): void
    {
        $category = Category::factory()->create(['description' => null]);

        $this->assertNull($category->description);
    }

    public function test_category_can_have_many_tours(): void
    {
        $category = Category::factory()->create();
        Tour::factory()->count(3)->create(['category_id' => $category->id]);

        $this->assertCount(3, $category->tours);
    }

    public function test_category_slug_is_unique(): void
    {
        $this->expectException(QueryException::class);

        Category::factory()->create(['slug' => 'adventure']);
        Category::factory()->create(['slug' => 'adventure']);
    }
}
