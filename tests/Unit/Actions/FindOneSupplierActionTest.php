<?php

namespace Tests\Unit\Actions;

use App\Actions\Tenant\Supplier\CreateSupplierAction;
use App\Actions\Tenant\Supplier\FindOneSupplierAction;
use App\Dto\SupplierDto;
use App\Exceptions\Supplier\SupplierNotFoundException;
use App\Models\Tenant\Supplier;
use Symfony\Component\Uid\Ulid;
use Tests\TestCase;
use Throwable;

class FindOneSupplierActionTest extends TestCase
{
    /**
     * @throws Throwable
     */
    public function testFindsSupplierById(): void
    {
        $data = [
            'name' => 'Fornecedor Delta Ltda',
            'document_number' => '77777777000177',
            'email' => 'delta@fornecedor.com',
            'phone' => '4444444444',
        ];

        $createAction = new CreateSupplierAction;
        $supplier = $createAction(SupplierDto::fromArray($data));

        $ulid = Ulid::fromString($supplier->id);

        $action = new FindOneSupplierAction;
        $result = $action($ulid);

        $this->assertInstanceOf(Supplier::class, $result);
        $this->assertEquals($supplier->id, $result->id);
        $this->assertEquals($data['name'], $result->name);
    }

    public function testThrowsWhenSupplierNotFound(): void
    {
        $this->expectException(SupplierNotFoundException::class);

        $ulid = Ulid::fromString(Ulid::generate());

        $action = new FindOneSupplierAction;
        $action($ulid);
    }
}
