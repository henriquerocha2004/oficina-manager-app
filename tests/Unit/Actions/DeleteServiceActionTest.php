<?php

namespace Tests\Unit\Actions;

use Tests\TestCase;
use Illuminate\Support\Str;
use App\Models\Tenant\Service;
use Symfony\Component\Uid\Ulid;
use App\Actions\Tenant\Service\DeleteServiceAction;
use App\Exceptions\Service\ServiceNotFoundException;

class DeleteServiceActionTest extends TestCase
{
    public function testDeletesServiceWhenExists(): void
    {
        $service = Service::factory()->create();

        $serviceId = Ulid::fromString($service->id);

        $action = new DeleteServiceAction();
        $action($serviceId);

        $this->assertSoftDeleted('services', [
            'id' => $service->id,
        ]);
    }

    public function testThrowsWhenServiceNotFound(): void
    {
        $nonExistentId = Ulid::fromString((string) Str::ulid());

        $this->expectException(ServiceNotFoundException::class);

        (new DeleteServiceAction())($nonExistentId);
    }
}
