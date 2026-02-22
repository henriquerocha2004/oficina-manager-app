<?php

namespace Tests\Unit\Actions;

use App\Actions\Tenant\ServiceOrder\AddItemAction;
use App\Actions\Tenant\ServiceOrder\CreateServiceOrderAction;
use App\Actions\Tenant\ServiceOrder\RegisterPaymentAction;
use App\Domain\Tenant\ServiceOrder\ServiceOrderDomain;
use App\Dto\PaymentDto;
use App\Dto\ServiceOrderDto;
use App\Enum\Tenant\ServiceOrder\PaymentMethodEnum;
use App\Enum\Tenant\ServiceOrder\ServiceOrderItemTypeEnum;
use App\Models\Tenant\Client;
use App\Models\Tenant\ServiceOrder;
use App\Models\Tenant\ServiceOrderItem;
use App\Models\Tenant\User;
use App\Models\Tenant\Vehicle;
use App\Services\Tenant\ServiceOrder\PaymentService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class RegisterPaymentActionTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @throws BindingResolutionException
     * @throws \Throwable
     */
    private function createServiceOrderWithItem(): ServiceOrder
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

        $item = new ServiceOrderItem([
            'type' => ServiceOrderItemTypeEnum::SERVICE,
            'description' => 'Oil change',
            'quantity' => 1,
            'unit_price' => 100.00,
            'subtotal' => 100.00,
        ]);

        $addAction = new AddItemAction($domain);

        return $addAction($serviceOrder->id, $user->id, $item);
    }

    /**
     * @throws BindingResolutionException
     * @throws \Throwable
     */
    public function test_registers_payment(): void
    {
        $serviceOrder = $this->createServiceOrderWithItem();
        $user = User::factory()->create();

        $dto = new PaymentDto(
            service_order_id: $serviceOrder->id,
            payment_method: PaymentMethodEnum::CASH,
            amount: 50.00,
            notes: 'Partial payment'
        );

        $action = new RegisterPaymentAction(
            $this->app->make(PaymentService::class)
        );

        $payment = $action($serviceOrder->id, $user->id, $dto);

        $this->assertNotNull($payment->id);
        $this->assertEquals(50.00, $payment->amount);
        $this->assertEquals(PaymentMethodEnum::CASH, $payment->payment_method);

        $serviceOrder->refresh();
        $this->assertEquals(50.00, $serviceOrder->paid_amount);
        $this->assertEquals(50.00, $serviceOrder->outstanding_balance);
    }

    /**
     * @throws \Throwable
     * @throws BindingResolutionException
     */
    public function test_full_payment_completes_service_order(): void
    {
        $serviceOrder = $this->createServiceOrderWithItem();
        $user = User::factory()->create();

        $dto = new PaymentDto(
            service_order_id: $serviceOrder->id,
            payment_method: PaymentMethodEnum::CASH,
            amount: 100.00,
            notes: 'Full payment'
        );

        $action = new RegisterPaymentAction(
            $this->app->make(PaymentService::class)
        );

        $payment = $action($serviceOrder->id, $user->id, $dto);

        $this->assertNotNull($payment->id);

        $serviceOrder->refresh();
        $this->assertEquals(100.00, $serviceOrder->paid_amount);
        $this->assertEquals(0, $serviceOrder->outstanding_balance);
    }
}
