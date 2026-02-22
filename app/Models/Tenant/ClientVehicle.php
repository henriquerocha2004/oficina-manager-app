<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $client_id
 * @property string $vehicle_id
 * @property bool $current_owner
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Client $client
 * @property-read Vehicle $vehicle
 */
class ClientVehicle extends Pivot
{
    use HasUlids;
    use SoftDeletes;

    protected $table = 'client_vehicle';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->connection = config('database.connections_names.tenant');
    }

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'client_id',
        'vehicle_id',
        'current_owner',
    ];

    protected $casts = [
        'current_owner' => 'boolean',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }
}
