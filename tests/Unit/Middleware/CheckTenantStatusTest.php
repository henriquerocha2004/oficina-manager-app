<?php

namespace Tests\Unit\Middleware;

use App\Constants\Messages;
use App\Enum\Admin\TenantStatus;
use App\Http\Middleware\CheckTenantStatus;
use App\Models\Admin\Tenant;
use App\Services\admin\TenantManager;
use Illuminate\Http\Request;
use Tests\AdminTestCase;

class CheckTenantStatusTest extends AdminTestCase
{
    private function makeTenant(string $status): Tenant
    {
        $tenant = new Tenant();
        $tenant->status = $status;

        return $tenant;
    }

    private function makeMiddleware(Tenant|\Throwable $tenantOrException): CheckTenantStatus
    {
        $tenantManager = $this->createMock(TenantManager::class);

        if ($tenantOrException instanceof \Throwable) {
            $tenantManager->method('getTenant')->willThrowException($tenantOrException);
        } else {
            $tenantManager->method('getTenant')->willReturn($tenantOrException);
        }

        return new CheckTenantStatus($tenantManager);
    }

    public function testAllowsRequestWhenTenantIsActive(): void
    {
        $middleware = $this->makeMiddleware($this->makeTenant(TenantStatus::Active->value));

        $request = Request::create('http://minha-oficina.localhost/dashboard');
        $request->headers->set('host', 'minha-oficina.localhost');

        $called = false;
        $response = $middleware->handle($request, function () use (&$called) {
            $called = true;

            return response('OK', 200);
        });

        $this->assertTrue($called, 'Next middleware should be called for active tenant');
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testRedirectsWhenTenantIsInactive(): void
    {
        $middleware = $this->makeMiddleware($this->makeTenant(TenantStatus::Inactive->value));

        $request = Request::create('http://minha-oficina.localhost/dashboard');
        $request->headers->set('host', 'minha-oficina.localhost');

        $called = false;
        $response = $middleware->handle($request, function () use (&$called) {
            $called = true;

            return response('OK', 200);
        });

        $this->assertFalse($called, 'Next middleware should NOT be called for inactive tenant');
        $this->assertSame(302, $response->getStatusCode());
    }

    public function testRedirectsWhenTrialExpired(): void
    {
        $tenant = $this->makeTenant(TenantStatus::Inactive->value);

        $middleware = $this->makeMiddleware($tenant);

        $request = Request::create('http://minha-oficina.localhost/dashboard');
        $request->headers->set('host', 'minha-oficina.localhost');

        $called = false;
        $response = $middleware->handle($request, function () use (&$called) {
            $called = true;

            return response('OK', 200);
        });

        $this->assertFalse($called, 'Next middleware should NOT be called for expired trial');
        $this->assertSame(302, $response->getStatusCode());
    }

    public function testAllowsRequestWhenNoTenantLoaded(): void
    {
        $middleware = $this->makeMiddleware(new \Error('Uninitialized property'));

        $request = Request::create('http://minha-oficina.localhost/dashboard');
        $request->headers->set('host', 'minha-oficina.localhost');

        $called = false;
        $response = $middleware->handle($request, function () use (&$called) {
            $called = true;

            return response('OK', 200);
        });

        $this->assertTrue($called, 'Request should pass when no tenant is loaded');
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testAllowsRequestWhenTenantIsOnActiveTrial(): void
    {
        $middleware = $this->makeMiddleware($this->makeTenant(TenantStatus::Trial->value));

        $request = Request::create('http://minha-oficina.localhost/dashboard');
        $request->headers->set('host', 'minha-oficina.localhost');

        $called = false;
        $response = $middleware->handle($request, function () use (&$called) {
            $called = true;

            return response('OK', 200);
        });

        $this->assertTrue($called, 'Next middleware should be called for trial tenant');
        $this->assertSame(200, $response->getStatusCode());
    }
}
