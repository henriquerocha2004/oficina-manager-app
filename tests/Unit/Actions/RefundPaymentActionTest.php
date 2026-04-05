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
use App\Enum\Tenant\ServiceOrder\PaymentTypeEnum;
use App\Enum\Tenant\ServiceOrder\ServiceOrderItemTypeEnum;
use App\Enum\Tenant\ServiceOrder\ServiceOrderStatusEnum;
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

class RefundPaymentActionTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Creates a service order with one item (total = R$100) and one payment of R$50.
     * Returns [$serviceOrder, $user].
     *
     * @throws Throwable
     * @throws BindingResolutionException
     */
    private function createServiceOrderWithPayment(): array
    {
        $client  = Client::factory()->create();
        $vehicle = Vehicle::factory()->create();
        $user    = User::factory()->create();

        $domain = new ServiceOrderDomain($this->app->make(PaymentService::class));

        $serviceOrder = (new CreateServiceOrderAction($domain))(new ServiceOrderDto(
            client_id: $client->id,
            vehicle_id: $vehicle->id,
            created_by: $user->id,
        ));

        $item = new ServiceOrderItem([
            'type'        => ServiceOrderItemTypeEnum::SERVICE,
            'description' => 'Oil change',
            'quantity'    => 1,
            'unit_price'  => 100.00,
            'subtotal'    => 100.00,
        ]);

        $serviceOrder = (new AddItemAction($domain))($serviceOrder->id, $user->id, $item);

        $paymentDto = new PaymentDto(
            service_order_id: $serviceOrder->id,
            payment_method: PaymentMethodEnum::CASH,
            amount: 50.00,
        );

        (new RegisterPaymentAction($this->app->make(PaymentService::class)))
            ($serviceOrder->id, $user->id, $paymentDto);

        return [$serviceOrder->refresh(), $user];
    }

    /**
     * @throws Throwable
     */
    public function testRefundCreatesNewRecord(): void
    {
        [$serviceOrder, $user] = $this->createServiceOrderWithPayment();

        $action = new RefundPaymentAction($this->app->make(PaymentService::class));
        $action($serviceOrder->id, $user->id, 30.00, PaymentMethodEnum::PIX, 'Motivo teste');

        $this->assertDatabaseHas('service_order_payments', [
            'service_order_id' => $serviceOrder->id,
            'type'             => PaymentTypeEnum::REFUND->value,
            'amount'           => 30.00,
        ]);

        $serviceOrder->refresh();
        $this->assertEquals(20.00, $serviceOrder->paid_amount);
        $this->assertEquals(80.00, $serviceOrder->outstanding_balance);
    }

    /**
     * @throws Throwable
     */
    public function testFullRefundReopensCompletedOrder(): void
    {
        [$serviceOrder, $user] = $this->createServiceOrderWithPayment();

        // Move order to WAITING_PAYMENT so auto-complete can trigger
        $serviceOrder->status = ServiceOrderStatusEnum::WAITING_PAYMENT;
        $serviceOrder->save();

        // Pay the remaining R$50 to complete the order
        $payFull = new PaymentDto(
            service_order_id: $serviceOrder->id,
            payment_method: PaymentMethodEnum::CASH,
            amount: 50.00,
        );
        (new RegisterPaymentAction($this->app->make(PaymentService::class)))
            ($serviceOrder->id, $user->id, $payFull);

        $serviceOrder->refresh();
        $this->assertEquals(ServiceOrderStatusEnum::COMPLETED, $serviceOrder->status);

        // Refund the full amount
        $action = new RefundPaymentAction($this->app->make(PaymentService::class));
        $action($serviceOrder->id, $user->id, 100.00, PaymentMethodEnum::CASH);

        $serviceOrder->refresh();
        $this->assertEquals(ServiceOrderStatusEnum::WAITING_PAYMENT, $serviceOrder->status);
        $this->assertEquals(0, $serviceOrder->paid_amount);
    }

    /**
     * @throws Throwable
     */
    public function testRefundExceedingPaidAmountThrows(): void
    {
        [$serviceOrder, $user] = $this->createServiceOrderWithPayment(); // paid_amount = 50

        $this->expectException(\Exception::class);

        $action = new RefundPaymentAction($this->app->make(PaymentService::class));
        $action($serviceOrder->id, $user->id, 99.00, PaymentMethodEnum::CASH);
    }
}
