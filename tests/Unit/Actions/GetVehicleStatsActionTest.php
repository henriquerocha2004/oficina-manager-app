<?php

namespace Tests\Unit\Actions;

use App\Actions\Tenant\Vehicle\GetVehicleStatsAction;
use App\Models\Tenant\Client;
use App\Models\Tenant\Vehicle;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class GetVehicleStatsActionTest extends TestCase
{
    use DatabaseTransactions;

    public function test_returns_correct_stats_structure(): void
    {
        // Arrange
        $action = new GetVehicleStatsAction;

        // Act
        $stats = $action();

        // Assert
        $this->assertIsArray($stats);
        $this->assertArrayHasKey('total', $stats);
        $this->assertArrayHasKey('last_90_days', $stats);
        $this->assertArrayHasKey('previous_90_days', $stats);
        $this->assertArrayHasKey('growth', $stats);
        $this->assertArrayHasKey('growth_percentage', $stats);
        $this->assertArrayHasKey('top_brand', $stats);
        $this->assertArrayHasKey('top_brand_count', $stats);
        $this->assertArrayHasKey('top_brand_percentage', $stats);
    }

    public function test_calculates_stats_correctly_with_vehicles(): void
    {
        // Arrange
        $client = Client::factory()->create();
        $now = Carbon::now();
        $ninetyDaysAgo = $now->copy()->subDays(90);
        $oneHundredEightyDaysAgo = $now->copy()->subDays(180);

        Vehicle::factory()->count(5)->create([
            'client_id' => $client->id,
            'brand' => 'Toyota',
            'created_at' => $now,
        ]);

        Vehicle::factory()->count(3)->create([
            'client_id' => $client->id,
            'brand' => 'Honda',
            'created_at' => $ninetyDaysAgo->copy()->addDay(),
        ]);

        Vehicle::factory()->count(2)->create([
            'client_id' => $client->id,
            'brand' => 'Toyota',
            'created_at' => $oneHundredEightyDaysAgo->copy()->addDay(),
        ]);

        $action = new GetVehicleStatsAction;

        // Act
        $stats = $action();

        // Assert
        $this->assertEquals(10, $stats['total']);
        $this->assertEquals(8, $stats['last_90_days']);
        $this->assertEquals(2, $stats['previous_90_days']);
        $this->assertEquals(6, $stats['growth']);
        $this->assertEquals(300.0, $stats['growth_percentage']);
        $this->assertEquals('Toyota', $stats['top_brand']);
        $this->assertEquals(7, $stats['top_brand_count']);
        $this->assertEquals(70.0, $stats['top_brand_percentage']);
    }

    public function test_returns_zero_stats_when_no_vehicles(): void
    {
        // Arrange
        $action = new GetVehicleStatsAction;

        // Act
        $stats = $action();

        // Assert
        $this->assertEquals(0, $stats['total']);
        $this->assertEquals(0, $stats['last_90_days']);
        $this->assertEquals(0, $stats['previous_90_days']);
        $this->assertEquals(0, $stats['growth']);
        $this->assertEquals(0, $stats['growth_percentage']);
        $this->assertNull($stats['top_brand']);
        $this->assertEquals(0, $stats['top_brand_count']);
        $this->assertEquals(0, $stats['top_brand_percentage']);
    }

    public function test_calculates_growth_percentage_correctly(): void
    {
        // Arrange
        $client = Client::factory()->create();
        $now = Carbon::now();
        $ninetyDaysAgo = $now->copy()->subDays(90);
        $oneHundredEightyDaysAgo = $now->copy()->subDays(180);

        Vehicle::factory()->count(10)->create([
            'client_id' => $client->id,
            'created_at' => $now,
        ]);

        Vehicle::factory()->count(5)->create([
            'client_id' => $client->id,
            'created_at' => $oneHundredEightyDaysAgo->copy()->addDay(),
        ]);

        $action = new GetVehicleStatsAction;

        // Act
        $stats = $action();

        // Assert
        $this->assertEquals(10, $stats['last_90_days']);
        $this->assertEquals(5, $stats['previous_90_days']);
        $this->assertEquals(5, $stats['growth']);
        $this->assertEquals(100.0, $stats['growth_percentage']);
    }

    public function test_handles_zero_division_in_growth_percentage(): void
    {
        // Arrange
        $client = Client::factory()->create();

        Vehicle::factory()->count(5)->create([
            'client_id' => $client->id,
            'created_at' => Carbon::now(),
        ]);

        $action = new GetVehicleStatsAction;

        // Act
        $stats = $action();

        // Assert
        $this->assertEquals(5, $stats['last_90_days']);
        $this->assertEquals(0, $stats['previous_90_days']);
        $this->assertEquals(5, $stats['growth']);
        $this->assertEquals(0, $stats['growth_percentage']);
    }

    public function test_finds_top_brand_correctly(): void
    {
        // Arrange
        $client = Client::factory()->create();

        Vehicle::factory()->count(5)->create([
            'client_id' => $client->id,
            'brand' => 'Ford',
        ]);

        Vehicle::factory()->count(3)->create([
            'client_id' => $client->id,
            'brand' => 'Chevrolet',
        ]);

        Vehicle::factory()->count(2)->create([
            'client_id' => $client->id,
            'brand' => 'Volkswagen',
        ]);

        $action = new GetVehicleStatsAction;

        // Act
        $stats = $action();

        // Assert
        $this->assertEquals('Ford', $stats['top_brand']);
        $this->assertEquals(5, $stats['top_brand_count']);
        $this->assertEquals(50.0, $stats['top_brand_percentage']);
    }
}
