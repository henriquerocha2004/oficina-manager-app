<?php

namespace Tests\Unit\Actions;

use App\Actions\Tenant\Client\GetClientStatsAction;
use App\Models\Tenant\Client;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class GetClientStatsActionTest extends TestCase
{
    use DatabaseTransactions;

    public function test_returns_correct_stats_structure(): void
    {
        // Arrange
        Client::factory()->count(5)->create();
        $action = new GetClientStatsAction;

        // Act
        $result = $action();

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('total', $result);
        $this->assertArrayHasKey('last_90_days', $result);
        $this->assertArrayHasKey('previous_90_days', $result);
        $this->assertArrayHasKey('growth', $result);
        $this->assertArrayHasKey('growth_percentage', $result);
        $this->assertArrayHasKey('top_city', $result);
        $this->assertArrayHasKey('top_city_count', $result);
        $this->assertArrayHasKey('top_city_percentage', $result);
        $this->assertEquals(5, $result['total']);
    }

    public function test_calculates_growth_correctly(): void
    {
        // Arrange
        Client::factory()->count(3)->create([
            'created_at' => now()->subDays(30),
        ]);
        Client::factory()->count(2)->create([
            'created_at' => now()->subDays(120),
        ]);
        $action = new GetClientStatsAction;

        // Act
        $result = $action();

        // Assert
        $this->assertEquals(5, $result['total']);
        $this->assertEquals(3, $result['last_90_days']);
        $this->assertEquals(2, $result['previous_90_days']);
        $this->assertEquals(1, $result['growth']);
        $this->assertEquals(50.0, $result['growth_percentage']);
    }

    public function test_returns_top_city_correctly(): void
    {
        // Arrange
        Client::factory()->count(3)->create(['city' => 'São Paulo']);
        Client::factory()->count(2)->create(['city' => 'Rio de Janeiro']);
        Client::factory()->count(1)->create(['city' => null]);
        $action = new GetClientStatsAction;

        // Act
        $result = $action();

        // Assert
        $this->assertEquals('São Paulo', $result['top_city']);
        $this->assertEquals(3, $result['top_city_count']);
        $this->assertEquals(50.0, $result['top_city_percentage']);
    }

    public function test_handles_empty_database_gracefully(): void
    {
        // Arrange
        $action = new GetClientStatsAction;

        // Act
        $result = $action();

        // Assert
        $this->assertEquals(0, $result['total']);
        $this->assertEquals(0, $result['last_90_days']);
        $this->assertEquals('N/A', $result['top_city']);
        $this->assertEquals(0, $result['growth_percentage']);
    }
}
