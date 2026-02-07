<?php

namespace Tests\Unit\Actions;

use Tests\TestCase;
use App\Actions\Tenant\Service\UpdateServiceAction;
use App\Dto\ServiceDto;
use App\Exceptions\Service\ServiceNotFoundException;
use App\Models\Tenant\Service;
use Symfony\Component\Uid\Ulid;

class UpdateServiceActionTest extends TestCase
{
    public function testUpdatesServiceWhenExists(): void
    {
        $service = Service::factory()->create([
            'name' => 'Troca de óleo',
            'base_price' => 100.00,
            'category' => Service::CATEGORY_MAINTENANCE,
        ]);

        $updateData = [
            'name' => 'Troca de óleo completa',
            'description' => 'Serviço atualizado',
            'base_price' => 200.00,
            'category' => Service::CATEGORY_MAINTENANCE,
            'estimated_time' => 90,
            'is_active' => true,
        ];

        $serviceDto = ServiceDto::fromArray($updateData);
        $serviceId = Ulid::fromString($service->id);

        $action = new UpdateServiceAction();
        $action($serviceDto, $serviceId);

        $this->assertDatabaseHas('services', [
            'id' => $service->id,
            'name' => 'Troca de óleo completa',
            'base_price' => 200.00,
        ]);
    }

    public function testThrowsWhenServiceNotFound(): void
    {
        $updateData = [
            'name' => 'Serviço inexistente',
            'base_price' => 100.00,
            'category' => Service::CATEGORY_REPAIR,
        ];

        $serviceDto = ServiceDto::fromArray($updateData);
        $nonExistentId = Ulid::fromString((string) \Illuminate\Support\Str::ulid());

        $this->expectException(ServiceNotFoundException::class);

        (new UpdateServiceAction())($serviceDto, $nonExistentId);
    }
}
