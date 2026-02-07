<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $license_plate
 * @property string $brand
 * @property string $model
 * @property int $year
 * @property string|null $color
 * @property string|null $vin
 * @property string|null $fuel
 * @property string|null $transmission
 * @property int|null $mileage
 * @property string|null $cilinder_capacity
 * @property string $client_id
 * @property string $vehicle_type
 * @property string|null $observations
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Client $client
 * @method static Builder<static>|Vehicle newModelQuery()
 * @method static Builder<static>|Vehicle newQuery()
 * @method static Builder<static>|Vehicle onlyTrashed()
 * @method static Builder<static>|Vehicle query()
 * @method static Builder<static>|Vehicle whereBrand($value)
 * @method static Builder<static>|Vehicle whereCilinderCapacity($value)
 * @method static Builder<static>|Vehicle whereClientId($value)
 * @method static Builder<static>|Vehicle whereColor($value)
 * @method static Builder<static>|Vehicle whereCreatedAt($value)
 * @method static Builder<static>|Vehicle whereDeletedAt($value)
 * @method static Builder<static>|Vehicle whereFuel($value)
 * @method static Builder<static>|Vehicle whereId($value)
 * @method static Builder<static>|Vehicle whereLicensePlate($value)
 * @method static Builder<static>|Vehicle whereMileage($value)
 * @method static Builder<static>|Vehicle whereModel($value)
 * @method static Builder<static>|Vehicle whereObservations($value)
 * @method static Builder<static>|Vehicle whereTransmission($value)
 * @method static Builder<static>|Vehicle whereUpdatedAt($value)
 * @method static Builder<static>|Vehicle whereVehicleType($value)
 * @method static Builder<static>|Vehicle whereVin($value)
 * @method static Builder<static>|Vehicle whereYear($value)
 * @method static Builder<static>|Vehicle withTrashed(bool $withTrashed = true)
 * @method static Builder<static>|Vehicle withoutTrashed()
 * @mixin \Eloquent
 */
class Vehicle extends Model
{
    use SoftDeletes;
    use HasFactory;
    use HasUlids;

    protected $table = 'vehicle';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'id',
        'license_plate',
        'brand',
        'model',
        'year',
        'color',
        'vin',
        'fuel',
        'transmission',
        'mileage',
        'cilinder_capacity',
        'client_id',
        'vehicle_type',
        'observations',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id', 'id');
    }
}
