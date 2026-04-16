<?php

namespace App\Services\Tenant\Auth;

use Illuminate\Contracts\Cookie\QueueingFactory;
use Illuminate\Http\Request;

class TenantSessionBinding
{
    public const string SESSION_KEY = 'tenant_auth.host';

    public const string COOKIE_NAME = 'tenant_auth_host';

    private const int REMEMBER_DURATION_MINUTES = 576000;

    public function __construct(
        private QueueingFactory $cookies
    ) {
    }

    public function bindToCurrentTenant(Request $request, bool $remember = false): void
    {
        $host = $this->currentTenantHost($request);

        $request->session()->put(self::SESSION_KEY, $host);

        if (!$remember) {
            $this->forgetCookie();

            return;
        }

        $this->cookies->queue($this->cookies->make(
            name: self::COOKIE_NAME,
            value: $host,
            minutes: self::REMEMBER_DURATION_MINUTES,
            path: config('session.path', '/'),
            domain: null,
            secure: config('session.secure'),
            httpOnly: config('session.http_only', true),
            raw: false,
            sameSite: config('session.same_site', 'lax'),
        ));
    }

    public function clear(Request $request): void
    {
        $request->session()->remove(self::SESSION_KEY);
        $this->forgetCookie();
    }

    public function hasBinding(Request $request): bool
    {
        return !is_null($this->boundHost($request));
    }

    public function matchesCurrentTenant(Request $request): bool
    {
        $boundHost = $this->boundHost($request);

        if (is_null($boundHost)) {
            return true;
        }

        return $boundHost === $this->currentTenantHost($request);
    }

    public function syncSession(Request $request): void
    {
        $boundHost = $this->boundHost($request);

        if (is_null($boundHost)) {
            return;
        }

        $request->session()->put(self::SESSION_KEY, $boundHost);
    }

    public function loginRouteParameters(Request $request): array
    {
        return [
            'subdomain' => explode('.', $this->currentTenantHost($request))[0],
        ];
    }

    private function boundHost(Request $request): ?string
    {
        $sessionHost = $request->session()->get(self::SESSION_KEY);

        if (is_string($sessionHost) && $sessionHost !== '') {
            return strtolower($sessionHost);
        }

        $cookieHost = $request->cookie(self::COOKIE_NAME);

        if (!is_string($cookieHost) || $cookieHost === '') {
            return null;
        }

        return strtolower($cookieHost);
    }

    private function currentTenantHost(Request $request): string
    {
        return strtolower($request->getHost());
    }

    private function forgetCookie(): void
    {
        $this->cookies->unqueue(self::COOKIE_NAME);
        $this->cookies->queue($this->cookies->forget(
            name: self::COOKIE_NAME,
            path: config('session.path', '/'),
            domain: null,
        ));
    }
}
