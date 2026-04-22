<?php

namespace Tests\Unit\Actions;

use App\Actions\Tenant\ServiceOrder\CreateServiceOrderAction;
use App\Actions\Tenant\ServiceOrder\UpdateDiagnosisAction;
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

class UpdateDiagnosisActionTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @throws \Throwable
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
            diagnosis: 'Initial diagnosis',
        );

        $domain = new ServiceOrderDomain(
            $this->app->make(PaymentService::class)
        );

        $createAction = new CreateServiceOrderAction($domain);

        return $createAction($dto);
    }

    public function test_updates_diagnosis(): void
    {
        $serviceOrder = $this->createServiceOrder();
        $user = User::factory()->create();

        $domain = new ServiceOrderDomain(
            $this->app->make(PaymentService::class)
        );

        $action = new UpdateDiagnosisAction($domain);
        $result = $action($serviceOrder->id, $user->id, 'Updated diagnosis - engine issue');

        $this->assertEquals('Updated diagnosis - engine issue', $result->technical_diagnosis);
    }

    public function test_throws_exception_when_completed(): void
    {
        $serviceOrder = $this->createServiceOrder();
        $serviceOrder->status = ServiceOrderStatusEnum::COMPLETED;
        $serviceOrder->save();

        $user = User::factory()->create();

        $domain = new ServiceOrderDomain(
            $this->app->make(PaymentService::class)
        );

        $this->expectException(ServiceOrderAlreadyCompletedException::class);

        $action = new UpdateDiagnosisAction($domain);
        $action($serviceOrder->id, $user->id, 'New diagnosis');
    }

    public function test_throws_exception_when_cancelled(): void
    {
        $serviceOrder = $this->createServiceOrder();
        $serviceOrder->status = ServiceOrderStatusEnum::CANCELLED;
        $serviceOrder->save();

        $user = User::factory()->create();

        $domain = new ServiceOrderDomain(
            $this->app->make(PaymentService::class)
        );

        $this->expectException(ServiceOrderAlreadyCancelledException::class);

        $action = new UpdateDiagnosisAction($domain);
        $action($serviceOrder->id, $user->id, 'New diagnosis');
    }
}
