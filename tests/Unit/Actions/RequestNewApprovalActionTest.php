<?php

namespace Tests\Unit\Actions;

use App\Actions\Tenant\ServiceOrder\AddItemAction;
use App\Actions\Tenant\ServiceOrder\ApproveAction;
use App\Actions\Tenant\ServiceOrder\CreateServiceOrderAction;
use App\Actions\Tenant\ServiceOrder\RequestNewApprovalAction;
use App\Actions\Tenant\ServiceOrder\SendForApprovalAction;
use App\Actions\Tenant\ServiceOrder\StartWorkAction;
use App\Domain\Tenant\ServiceOrder\ServiceOrderDomain;
use App\Dto\ServiceOrderDto;
use App\Enum\Tenant\ServiceOrder\ServiceOrderItemTypeEnum;
use App\Enum\Tenant\ServiceOrder\ServiceOrderStatusEnum;
use App\Exceptions\ServiceOrder\InvalidStatusTransitionException;
use App\Exceptions\ServiceOrder\ServiceOrderNotFoundException;
use App\Models\Tenant\Client;
use App\Models\Tenant\ServiceOrder;
use App\Models\Tenant\ServiceOrderItem;
use App\Models\Tenant\User;
use App\Models\Tenant\Vehicle;
use App\Services\Tenant\ServiceOrder\PaymentService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Throwable;

class RequestNewApprovalActionTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @throws Throwable
     * @throws BindingResolutionException
     */
    private function createInProgressServiceOrder(): ServiceOrder
    {
        $client = Client::factory()->create();
        $vehicle = Vehicle::factory()->create();
        $user = User::factory()->create();

        $domain = new ServiceOrderDomain(
            $this->app->make(PaymentService::class)
        );

        $dto = new ServiceOrderDto(
            client_id: $client->id,
            vehicle_id: $vehicle->id,
            created_by: $user->id,
        );

        $serviceOrder = (new CreateServiceOrderAction($domain))($dto);

        $item = new ServiceOrderItem([
            'type' => ServiceOrderItemTypeEnum::SERVICE,
            'description' => 'Oil change',
            'quantity' => 1,
            'unit_price' => 100.00,
            'subtotal' => 100.00,
        ]);

        $serviceOrder = (new AddItemAction($domain))($serviceOrder->id, $user->id, $item);
        $serviceOrder = (new SendForApprovalAction($domain))($serviceOrder->id, $user->id);
        $serviceOrder = (new ApproveAction($domain))($serviceOrder->id, $user->id);

        return (new StartWorkAction($domain))($serviceOrder->id, $user->id);
    }

    /**
     * @throws Throwable
     * @throws BindingResolutionException
     */
    public function testRequestsNewApprovalSuccessfully(): void
    {
        $serviceOrder = $this->createInProgressServiceOrder();
        $user = User::factory()->create();

        $domain = new ServiceOrderDomain($this->app->make(PaymentService::class));
        $action = new RequestNewApprovalAction($domain);

        $result = $action($serviceOrder->id, $user->id);

        $this->assertEquals(ServiceOrderStatusEnum::WAITING_APPROVAL, $result->status);
        $this->assertNull($result->approved_at);
    }

    /**
     * @throws Throwable
     * @throws BindingResolutionException
     */
    public function testRequestsNewApprovalWithUpdatedDiagnosis(): void
    {
        $serviceOrder = $this->createInProgressServiceOrder();
        $user = User::factory()->create();

        $domain = new ServiceOrderDomain($this->app->make(PaymentService::class));
        $action = new RequestNewApprovalAction($domain);

        $result = $action($serviceOrder->id, $user->id, 'Updated diagnosis after inspection');

        $this->assertEquals(ServiceOrderStatusEnum::WAITING_APPROVAL, $result->status);
        $this->assertEquals('Updated diagnosis after inspection', $result->technical_diagnosis);
    }

    /**
     * @throws Throwable
     * @throws BindingResolutionException
     */
    public function testRequestsNewApprovalWithNewItems(): void
    {
        $serviceOrder = $this->createInProgressServiceOrder();
        $user = User::factory()->create();

        $domain = new ServiceOrderDomain($this->app->make(PaymentService::class));
        $action = new RequestNewApprovalAction($domain);

        $existingItemId = $serviceOrder->items->first()->id;

        $result = $action($serviceOrder->id, $user->id, null, [
            ['id' => $existingItemId],
            [
                'type' => 'part',
                'description' => 'Air filter',
                'quantity' => 1,
                'unit_price' => 50.00,
                'service_id' => null,
                'product_id' => null,
            ],
        ]);

        $this->assertEquals(ServiceOrderStatusEnum::WAITING_APPROVAL, $result->status);
        $this->assertCount(2, $result->items);
    }

    /**
     * @throws Throwable
     * @throws BindingResolutionException
     */
    public function testThrowsExceptionWhenServiceOrderNotFound(): void
    {
        $user = User::factory()->create();

        $domain = new ServiceOrderDomain($this->app->make(PaymentService::class));
        $action = new RequestNewApprovalAction($domain);

        $this->expectException(ServiceOrderNotFoundException::class);

        $action('non-existent-id', $user->id);
    }

    /**
     * @throws Throwable
     * @throws BindingResolutionException
     */
    public function testThrowsExceptionWhenStatusIsNotInProgress(): void
    {
        $client = Client::factory()->create();
        $vehicle = Vehicle::factory()->create();
        $user = User::factory()->create();

        $domain = new ServiceOrderDomain($this->app->make(PaymentService::class));

        $dto = new ServiceOrderDto(
            client_id: $client->id,
            vehicle_id: $vehicle->id,
            created_by: $user->id,
        );

        $serviceOrder = (new CreateServiceOrderAction($domain))($dto);

        $this->expectException(InvalidStatusTransitionException::class);

        $action = new RequestNewApprovalAction($domain);
        $action($serviceOrder->id, $user->id);
    }
}
