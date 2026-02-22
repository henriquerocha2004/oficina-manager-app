<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
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
 *
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
 *
 * @mixin \Eloquent
 */
class Vehicle extends Model
{
    use HasFactory;
    use HasUlids;
    use SoftDeletes;

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
        'vehicle_type',
        'observations',
    ];

    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(
            related: Client::class,
            table: 'client_vehicle',
            foreignPivotKey: 'vehicle_id',
            relatedPivotKey: 'client_id'
        )
            ->using(ClientVehicle::class)
            ->withPivot(['current_owner', 'created_at', 'updated_at'])
            ->withTimestamps();
    }

    public function currentOwner(): HasOne
    {
        return $this->hasOne(ClientVehicle::class, 'vehicle_id')
            ->where('current_owner', true)
            ->with('client');
    }
}
