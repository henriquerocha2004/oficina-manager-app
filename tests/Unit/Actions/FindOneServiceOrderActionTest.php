<?php

namespace Tests\Unit\Actions;

use App\Actions\Tenant\ServiceOrder\CreateServiceOrderAction;
use App\Actions\Tenant\ServiceOrder\FindOneServiceOrderAction;
use App\Domain\Tenant\ServiceOrder\ServiceOrderDomain;
use App\Dto\ServiceOrderDto;
use App\Exceptions\ServiceOrder\ServiceOrderNotFoundException;
use App\Models\Tenant\Client;
use App\Models\Tenant\ServiceOrder;
use App\Models\Tenant\User;
use App\Models\Tenant\Vehicle;
use App\Services\Tenant\ServiceOrder\PaymentService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Throwable;

class FindOneServiceOrderActionTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @throws Throwable
     * @throws BindingResolutionException
     */
    public function test_returns_service_order_when_found(): void
    {
        $client = Client::factory()->create();
        $vehicle = Vehicle::factory()->create();
        $user = User::factory()->create();

        $dto = new ServiceOrderDto(
            client_id: $client->id,
            vehicle_id: $vehicle->id,
            created_by: $user->id,
        );

        $domain = new ServiceOrderDomain(
            $this->app->make(PaymentService::class)
        );

        $createAction = new CreateServiceOrderAction($domain);
        $serviceOrder = $createAction($dto);

        $action = new FindOneServiceOrderAction;
        $result = $action($serviceOrder->id);

        $this->assertInstanceOf(ServiceOrder::class, $result);
        $this->assertEquals($serviceOrder->id, $result->id);
        $this->assertTrue($result->relationLoaded('client'));
        $this->assertTrue($result->relationLoaded('vehicle'));
        $this->assertTrue($result->relationLoaded('items'));
    }

    /**
     * @throws Throwable
     */
    public function test_throws_exception_when_not_found(): void
    {
        $this->expectException(ServiceOrderNotFoundException::class);

        $action = new FindOneServiceOrderAction;
        $action('non-existent-id');
    }
}
