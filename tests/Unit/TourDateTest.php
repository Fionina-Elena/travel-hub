<?php

namespace Tests\Unit;

use App\Models\Tour;
use App\Models\TourDate;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TourDateTest extends TestCase
{
    use RefreshDatabase;

    public function test_tour_date_has_price(): void
    {
        $tourDate = TourDate::factory()->create(['price' => 50000]);

        $this->assertEquals(50000, $tourDate->price);
    }

    public function test_tour_date_has_available_slots(): void
    {
        $tourDate = TourDate::factory()->create(['available_slots' => 15]);

        $this->assertEquals(15, $tourDate->available_slots);
    }

    public function test_tour_date_available_slots_can_be_null(): void
    {
        $tourDate = TourDate::factory()->create(['available_slots' => null]);

        $this->assertNull($tourDate->available_slots);
    }

    public function test_tour_date_casts_is_active_as_boolean(): void
    {
        $tourDate = TourDate::factory()->create(['is_active' => true]);

        $tourDate->refresh();
        $this->assertTrue($tourDate->is_active);
    }

    public function test_tour_date_casts_date_as_date(): void
    {
        $tourDate = TourDate::factory()->create(['date' => '2025-06-15']);

        $tourDate->refresh();
        $this->assertInstanceOf(Carbon::class, $tourDate->date);
    }

    public function test_tour_date_belongs_to_tour(): void
    {
        $tour = Tour::factory()->create();
        $tourDate = TourDate::factory()->create(['tour_id' => $tour->id]);

        $this->assertInstanceOf(Tour::class, $tourDate->tour);
        $this->assertEquals($tour->id, $tourDate->tour->id);
    }

    public function test_tour_date_can_be_inactive(): void
    {
        $tourDate = TourDate::factory()->inactive()->create();

        $this->assertFalse($tourDate->is_active);
    }
}
