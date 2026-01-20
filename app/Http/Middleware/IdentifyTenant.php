<?php

namespace App\Http\Middleware;

use App\Constants\Messages;
use App\Services\admin\TenantManager;
use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenant
{
    public function __construct(
       protected TenantManager $tenantManager
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        View::share('subdomain', Route::current()->parameter('subdomain'));
        $request->route()->forgetParameter('subdomain');

        if (app()->environment() === 'testing') return $next($request);
        $tenantManager = $this->tenantManager->loadTenant($request->getHost());

        if (!$tenantManager->getTenant()) {
            Log::error(Messages::TENANT_NOT_FOUND, [
                'host' => $request->getHost(),
            ]);

            abort(Response::HTTP_NOT_FOUND, Messages::WE_NOT_RECOGNIZE_TENANT);
        }

        try {
            $tenantManager->switchTenant($tenantManager->getTenant());
        } catch (Exception $exception) {
            Log::error(Messages::UNABLE_TO_SWITCH_TENANT, [
                'host' => $request->getHost(),
                'message' => $exception->getMessage(),
            ]);

            abort(Response::HTTP_INTERNAL_SERVER_ERROR, Messages::UNABLE_TO_SWITCH_TENANT);
        }


        return $next($request);
    }
}
