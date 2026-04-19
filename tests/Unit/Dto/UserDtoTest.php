<?php

namespace Tests\Unit\Dto;

use App\Dto\UserDto;
use PHPUnit\Framework\TestCase;

class UserDtoTest extends TestCase
{
    public function testToArrayIncludesMustChangePasswordWhenTrue(): void
    {
        $dto = new UserDto(
            name: 'Test',
            email: 'test@test.com',
            role: 'admin',
            must_change_password: true,
        );

        $this->assertArrayHasKey('must_change_password', $dto->toArray());
        $this->assertTrue($dto->toArray()['must_change_password']);
    }

    public function testToArrayExcludesMustChangePasswordWhenFalse(): void
    {
        $dto = new UserDto(
            name: 'Test',
            email: 'test@test.com',
            role: 'admin',
            must_change_password: false,
        );

        $this->assertArrayNotHasKey('must_change_password', $dto->toArray());
    }

    public function testFromArrayMapsMustChangePassword(): void
    {
        $dto = UserDto::fromArray([
            'name' => 'Test',
            'email' => 'test@test.com',
            'role' => 'admin',
            'must_change_password' => true,
        ]);

        $this->assertTrue($dto->must_change_password);
    }
}
