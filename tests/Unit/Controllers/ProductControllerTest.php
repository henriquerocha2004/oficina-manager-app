<?php

namespace Tests\Unit\Controllers;

use App\Actions\Tenant\Product\CreateProductAction;
use App\Actions\Tenant\Product\DeleteProductAction;
use App\Actions\Tenant\Product\FindOneProductAction;
use App\Actions\Tenant\Product\UpdateProductAction;
use App\Dto\ProductDto;
use App\Http\Controllers\tenant\ProductController;
use App\Http\Requests\tenant\ProductRequest;
use App\Models\Tenant\Product;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;
use Throwable;

class ProductControllerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @throws Throwable
     */
    public function testStoreReturnsCreatedResponse(): void
    {
        $data = [
            'name' => 'Test Product',
            'category' => 'engine',
            'unit' => 'unit',
            'unit_price' => 150.00,
        ];

        $productDto = ProductDto::fromArray($data);

        /** @var ProductRequest&MockInterface $requestMock */
        $requestMock = Mockery::mock(ProductRequest::class);
        $requestMock->shouldReceive('toDto')->andReturn($productDto);

        $createdProduct = new Product;
        $createdProduct->id = '01HQWXYZ1234567890ABCDEFGH';
        $createdProduct->name = 'Test Product';

        /** @var CreateProductAction&MockInterface $createAction */
        $createAction = Mockery::mock(CreateProductAction::class);
        $createAction->shouldReceive('__invoke')->andReturn($createdProduct);

        $controller = new ProductController;

        $response = $controller->store($requestMock, $createAction);

        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $payload = $response->getData(true);
        $this->assertArrayHasKey('data', $payload);
        $this->assertArrayHasKey('product', $payload['data']);
    }

    public function testUpdateReturnsOkResponse(): void
    {
        $data = [
            'name' => 'Updated Product',
            'category' => 'brakes',
            'unit' => 'box',
            'unit_price' => 200.00,
        ];

        $productDto = ProductDto::fromArray($data);

        /** @var ProductRequest&MockInterface $requestMock */
        $requestMock = Mockery::mock(ProductRequest::class);
        $requestMock->shouldReceive('toDto')->andReturn($productDto);

        /** @var UpdateProductAction&MockInterface $updateAction */
        $updateAction = Mockery::mock(UpdateProductAction::class);
        $updateAction->shouldReceive('__invoke')->andReturnNull();

        $controller = new ProductController;

        $response = $controller->update($requestMock, '01HQWXYZ1234567890ABCDEFGH', $updateAction);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testDeleteReturnsOkResponse(): void
    {
        /** @var DeleteProductAction&MockInterface $deleteAction */
        $deleteAction = Mockery::mock(DeleteProductAction::class);
        $deleteAction->shouldReceive('__invoke')->andReturnNull();

        $controller = new ProductController;

        $response = $controller->delete('01HQWXYZ1234567890ABCDEFGH', $deleteAction);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testFindOneReturnsProductData(): void
    {
        $product = new Product;
        $product->id = '01HQWXYZ1234567890ABCDEFGH';
        $product->name = 'Specific Product';

        /** @var FindOneProductAction&MockInterface $findOneAction */
        $findOneAction = Mockery::mock(FindOneProductAction::class);
        $findOneAction->shouldReceive('__invoke')->andReturn($product);

        $controller = new ProductController;

        $response = $controller->findOne('01HQWXYZ1234567890ABCDEFGH', $findOneAction);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $payload = $response->getData(true);
        $this->assertArrayHasKey('data', $payload);
        $this->assertArrayHasKey('product', $payload['data']);
    }
}
