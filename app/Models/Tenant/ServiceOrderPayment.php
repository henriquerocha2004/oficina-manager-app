<?php

namespace App\Models\Tenant;

use App\Enum\Tenant\ServiceOrder\PaymentMethodEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $service_order_id
 * @property int $received_by
 * @property PaymentMethodEnum $payment_method
 * @property float $amount
 * @property Carbon $paid_at
 * @property string|null $notes
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read ServiceOrder $serviceOrder
 * @property-read User $receiver
 */
class ServiceOrderPayment extends Model
{
    use SoftDeletes;
    use HasFactory;
    use HasUlids;

    protected $table = 'service_order_payments';
    public $incrementing = false;
    protected $keyType = 'string';
    
    protected $fillable = [
        'id',
        'service_order_id',
        'received_by',
        'payment_method',
        'amount',
        'paid_at',
        'notes',
    ];

    protected $casts = [
        'payment_method' => PaymentMethodEnum::class,
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function serviceOrder(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class, 'service_order_id', 'id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by', 'id');
    }
}
