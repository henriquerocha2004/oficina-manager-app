<?php

namespace Tests\Feature\Middleware;

use App\Enum\Admin\TenantStatus;
use App\Http\Middleware\CheckTenantStatus;
use App\Models\Admin\Tenant;
use App\Services\admin\TenantManager;
use Closure;
use Illuminate\Http\Request;
use Tests\AdminTestCase;

class CheckTenantStatusTest extends AdminTestCase
{
    private string $adminConnection;

    private function attachSessionToRequest(Request $request): Request
    {
        $session = app('session.store');
        $session->start();
        $request->setLaravelSession($session);

        return $request;
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminConnection = config('database.connections_names.admin');
    }

    public function testAllowsRequestWhenTenantIsActive(): void
    {
        $tenant = Tenant::factory()->create(['status' => TenantStatus::Active->value]);

        $tenantManager = $this->createMock(TenantManager::class);
        $tenantManager->method('getTenant')->willReturn($tenant);

        $middleware = new CheckTenantStatus($tenantManager);

        $request = $this->attachSessionToRequest(Request::create('http://test-tenant.localhost/dashboard'));
        $request->headers->set('host', 'test-tenant.localhost');

        $called = false;
        $response = $middleware->handle($request, function () use (&$called) {
            $called = true;
            return response('OK', 200);
        });

        $this->assertTrue($called, 'Next middleware should have been called for active tenant');
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testRedirectsWhenTenantIsInactive(): void
    {
        $tenant = Tenant::factory()->inactive()->create();

        $tenantManager = $this->createMock(TenantManager::class);
        $tenantManager->method('getTenant')->willReturn($tenant);

        $middleware = new CheckTenantStatus($tenantManager);

        $request = $this->attachSessionToRequest(Request::create('http://test-tenant.localhost/dashboard'));
        $request->headers->set('host', 'test-tenant.localhost');

        $called = false;
        $response = $middleware->handle($request, function () use (&$called) {
            $called = true;
            return response('OK', 200);
        });

        $this->assertFalse($called, 'Next middleware should NOT have been called for inactive tenant');
        $this->assertSame(302, $response->getStatusCode());
    }

    public function testAllowsRequestWhenNoTenantLoaded(): void
    {
        $tenantManager = $this->createMock(TenantManager::class);
        $tenantManager->method('getTenant')->willThrowException(new \Error('Uninitialized property'));

        $middleware = new CheckTenantStatus($tenantManager);

        $request = $this->attachSessionToRequest(Request::create('http://test-tenant.localhost/dashboard'));

        $called = false;
        $response = $middleware->handle($request, function () use (&$called) {
            $called = true;
            return response('OK', 200);
        });

        $this->assertTrue($called);
        $this->assertSame(200, $response->getStatusCode());
    }
}
