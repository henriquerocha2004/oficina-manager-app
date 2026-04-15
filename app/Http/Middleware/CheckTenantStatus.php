<?php

namespace App\Http\Middleware;

use App\Constants\Messages;
use App\Enum\Admin\TenantStatus;
use App\Services\admin\TenantManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTenantStatus
{
    public function __construct(
        protected TenantManager $tenantManager
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        try {
            $tenant = $this->tenantManager->getTenant();
        } catch (\Error) {
            return $next($request);
        }

        if ($tenant->status === TenantStatus::Inactive->value) {
            $subdomain = explode('.', $request->getHost())[0];

            return redirect()
                ->route('tenant.login', ['subdomain' => $subdomain])
                ->with('error', Messages::TENANT_ACCOUNT_DISABLED);
        }

        return $next($request);
    }
}
