<?php

namespace Tests\Unit\Actions;

use App\Actions\Tenant\Service\GetServiceStatsAction;
use App\Models\Tenant\Service;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class GetServiceStatsActionTest extends TestCase
{
    use DatabaseTransactions;

    public function test_returns_correct_stats_structure(): void
    {
        // Arrange
        $action = new GetServiceStatsAction;

        // Act
        $stats = $action();

        // Assert
        $this->assertIsArray($stats);
        $this->assertArrayHasKey('total', $stats);
        $this->assertArrayHasKey('active_services', $stats);
        $this->assertArrayHasKey('average_price', $stats);
        $this->assertArrayHasKey('last_90_days', $stats);
        $this->assertArrayHasKey('previous_90_days', $stats);
        $this->assertArrayHasKey('growth', $stats);
        $this->assertArrayHasKey('growth_percentage', $stats);
    }

    public function test_calculates_stats_correctly_with_services(): void
    {
        // Arrange
        $now = Carbon::now();
        $ninetyDaysAgo = $now->copy()->subDays(90);
        $oneHundredEightyDaysAgo = $now->copy()->subDays(180);

        Service::factory()->count(5)->create([
            'is_active' => true,
            'base_price' => 100.00,
            'created_at' => $now,
        ]);

        Service::factory()->count(3)->create([
            'is_active' => false,
            'base_price' => 200.00,
            'created_at' => $ninetyDaysAgo->copy()->addDay(),
        ]);

        Service::factory()->count(2)->create([
            'is_active' => true,
            'base_price' => 150.00,
            'created_at' => $oneHundredEightyDaysAgo->copy()->addDay(),
        ]);

        $action = new GetServiceStatsAction;

        // Act
        $stats = $action();

        // Assert
        $this->assertEquals(10, $stats['total']);
        $this->assertEquals(7, $stats['active_services']);
        $this->assertEquals(8, $stats['last_90_days']);
        $this->assertEquals(2, $stats['previous_90_days']);
        $this->assertEquals(6, $stats['growth']);
        $this->assertEquals(300.0, $stats['growth_percentage']);
        $this->assertEquals(140.0, $stats['average_price']);
    }

    public function test_returns_zero_stats_when_no_services(): void
    {
        // Arrange
        $action = new GetServiceStatsAction;

        // Act
        $stats = $action();

        // Assert
        $this->assertEquals(0, $stats['total']);
        $this->assertEquals(0, $stats['active_services']);
        $this->assertEquals(0, $stats['average_price']);
        $this->assertEquals(0, $stats['last_90_days']);
        $this->assertEquals(0, $stats['previous_90_days']);
        $this->assertEquals(0, $stats['growth']);
        $this->assertEquals(0, $stats['growth_percentage']);
    }

    public function test_calculates_growth_percentage_correctly(): void
    {
        // Arrange
        $now = Carbon::now();
        $ninetyDaysAgo = $now->copy()->subDays(90);
        $oneHundredEightyDaysAgo = $now->copy()->subDays(180);

        Service::factory()->count(10)->create([
            'created_at' => $now,
        ]);

        Service::factory()->count(5)->create([
            'created_at' => $oneHundredEightyDaysAgo->copy()->addDay(),
        ]);

        $action = new GetServiceStatsAction;

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
        Service::factory()->count(5)->create([
            'created_at' => Carbon::now(),
        ]);

        $action = new GetServiceStatsAction;

        // Act
        $stats = $action();

        // Assert
        $this->assertEquals(5, $stats['last_90_days']);
        $this->assertEquals(0, $stats['previous_90_days']);
        $this->assertEquals(5, $stats['growth']);
        $this->assertEquals(0, $stats['growth_percentage']);
    }

    public function test_calculates_average_price_correctly(): void
    {
        // Arrange
        Service::factory()->create(['base_price' => 100.00]);
        Service::factory()->create(['base_price' => 200.00]);
        Service::factory()->create(['base_price' => 300.00]);

        $action = new GetServiceStatsAction;

        // Act
        $stats = $action();

        // Assert
        $this->assertEquals(200.0, $stats['average_price']);
    }
}
