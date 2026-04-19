<?php

namespace Tests\Feature\Api;

use App\Models\Admin\Client;
use App\Models\Admin\Tenant;
use App\Services\Admin\ClientTenantService;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class TrialControllerTest extends TestCase
{
    public function testTrialEndpointDefaultsTo15Days(): void
    {
        Carbon::setTestNow('2026-04-16 10:00:00');

        $data = [
            'name' => 'Cliente Trial',
            'email' => 'trial@example.com',
            'document' => '12345678901234',
            'phone' => '11999990000',
            'domain' => 'cliente-trial',
        ];

        $client = Client::factory()->make([
            'name' => $data['name'],
            'email' => $data['email'],
            'document' => $data['document'],
        ]);

        $tenant = Tenant::factory()->make([
            'domain' => $data['domain'],
            'status' => 'trial',
            'trial_until' => Carbon::now()->addDays(15),
        ]);

        $this->mock(ClientTenantService::class)
            ->shouldReceive('create')
            ->once()
            ->withArgs(function ($clientDto, $domain, $status, $trialUntil) {
                return $domain === 'cliente-trial'
                    && $status === 'trial'
                    && $trialUntil === Carbon::now()->addDays(15)->toDateString();
            })
            ->andReturn(['client' => $client, 'tenant' => $tenant]);

        $response = $this->postJson('/api/trial', $data);

        $response->assertStatus(201);
        $response->assertJsonPath('data.tenant.status', 'trial');

        Carbon::setTestNow();
    }
}
