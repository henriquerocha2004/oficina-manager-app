<?php

namespace Tests\Unit\Actions;

use App\Actions\Tenant\Product\GetProductStatsAction;
use App\Models\Tenant\Product;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class GetProductStatsActionTest extends TestCase
{
    use DatabaseTransactions;

    public function test_returns_correct_stats_structure(): void
    {
        // Arrange
        Product::factory()->count(5)->create();
        $action = new GetProductStatsAction;

        // Act
        $result = $action();

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('total', $result);
        $this->assertArrayHasKey('last_90_days', $result);
        $this->assertArrayHasKey('previous_90_days', $result);
        $this->assertArrayHasKey('active_products', $result);
        $this->assertArrayHasKey('active_percentage', $result);
        $this->assertArrayHasKey('total_value', $result);
        $this->assertArrayHasKey('growth', $result);
        $this->assertArrayHasKey('growth_percentage', $result);
        $this->assertArrayHasKey('top_category', $result);
        $this->assertArrayHasKey('top_category_count', $result);
        $this->assertArrayHasKey('top_category_percentage', $result);
        $this->assertEquals(5, $result['total']);
    }

    public function test_calculates_growth_correctly(): void
    {
        // Arrange
        Product::factory()->count(3)->create([
            'created_at' => now()->subDays(30),
        ]);
        Product::factory()->count(2)->create([
            'created_at' => now()->subDays(120),
        ]);
        $action = new GetProductStatsAction;

        // Act
        $result = $action();

        // Assert
        $this->assertEquals(5, $result['total']);
        $this->assertEquals(3, $result['last_90_days']);
        $this->assertEquals(2, $result['previous_90_days']);
        $this->assertEquals(1, $result['growth']);
        $this->assertEquals(50.0, $result['growth_percentage']);
    }

    public function test_returns_top_category_correctly(): void
    {
        // Arrange
        Product::factory()->count(3)->create(['category' => 'engine']);
        Product::factory()->count(2)->create(['category' => 'brakes']);
        Product::factory()->count(1)->create(['category' => 'filters']);
        $action = new GetProductStatsAction;

        // Act
        $result = $action();

        // Assert
        $this->assertEquals('engine', $result['top_category']);
        $this->assertEquals(3, $result['top_category_count']);
        $this->assertEquals(50.0, $result['top_category_percentage']);
    }

    public function test_calculates_active_percentage_correctly(): void
    {
        // Arrange
        Product::factory()->count(7)->create(['is_active' => true]);
        Product::factory()->count(3)->create(['is_active' => false]);
        $action = new GetProductStatsAction;

        // Act
        $result = $action();

        // Assert
        $this->assertEquals(10, $result['total']);
        $this->assertEquals(7, $result['active_products']);
        $this->assertEquals(70.0, $result['active_percentage']);
    }

    public function test_calculates_total_value_correctly(): void
    {
        // Arrange
        Product::factory()->count(3)->create(['unit_price' => 100.00]);
        Product::factory()->count(2)->create(['unit_price' => 50.00]);
        $action = new GetProductStatsAction;

        // Act
        $result = $action();

        // Assert
        $this->assertEquals(5, $result['total']);
        $this->assertEquals(400.00, $result['total_value']);
    }

    public function test_handles_empty_database_gracefully(): void
    {
        // Arrange
        $action = new GetProductStatsAction;

        // Act
        $result = $action();

        // Assert
        $this->assertEquals(0, $result['total']);
        $this->assertEquals(0, $result['last_90_days']);
        $this->assertNull($result['top_category']);
        $this->assertEquals(0, $result['growth_percentage']);
        $this->assertEquals(0, $result['active_percentage']);
        $this->assertEquals(0.0, $result['total_value']);
    }
}
