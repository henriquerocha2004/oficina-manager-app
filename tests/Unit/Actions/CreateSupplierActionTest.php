<?php

namespace Tests\Unit\Actions;

use App\Actions\Tenant\Supplier\CreateSupplierAction;
use App\Dto\SupplierDto;
use App\Exceptions\Supplier\SupplierAlreadyExistsException;
use App\Models\Tenant\Supplier;
use Tests\TestCase;
use Throwable;

class CreateSupplierActionTest extends TestCase
{
    /**
     * @throws Throwable
     */
    public function testCreatesSupplierWhenNotExists(): void
    {
        $data = [
            'name' => 'Fornecedor Teste Ltda',
            'document_number' => '12345678000190',
            'email' => 'contato@fornecedor.com',
            'phone' => '1133334444',
        ];

        $supplierDto = SupplierDto::fromArray($data);

        $action = new CreateSupplierAction;
        $result = $action($supplierDto);

        $this->assertInstanceOf(Supplier::class, $result);
        $this->assertDatabaseHas('supplier', ['document_number' => $data['document_number']]);
    }

    /**
     * @throws Throwable
     */
    public function testThrowsWhenSupplierAlreadyExists(): void
    {
        $data = [
            'name' => 'Fornecedor Duplicado Ltda',
            'document_number' => '98765432000100',
            'email' => 'duplicado@fornecedor.com',
            'phone' => '1144445555',
        ];

        Supplier::create($data);

        $supplierDto = SupplierDto::fromArray($data);

        $this->expectException(SupplierAlreadyExistsException::class);

        (new CreateSupplierAction)($supplierDto);
    }
}
