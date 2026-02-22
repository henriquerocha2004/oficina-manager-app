<?php

namespace Tests\Unit\Actions;

use App\Actions\Tenant\ServiceOrder\CreateServiceOrderAction;
use App\Actions\Tenant\ServiceOrder\DeleteServiceOrderAction;
use App\Domain\Tenant\ServiceOrder\ServiceOrderDomain;
use App\Dto\ServiceOrderDto;
use App\Exceptions\ServiceOrder\ServiceOrderNotFoundException;
use App\Models\Tenant\Client;
use App\Models\Tenant\User;
use App\Models\Tenant\Vehicle;
use App\Services\Tenant\ServiceOrder\PaymentService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Throwable;

class DeleteServiceOrderActionTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @throws Throwable
     * @throws BindingResolutionException
     */
    public function test_deletes_service_order_when_found(): void
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

        $action = new DeleteServiceOrderAction;
        $action($serviceOrder->id);

        $this->assertSoftDeleted('service_orders', ['id' => $serviceOrder->id]);
    }

    /**
     * @throws Throwable
     */
    public function test_throws_exception_when_not_found(): void
    {
        $this->expectException(ServiceOrderNotFoundException::class);

        $action = new DeleteServiceOrderAction;
        $action('non-existent-id');
    }
}
