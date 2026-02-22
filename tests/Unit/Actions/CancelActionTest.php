<?php

namespace Tests\Unit\Actions;

use App\Actions\Tenant\ServiceOrder\CancelAction;
use App\Actions\Tenant\ServiceOrder\CreateServiceOrderAction;
use App\Domain\Tenant\ServiceOrder\ServiceOrderDomain;
use App\Dto\ServiceOrderDto;
use App\Enum\Tenant\ServiceOrder\ServiceOrderStatusEnum;
use App\Exceptions\ServiceOrder\ServiceOrderAlreadyCancelledException;
use App\Exceptions\ServiceOrder\ServiceOrderAlreadyCompletedException;
use App\Models\Tenant\Client;
use App\Models\Tenant\ServiceOrder;
use App\Models\Tenant\User;
use App\Models\Tenant\Vehicle;
use App\Services\Tenant\ServiceOrder\PaymentService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Throwable;

class CancelActionTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @throws Throwable
     * @throws BindingResolutionException
     */
    private function createServiceOrder(): ServiceOrder
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

        return $createAction($dto);
    }

    /**
     * @throws BindingResolutionException
     * @throws Throwable
     */
    public function test_cancels_service_order(): void
    {
        $serviceOrder = $this->createServiceOrder();
        $user = User::factory()->create();

        $domain = new ServiceOrderDomain(
            $this->app->make(PaymentService::class)
        );

        $action = new CancelAction($domain);
        $result = $action($serviceOrder->id, $user->id, 'Client requested cancellation');

        $this->assertEquals(ServiceOrderStatusEnum::CANCELLED, $result->status);
        $this->assertNotNull($result->cancelled_at);
    }

    /**
     * @throws Throwable
     * @throws BindingResolutionException
     */
    public function test_throws_exception_when_already_completed(): void
    {
        $serviceOrder = $this->createServiceOrder();
        $serviceOrder->status = ServiceOrderStatusEnum::COMPLETED;
        $serviceOrder->save();

        $user = User::factory()->create();

        $domain = new ServiceOrderDomain(
            $this->app->make(PaymentService::class)
        );

        $this->expectException(ServiceOrderAlreadyCompletedException::class);

        $action = new CancelAction($domain);
        $action($serviceOrder->id, $user->id, 'Too late');
    }

    /**
     * @throws BindingResolutionException
     * @throws Throwable
     */
    public function test_throws_exception_when_already_cancelled(): void
    {
        $serviceOrder = $this->createServiceOrder();
        $serviceOrder->status = ServiceOrderStatusEnum::CANCELLED;
        $serviceOrder->save();

        $user = User::factory()->create();

        $domain = new ServiceOrderDomain(
            $this->app->make(PaymentService::class)
        );

        $this->expectException(ServiceOrderAlreadyCancelledException::class);

        $action = new CancelAction($domain);
        $action($serviceOrder->id, $user->id, 'Already cancelled');
    }
}
