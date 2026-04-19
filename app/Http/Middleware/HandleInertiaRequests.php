<?php

namespace App\Http\Middleware;

use App\Support\MediaStorage;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    private function resolveTenantSettings(Request $request): array
    {
        $tenantName = null;
        if (app()->bound('tenant')) {
            $tenantName = app('tenant')?->trade_name;
        }

        $logo = null;
        try {
            $logo = \App\Models\Tenant\TenantSetting::getValue('logo_path');
        } catch (\Throwable $e) {
            // settings table may not exist yet for this tenant
        }

        return [
            'logo_url'    => MediaStorage::url($logo),
            'tenant_name' => $tenantName,
        ];
    }

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $isAdminRequest = $request->is('admin*') || $request->getHost() === config('app.base_domain');

        return [
            ...parent::share($request),
            'auth' => [
                'user' => fn () => $isAdminRequest
                    ? $request->user('admin')
                    : $request->user('tenant'),
                'guard' => fn () => $isAdminRequest ? 'admin' : 'tenant',
            ],
            'flash' => [
                'message' => fn () => $request->session()->get('status'),
                'error' => fn () => $request->session()->get('error'),
                'success' => fn () => $request->session()->get('success'),
            ],
            'tenant_settings' => fn () => $isAdminRequest ? null : $this->resolveTenantSettings($request),
        ];
    }
}
