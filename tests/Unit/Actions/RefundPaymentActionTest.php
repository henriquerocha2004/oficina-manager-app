<?php

namespace Tests\Unit\Actions;

use App\Actions\Tenant\ServiceOrder\AddItemAction;
use App\Actions\Tenant\ServiceOrder\CreateServiceOrderAction;
use App\Actions\Tenant\ServiceOrder\RefundPaymentAction;
use App\Actions\Tenant\ServiceOrder\RegisterPaymentAction;
use App\Domain\Tenant\ServiceOrder\ServiceOrderDomain;
use App\Dto\PaymentDto;
use App\Dto\ServiceOrderDto;
use App\Enum\Tenant\ServiceOrder\PaymentMethodEnum;
use App\Enum\Tenant\ServiceOrder\ServiceOrderItemTypeEnum;
use App\Models\Tenant\Client;
use App\Models\Tenant\ServiceOrderItem;
use App\Models\Tenant\User;
use App\Models\Tenant\Vehicle;
use App\Services\Tenant\ServiceOrder\PaymentService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Throwable;

class RefundPaymentActionTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @throws Throwable
     * @throws BindingResolutionException
     */
    private function createServiceOrderWithPayment(): array
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
        $serviceOrder = $addAction($serviceOrder->id, $user->id, $item);

        $paymentDto = new PaymentDto(
            service_order_id: $serviceOrder->id,
            payment_method: PaymentMethodEnum::CASH,
            amount: 50.00,
            notes: 'Partial payment'
        );

        $registerAction = new RegisterPaymentAction(
            $this->app->make(PaymentService::class)
        );

        $payment = $registerAction($serviceOrder->id, $user->id, $paymentDto);

        return [$serviceOrder->refresh(), $payment, $user];
    }

    /**
     * @throws Throwable
     * @throws BindingResolutionException
     */
    public function test_refunds_payment(): void
    {
        [$serviceOrder, $payment, $user] = $this->createServiceOrderWithPayment();

        $action = new RefundPaymentAction(
            $this->app->make(PaymentService::class)
        );

        $result = $action($serviceOrder->id, $payment->id, $user->id, 'Client requested refund');

        $this->assertNotNull($result);

        $serviceOrder->refresh();
        $this->assertEquals(0, $serviceOrder->paid_amount);
        $this->assertEquals(100.00, $serviceOrder->outstanding_balance);
    }

    /**
     * @throws BindingResolutionException
     * @throws Throwable
     */
    public function test_partial_refund_updates_amounts(): void
    {
        [$serviceOrder, $payment, $user] = $this->createServiceOrderWithPayment();

        $this->assertEquals(50.00, $serviceOrder->paid_amount);
        $this->assertEquals(50.00, $serviceOrder->outstanding_balance);

        $action = new RefundPaymentAction(
            $this->app->make(PaymentService::class)
        );

        $result = $action($serviceOrder->id, $payment->id, $user->id, 'Partial refund');

        $this->assertNotNull($result);
    }
}
