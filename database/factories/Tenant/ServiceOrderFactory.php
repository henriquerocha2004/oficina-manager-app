<?php

namespace Database\Factories\Tenant;

use App\Enum\Tenant\ServiceOrder\ServiceOrderStatusEnum;
use App\Models\Tenant\ServiceOrder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ServiceOrderFactory extends Factory
{
    protected $model = ServiceOrder::class;

    public function definition(): array
    {
        $faker = $this->faker;
        $status = $faker->randomElement(ServiceOrderStatusEnum::cases());
        $createdAt = $faker->dateTimeBetween('-30 days', 'now');

        $totalParts = $faker->randomFloat(2, 0, 1500);
        $totalServices = $faker->randomFloat(2, 0, 2000);
        $discount = $faker->randomFloat(2, 0, 200);
        $total = $totalParts + $totalServices - $discount;
        $paidAmount = $status === ServiceOrderStatusEnum::COMPLETED
            ? $total
            : $faker->randomFloat(2, 0, $total);

        $users = \App\Models\Tenant\User::all();
        $technicianId = $users->isNotEmpty() ? $users->random()->id : null;

        $approvedAt = in_array($status, [ServiceOrderStatusEnum::APPROVED, ServiceOrderStatusEnum::IN_PROGRESS, ServiceOrderStatusEnum::WAITING_PAYMENT, ServiceOrderStatusEnum::COMPLETED])
            ? $faker->dateTimeBetween($createdAt, 'now')
            : null;
        $startedAt = in_array($status, [ServiceOrderStatusEnum::IN_PROGRESS, ServiceOrderStatusEnum::WAITING_PAYMENT, ServiceOrderStatusEnum::COMPLETED])
            ? $faker->dateTimeBetween($createdAt, 'now')
            : null;
        $completedAt = $status === ServiceOrderStatusEnum::COMPLETED
            ? $faker->dateTimeBetween($createdAt, 'now')
            : null;

        return [
            'id' => (string) Str::ulid(),
            'order_number' => 'OS-'.str_pad((string) $faker->unique()->numberBetween(1, 99999), 5, '0', STR_PAD_LEFT),
            'vehicle_id' => null,
            'client_id' => null,
            'created_by' => $technicianId ?? 1,
            'technician_id' => $technicianId,
            'status' => $status,
            'diagnosis' => $faker->optional(0.7)->sentence(),
            'observations' => $faker->optional(0.5)->sentence(),
            'total_parts' => $totalParts,
            'total_services' => $totalServices,
            'discount' => $discount,
            'total' => $total,
            'paid_amount' => $paidAmount,
            'outstanding_balance' => $total - $paidAmount,
            'approved_at' => $approvedAt,
            'started_at' => $startedAt,
            'completed_at' => $completedAt,
            'cancelled_at' => $status === ServiceOrderStatusEnum::CANCELLED ? $faker->dateTimeBetween($createdAt, 'now') : null,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (ServiceOrder $serviceOrder) {
            if (is_null($serviceOrder->vehicle_id)) {
                $vehicles = \App\Models\Tenant\Vehicle::all();
                if ($vehicles->isNotEmpty()) {
                    $vehicle = $vehicles->random();
                    $clientId = $vehicle->currentOwner?->client?->id ?? $vehicle->clients()->first()?->id;
                    $serviceOrder->update([
                        'vehicle_id' => $vehicle->id,
                        'client_id' => $clientId,
                    ]);
                }
            }
        });
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ServiceOrderStatusEnum::DRAFT,
            'approved_at' => null,
            'started_at' => null,
            'completed_at' => null,
            'cancelled_at' => null,
        ]);
    }

    public function waitingApproval(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ServiceOrderStatusEnum::WAITING_APPROVAL,
            'approved_at' => null,
            'started_at' => null,
            'completed_at' => null,
            'cancelled_at' => null,
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ServiceOrderStatusEnum::APPROVED,
            'approved_at' => now(),
            'started_at' => null,
            'completed_at' => null,
            'cancelled_at' => null,
        ]);
    }

    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ServiceOrderStatusEnum::IN_PROGRESS,
            'approved_at' => now(),
            'started_at' => now(),
            'completed_at' => null,
            'cancelled_at' => null,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ServiceOrderStatusEnum::COMPLETED,
            'approved_at' => now(),
            'started_at' => now(),
            'completed_at' => now(),
            'cancelled_at' => null,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ServiceOrderStatusEnum::CANCELLED,
            'approved_at' => null,
            'started_at' => null,
            'completed_at' => null,
            'cancelled_at' => now(),
        ]);
    }
}
