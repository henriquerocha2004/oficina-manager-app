<?php

namespace App\Http\Requests;

use App\Enum\Admin\TenantStatus;
use Illuminate\Support\Carbon;

class TrialRequest extends ClientTenantRequest
{
    protected function prepareForValidation(): void
    {
        $trialUntil = $this->input('trial_until');

        if (is_null($trialUntil) || $trialUntil === '') {
            $trialUntil = Carbon::now()->addDays(15)->toDateString();
        }

        $this->merge([
            'status' => TenantStatus::Trial->value,
            'trial_until' => $trialUntil,
        ]);
    }
}
