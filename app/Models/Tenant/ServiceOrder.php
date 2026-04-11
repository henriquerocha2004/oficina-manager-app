<?php

namespace App\Models\Tenant;

use App\Enum\Tenant\ServiceOrder\ServiceOrderStatusEnum;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $order_number
 * @property string $vehicle_id
 * @property string $client_id
 * @property int $created_by
 * @property int|null $technician_id
 * @property ServiceOrderStatusEnum $status
 * @property string|null $reported_problem
 * @property string|null $technical_diagnosis
 * @property string|null $observations
 * @property string|null $customer_complaint
 * @property float $total_parts
 * @property float $total_services
 * @property float $discount
 * @property float $total
 * @property float $paid_amount
 * @property float $outstanding_balance
 * @property Carbon|null $approved_at
 * @property Carbon|null $started_at
 * @property Carbon|null $completed_at
 * @property Carbon|null $cancelled_at
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Vehicle $vehicle
 * @property-read Client $client
 * @property-read User $creator
 * @property-read User|null $technician
 * @property-read ServiceOrderItem[] $items
 * @property-read ServiceOrderEvent[] $events
 * @property-read ServiceOrderPayment[] $payments
 * @property-read ServiceOrderPhoto[] $photos
 */
class ServiceOrder extends Model
{
    use SoftDeletes;
    use HasFactory;
    use HasUlids;

    protected $table = 'service_orders';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'order_number',
        'vehicle_id',
        'client_id',
        'created_by',
        'technician_id',
        'status',
        'reported_problem',
        'technical_diagnosis',
        'observations',
        'customer_complaint',
        'total_parts',
        'total_services',
        'discount',
        'total',
        'paid_amount',
        'outstanding_balance',
        'approved_at',
        'started_at',
        'completed_at',
        'cancelled_at',
    ];

    protected $casts = [
        'status' => ServiceOrderStatusEnum::class,
        'total_parts' => 'decimal:2',
        'total_services' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'outstanding_balance' => 'decimal:2',
        'approved_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id', 'id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id', 'id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id', 'id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ServiceOrderItem::class, 'service_order_id', 'id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(ServiceOrderEvent::class, 'service_order_id', 'id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(ServiceOrderPayment::class, 'service_order_id', 'id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(ServiceOrderPhoto::class, 'service_order_id', 'id');
    }
}
