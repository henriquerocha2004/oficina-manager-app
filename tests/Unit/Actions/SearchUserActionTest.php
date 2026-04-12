<?php

namespace Tests\Unit\Actions;

use App\Actions\Tenant\User\SearchUserAction;
use App\Dto\SearchDto;
use App\Models\Tenant\User;
use Tests\TestCase;

class SearchUserActionTest extends TestCase
{
    public function testReturnsFilteredUsers(): void
    {
        $alice = User::query()->create([
            'name' => 'Alice Silva',
            'email' => 'alice@example.com',
            'role' => 'administrator',
            'password' => '12345678',
            'is_active' => true,
        ]);

        User::query()->create([
            'name' => 'Bruno Souza',
            'email' => 'bruno@example.com',
            'role' => 'mechanic',
            'password' => '12345678',
            'is_active' => true,
        ]);

        $searchDto = new SearchDto(
            search: 'alice',
            per_page: 10,
            sort_by: 'created_at',
            sort_direction: 'desc',
            filters: [],
        );

        $result = (new SearchUserAction())($searchDto);

        $this->assertSame(1, $result->total());
        $this->assertSame('alice@example.com', $result->items()[0]->email);

        $resultWithoutAlice = (new SearchUserAction())(
            searchDto: $searchDto,
            excludedUserId: $alice->id,
        );

        $this->assertSame(0, $resultWithoutAlice->total());
    }
}
