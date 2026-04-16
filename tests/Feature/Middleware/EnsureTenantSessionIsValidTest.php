<?php

namespace Tests\Feature\Middleware;

use App\Models\Tenant\User;
use App\Services\Tenant\Auth\TenantSessionBinding;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class EnsureTenantSessionIsValidTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::domain('{subdomain}.' . config('app.base_domain'))
            ->middleware(['web', 'auth:tenant', 'tenant.auth.session'])
            ->get('/tenant-session-probe', fn () => response()->json(['ok' => true]))
            ->name('tenant.session.probe');
    }

    public function testStoresTenantBindingAfterTenantLogin(): void
    {
        $user = User::factory()->create([
            'email' => 'tenant@example.com',
        ]);

        $response = $this->post('http://alpha.localhost/login', [
            'email' => $user->email,
            'password' => 'password',
            'remember' => true,
        ]);

        $response->assertSessionHas(TenantSessionBinding::SESSION_KEY, 'alpha.localhost');
        $response->assertCookie(TenantSessionBinding::COOKIE_NAME, 'alpha.localhost');
    }

    public function testAllowsAuthenticatedTenantWhenBoundHostMatches(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'tenant')
            ->withSession([
                TenantSessionBinding::SESSION_KEY => 'alpha.localhost',
            ])
            ->getJson('http://alpha.localhost/tenant-session-probe');

        $response->assertOk()
            ->assertJson([
                'ok' => true,
            ]);

        $this->assertAuthenticatedAs($user, 'tenant');
    }

    public function testRejectsAuthenticatedTenantWhenBoundHostDoesNotMatch(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'tenant')
            ->withSession([
                TenantSessionBinding::SESSION_KEY => 'alpha.localhost',
            ])
            ->get('http://beta.localhost/tenant-session-probe');

        $response->assertRedirect(route('tenant.login', ['subdomain' => 'beta']));
        $this->assertGuest('tenant');
    }

    public function testLogoutClearsTenantBindingCookie(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'tenant')
            ->withSession([
                TenantSessionBinding::SESSION_KEY => 'alpha.localhost',
            ])
            ->withCookie(TenantSessionBinding::COOKIE_NAME, 'alpha.localhost')
            ->post('http://alpha.localhost/logout');

        $response->assertRedirect('/');
        $response->assertCookieExpired(TenantSessionBinding::COOKIE_NAME);
        $response->assertSessionMissing(TenantSessionBinding::SESSION_KEY);
    }
}
