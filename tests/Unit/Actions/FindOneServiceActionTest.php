<?php

namespace Tests\Unit\Actions;

use Tests\TestCase;
use Illuminate\Support\Str;
use App\Models\Tenant\Service;
use Symfony\Component\Uid\Ulid;
use App\Actions\Tenant\Service\FindOneServiceAction;
use App\Exceptions\Service\ServiceNotFoundException;

class FindOneServiceActionTest extends TestCase
{
    public function testFindsServiceWhenExists(): void
    {
        $service = Service::factory()->create([
            'name' => 'Troca de óleo',
        ]);

        $serviceId = Ulid::fromString($service->id);

        $action = new FindOneServiceAction();
        $result = $action($serviceId);

        $this->assertInstanceOf(Service::class, $result);
        $this->assertEquals($service->id, $result->id);
        $this->assertEquals('Troca de óleo', $result->name);
    }

    public function testThrowsWhenServiceNotFound(): void
    {
        $nonExistentId = Ulid::fromString((string) Str::ulid());

        $this->expectException(ServiceNotFoundException::class);

        (new FindOneServiceAction())($nonExistentId);
    }
}
