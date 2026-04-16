<?php

namespace App\Http\Middleware;

use App\Constants\Messages;
use App\Enum\Admin\TenantStatus;
use App\Services\admin\TenantManager;
use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class CheckTenantStatus
{
    public function __construct(
        protected TenantManager $tenantManager
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $this->resolveTenant($request);

        if (is_null($tenant)) {
            return $next($request);
        }

        $status = $tenant->status instanceof TenantStatus
            ? $tenant->status->value
            : (string) $tenant->status;

        if ($status === TenantStatus::Inactive->value) {
            $request->session()->flash('error', Messages::TENANT_ACCOUNT_DISABLED);

            if ($request->route()?->named('tenant.login')) {
                return Inertia::render('Tenant/Auth/Login')
                    ->toResponse($request)
                    ->setStatusCode(Response::HTTP_FORBIDDEN);
            }

            $subdomain = explode('.', $request->getHost())[0];

            return redirect()
                ->route('tenant.login', ['subdomain' => $subdomain]);
        }

        return $next($request);
    }

    private function resolveTenant(Request $request): mixed
    {
        try {
            return $this->tenantManager->getTenant();
        } catch (\Error) {
            $tenantManager = $this->tenantManager->loadTenant($request->getHost());

            try {
                return $tenantManager->getTenant();
            } catch (\Error) {
                return null;
            }
        }
    }
}
