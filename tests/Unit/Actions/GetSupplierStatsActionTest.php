<?php

namespace Tests\Unit\Actions;

use App\Actions\Tenant\Supplier\GetSupplierStatsAction;
use App\Models\Tenant\Supplier;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class GetSupplierStatsActionTest extends TestCase
{
    use DatabaseTransactions;

    public function test_returns_correct_stats_structure(): void
    {
        // Arrange
        Supplier::factory()->count(5)->create();
        $action = new GetSupplierStatsAction;

        // Act
        $result = $action();

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('total', $result);
        $this->assertArrayHasKey('last_90_days', $result);
        $this->assertArrayHasKey('previous_90_days', $result);
        $this->assertArrayHasKey('active_suppliers', $result);
        $this->assertArrayHasKey('active_percentage', $result);
        $this->assertArrayHasKey('growth', $result);
        $this->assertArrayHasKey('growth_percentage', $result);
        $this->assertArrayHasKey('top_state', $result);
        $this->assertArrayHasKey('top_state_count', $result);
        $this->assertArrayHasKey('top_state_percentage', $result);
        $this->assertEquals(5, $result['total']);
    }

    public function test_calculates_growth_correctly(): void
    {
        // Arrange
        Supplier::factory()->count(3)->create([
            'created_at' => now()->subDays(30),
        ]);
        Supplier::factory()->count(2)->create([
            'created_at' => now()->subDays(120),
        ]);
        $action = new GetSupplierStatsAction;

        // Act
        $result = $action();

        // Assert
        $this->assertEquals(5, $result['total']);
        $this->assertEquals(3, $result['last_90_days']);
        $this->assertEquals(2, $result['previous_90_days']);
        $this->assertEquals(1, $result['growth']);
        $this->assertEquals(50.0, $result['growth_percentage']);
    }

    public function test_returns_top_state_correctly(): void
    {
        // Arrange
        Supplier::factory()->count(3)->create(['state' => 'SP']);
        Supplier::factory()->count(2)->create(['state' => 'RJ']);
        Supplier::factory()->count(1)->create(['state' => null]);
        $action = new GetSupplierStatsAction;

        // Act
        $result = $action();

        // Assert
        $this->assertEquals('SP', $result['top_state']);
        $this->assertEquals(3, $result['top_state_count']);
        $this->assertEquals(50.0, $result['top_state_percentage']);
    }

    public function test_calculates_active_percentage_correctly(): void
    {
        // Arrange
        Supplier::factory()->count(7)->create(['is_active' => true]);
        Supplier::factory()->count(3)->create(['is_active' => false]);
        $action = new GetSupplierStatsAction;

        // Act
        $result = $action();

        // Assert
        $this->assertEquals(10, $result['total']);
        $this->assertEquals(7, $result['active_suppliers']);
        $this->assertEquals(70.0, $result['active_percentage']);
    }

    public function test_handles_empty_database_gracefully(): void
    {
        // Arrange
        $action = new GetSupplierStatsAction;

        // Act
        $result = $action();

        // Assert
        $this->assertEquals(0, $result['total']);
        $this->assertEquals(0, $result['last_90_days']);
        $this->assertNull($result['top_state']);
        $this->assertEquals(0, $result['growth_percentage']);
        $this->assertEquals(0, $result['active_percentage']);
    }
}
