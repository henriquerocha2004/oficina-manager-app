<?php

namespace Tests\Unit\Actions;

use App\Actions\Tenant\ServiceOrder\CreateServiceOrderAction;
use App\Actions\Tenant\ServiceOrder\GetServiceOrderStatsAction;
use App\Domain\Tenant\ServiceOrder\ServiceOrderDomain;
use App\Dto\ServiceOrderDto;
use App\Models\Tenant\Client;
use App\Models\Tenant\User;
use App\Models\Tenant\Vehicle;
use App\Services\Tenant\ServiceOrder\PaymentService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class GetServiceOrderStatsActionTest extends TestCase
{
    use DatabaseTransactions;

    public function test_returns_stats_with_no_service_orders(): void
    {
        $action = new GetServiceOrderStatsAction;
        $stats = $action();

        $this->assertEquals(0, $stats['total']);
        $this->assertEquals(0, $stats['by_status']['draft']);
    }

    /**
     * @throws \Throwable
     * @throws BindingResolutionException
     */
    public function test_returns_stats_with_service_orders(): void
    {
        $client = Client::factory()->create();
        $vehicle = Vehicle::factory()->create();
        $user = User::factory()->create();

        $domain = new ServiceOrderDomain(
            $this->app->make(PaymentService::class)
        );

        $createAction = new CreateServiceOrderAction($domain);

        for ($i = 0; $i < 3; $i++) {
            $dto = new ServiceOrderDto(
                client_id: $client->id,
                vehicle_id: $vehicle->id,
                created_by: $user->id,
            );
            $createAction($dto);
        }

        $action = new GetServiceOrderStatsAction;
        $stats = $action();

        $this->assertEquals(3, $stats['total']);
        $this->assertEquals(3, $stats['by_status']['draft']);
        $this->assertArrayHasKey('draft', $stats['by_status']);
        $this->assertArrayHasKey('waiting_approval', $stats['by_status']);
        $this->assertArrayHasKey('approved', $stats['by_status']);
        $this->assertArrayHasKey('in_progress', $stats['by_status']);
        $this->assertArrayHasKey('waiting_payment', $stats['by_status']);
        $this->assertArrayHasKey('completed', $stats['by_status']);
        $this->assertArrayHasKey('cancelled', $stats['by_status']);
    }
}
