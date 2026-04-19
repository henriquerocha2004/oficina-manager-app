<?php

namespace Tests\Feature\Middleware;

use App\Models\Tenant\User;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class EnsurePasswordIsChangedTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::domain('{subdomain}.' . config('app.base_domain'))
            ->middleware(['web', 'auth:tenant', 'password.change'])
            ->get('/probe', fn () => response()->json(['ok' => true]))
            ->name('tenant.probe');
    }

    public function testPassesThroughWhenFlagIsFalse(): void
    {
        $user = User::factory()->create(['must_change_password' => false]);

        $response = $this->actingAs($user, 'tenant')
            ->getJson('http://alpha.localhost/probe');

        $response->assertOk()->assertJson(['ok' => true]);
    }

    public function testRedirectsWhenFlagIsTrue(): void
    {
        $user = User::factory()->create(['must_change_password' => true]);

        $response = $this->actingAs($user, 'tenant')
            ->getJson('http://alpha.localhost/probe');

        $response->assertRedirectContains('/change-password');
    }

    public function testDoesNotRedirectOnChangePasswordRoute(): void
    {
        $user = User::factory()->create(['must_change_password' => true]);

        $response = $this->actingAs($user, 'tenant')
            ->getJson('http://alpha.localhost/change-password');

        // Deve retornar 200 (página) ou redirecionar para si mesma, nunca loop
        $this->assertNotSame('http://alpha.localhost/probe', $response->headers->get('Location'));
    }
}
