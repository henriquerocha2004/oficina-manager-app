<?php

namespace Tests\Unit\Actions;

use App\Actions\Tenant\Supplier\CreateSupplierAction;
use App\Actions\Tenant\Supplier\UpdateSupplierAction;
use App\Dto\SupplierDto;
use App\Exceptions\Supplier\SupplierNotFoundException;
use Symfony\Component\Uid\Ulid;
use Tests\TestCase;
use Throwable;

class UpdateSupplierActionTest extends TestCase
{
    /**
     * @throws Throwable
     */
    public function testUpdatesSupplierWhenFound(): void
    {
        $data = [
            'name' => 'Fornecedor Original Ltda',
            'document_number' => '11111111000111',
            'email' => 'original@fornecedor.com',
            'phone' => '1155556666',
        ];

        $createAction = new CreateSupplierAction;
        $supplier = $createAction(SupplierDto::fromArray($data));

        $this->assertDatabaseHas('supplier', ['document_number' => $data['document_number']]);

        $supplierDto = SupplierDto::fromArray([
            'name' => 'Fornecedor Atualizado Ltda',
            'document_number' => '11111111000111',
            'email' => 'atualizado@fornecedor.com',
            'phone' => '1177778888',
        ]);

        $ulid = Ulid::fromString($supplier->id);

        $action = new UpdateSupplierAction;
        $action($supplierDto, $ulid);

        $this->assertDatabaseHas('supplier', ['id' => $supplier->id, 'name' => 'Fornecedor Atualizado Ltda']);
    }

    public function testThrowsWhenNotFound(): void
    {
        $supplierDto = SupplierDto::fromArray([
            'name' => 'Fornecedor Inexistente Ltda',
            'document_number' => '22222222000122',
            'email' => 'inexistente@fornecedor.com',
            'phone' => '1188889999',
        ]);

        $this->expectException(SupplierNotFoundException::class);

        $ulid = Ulid::fromString(Ulid::generate());

        $action = new UpdateSupplierAction;
        $action($supplierDto, $ulid);
    }
}
