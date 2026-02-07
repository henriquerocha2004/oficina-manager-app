<?php

namespace Tests\Unit\Actions;

use Tests\TestCase;
use App\Actions\Tenant\Service\CreateServiceAction;
use App\Dto\ServiceDto;
use App\Exceptions\Service\ServiceAlreadyExistsException;
use App\Models\Tenant\Service;

class CreateServiceActionTest extends TestCase
{
    public function testCreatesServiceWhenNotExists(): void
    {
        $data = [
            'name' => 'Troca de óleo',
            'description' => 'Troca completa de óleo do motor',
            'base_price' => 150.00,
            'category' => Service::CATEGORY_MAINTENANCE,
            'estimated_time' => 60,
            'is_active' => true,
        ];

        $serviceDto = ServiceDto::fromArray($data);

        $action = new CreateServiceAction();
        $result = $action($serviceDto);

        $this->assertInstanceOf(Service::class, $result);
        $this->assertDatabaseHas('services', ['name' => $data['name']]);
    }

    public function testThrowsWhenServiceAlreadyExists(): void
    {
        $data = [
            'name' => 'Troca de óleo',
            'description' => 'Troca completa de óleo do motor',
            'base_price' => 150.00,
            'category' => Service::CATEGORY_MAINTENANCE,
            'estimated_time' => 60,
            'is_active' => true,
        ];

        Service::create($data);

        $serviceDto = ServiceDto::fromArray($data);

        $this->expectException(ServiceAlreadyExistsException::class);

        (new CreateServiceAction())($serviceDto);
    }
}
