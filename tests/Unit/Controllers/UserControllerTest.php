<?php

namespace Tests\Unit\Controllers;

use App\Actions\Tenant\User\CreateUserAction;
use App\Dto\UserDto;
use App\Exceptions\User\UserAlreadyExistsException;
use App\Http\Controllers\tenant\UserController;
use App\Http\Requests\tenant\UserRequest;
use App\Models\Tenant\User;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testStoreReturnsCreatedResponse(): void
    {
        $userDto = UserDto::fromArray([
            'name' => 'Controller User',
            'email' => 'controller.user@example.com',
            'role' => 'administrator',
            'password' => '12345678',
            'is_active' => true,
        ]);

        /** @var UserRequest&MockInterface $requestMock */
        $requestMock = Mockery::mock(UserRequest::class);
        $requestMock->shouldReceive('toDto')->andReturn($userDto);
        $requestMock->shouldReceive('file')->with('avatar')->andReturn(null);
        $requestMock->shouldReceive('boolean')->with('remove_avatar')->andReturn(false);

        $createdUser = new User();

        /** @var CreateUserAction&MockInterface $createAction */
        $createAction = Mockery::mock(CreateUserAction::class);
        $createAction->shouldReceive('__invoke')->andReturn($createdUser);

        $controller = new UserController();
        $response = $controller->store($requestMock, $createAction);

        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $payload = $response->getData(true);
        $this->assertArrayHasKey('data', $payload);
        $this->assertArrayHasKey('user', $payload['data']);
    }

    public function testStoreReturnsConflictWhenUserAlreadyExists(): void
    {
        $userDto = UserDto::fromArray([
            'name' => 'Controller User',
            'email' => 'controller.user@example.com',
            'role' => 'administrator',
            'password' => '12345678',
            'is_active' => true,
        ]);

        /** @var UserRequest&MockInterface $requestMock */
        $requestMock = Mockery::mock(UserRequest::class);
        $requestMock->shouldReceive('toDto')->andReturn($userDto);
        $requestMock->shouldReceive('file')->with('avatar')->andReturn(null);
        $requestMock->shouldReceive('boolean')->with('remove_avatar')->andReturn(false);

        /** @var CreateUserAction&MockInterface $createAction */
        $createAction = Mockery::mock(CreateUserAction::class);
        $createAction
            ->shouldReceive('__invoke')
            ->andThrow(new UserAlreadyExistsException());

        $controller = new UserController();
        $response = $controller->store($requestMock, $createAction);

        $this->assertSame(Response::HTTP_CONFLICT, $response->getStatusCode());
    }
}
