<?php

namespace App\Notifications\Admin;

use App\Enum\Admin\TenantStatus;
use App\Models\Admin\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

class TenantAccessCreatedNotification extends Notification
{
    use Queueable;

    private const string BRAND_NAME = 'Easy Oficina';

    public function __construct(
        private readonly Tenant $tenant,
        private readonly string $password,
    ) {
    }

    public function via(): array
    {
        return ['mail'];
    }

    public function toMail(): MailMessage
    {
        $loginUrl = route('tenant.login', ['subdomain' => $this->tenant->domain]);
        $trialUntil = null;

        if ($this->tenant->status === TenantStatus::Trial->value && $this->tenant->trial_until) {
            $trialUntil = Carbon::parse($this->tenant->trial_until)->format('d/m/Y');
        }

        return (new MailMessage())
            ->subject('Acesso ao sistema criado')
            ->markdown('emails.admin.tenant-access-created', [
                'loginUrl' => $loginUrl,
                'tenantName' => $this->tenant->name,
                'tenantEmail' => $this->tenant->email,
                'tenantDomain' => $this->tenant->domain,
                'password' => $this->password,
                'trialUntil' => $trialUntil,
                'isTrial' => $this->tenant->status === TenantStatus::Trial->value,
                'appName' => self::BRAND_NAME,
            ]);
    }
}
