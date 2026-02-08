<?php

namespace Tests\Unit\Actions;

use App\Actions\Tenant\Supplier\CreateSupplierAction;
use App\Actions\Tenant\Supplier\DeleteSupplierAction;
use App\Dto\SupplierDto;
use App\Exceptions\Supplier\SupplierNotFoundException;
use Symfony\Component\Uid\Ulid;
use Tests\TestCase;
use Throwable;

class DeleteSupplierActionTest extends TestCase
{
    /**
     * @throws Throwable
     */
    public function testDeletesSupplierWhenFound(): void
    {
        $data = [
            'name' => 'Fornecedor Para Deletar Ltda',
            'document_number' => '33333333000133',
            'email' => 'deletar@fornecedor.com',
            'phone' => '1199990000',
        ];

        $createAction = new CreateSupplierAction;
        $supplier = $createAction(SupplierDto::fromArray($data));

        $this->assertDatabaseHas('supplier', ['document_number' => $data['document_number']]);

        $ulid = Ulid::fromString($supplier->id);

        $action = new DeleteSupplierAction;
        $action($ulid);

        $this->assertSoftDeleted('supplier', ['id' => $supplier->id]);
    }

    public function testThrowsWhenNotFound(): void
    {
        $this->expectException(SupplierNotFoundException::class);

        $ulid = Ulid::fromString((string) Ulid::generate());

        $action = new DeleteSupplierAction;
        $action($ulid);
    }
}
