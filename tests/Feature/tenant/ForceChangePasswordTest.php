<?php

namespace Tests\Feature\tenant;

use App\Models\Tenant\User;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ForceChangePasswordTest extends TestCase
{
    use DatabaseTransactions;

    protected function tenantRequest(string $method, string $uri, array $data = []): \Illuminate\Testing\TestResponse
    {
        return match (strtoupper($method)) {
            'GET' => $this->withoutMiddleware([Authenticate::class])->getJson(
                'http://test-tenant.localhost' . $uri
            ),
            'POST' => $this->withoutMiddleware([Authenticate::class])->postJson(
                'http://test-tenant.localhost' . $uri,
                $data
            ),
            default => throw new \InvalidArgumentException("Unsupported HTTP method: $method"),
        };
    }

    protected function tenantRequestAs(User $user, string $method, string $uri, array $data = []): \Illuminate\Testing\TestResponse
    {
        return match (strtoupper($method)) {
            'GET' => $this->actingAs($user, 'tenant')->getJson(
                'http://test-tenant.localhost' . $uri
            ),
            'POST' => $this->actingAs($user, 'tenant')->postJson(
                'http://test-tenant.localhost' . $uri,
                $data
            ),
            default => throw new \InvalidArgumentException("Unsupported HTTP method: $method"),
        };
    }

    public function testCanAccessChangePasswordPage(): void
    {
        $user = User::factory()->create(['must_change_password' => true]);

        $response = $this->tenantRequestAs($user, 'GET', '/change-password');

        $response->assertStatus(200);
    }

    public function testSubmittingFormUpdatesPasswordAndClearsFlag(): void
    {
        $user = User::factory()->create(['must_change_password' => true]);

        $response = $this->tenantRequestAs($user, 'POST', '/change-password', [
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect();

        $user->refresh();
        $this->assertFalse($user->must_change_password);
    }

    public function testSubmittingFormValidatesPassword(): void
    {
        $user = User::factory()->create(['must_change_password' => true]);

        $response = $this->tenantRequestAs($user, 'POST', '/change-password', [
            'password' => 'newpassword123',
            'password_confirmation' => 'different-password',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
    }

    public function testSubmittingFormValidatesMinLength(): void
    {
        $user = User::factory()->create(['must_change_password' => true]);

        $response = $this->tenantRequestAs($user, 'POST', '/change-password', [
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
    }

    public function testProtectedRoutesRedirectToChangePasswordWhenFlagIsTrue(): void
    {
        $user = User::factory()->create([
            'role' => 'administrator',
            'must_change_password' => true,
        ]);

        $response = $this->tenantRequestAs($user, 'GET', '/clients');

        $response->assertRedirectContains('/change-password');
    }

    public function testProtectedRoutesAreAccessibleAfterPasswordChange(): void
    {
        $user = User::factory()->create([
            'role' => 'administrator',
            'must_change_password' => false,
        ]);

        $response = $this->tenantRequestAs($user, 'GET', '/clients');

        $response->assertStatus(200);
    }
}
