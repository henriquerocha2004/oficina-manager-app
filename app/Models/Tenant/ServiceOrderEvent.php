<?php

namespace App\Models\Tenant;

use App\Enum\Tenant\ServiceOrder\ServiceOrderEventTypeEnum;
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
 * @property int $user_id
 * @property ServiceOrderEventTypeEnum $event_type
 * @property string $description
 * @property array|null $metadata
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read ServiceOrder $serviceOrder
 * @property-read User $user
 */
class ServiceOrderEvent extends Model
{
    use SoftDeletes;
    use HasFactory;
    use HasUlids;

    protected $table = 'service_order_events';
    public $incrementing = false;
    protected $keyType = 'string';
    
    protected $fillable = [
        'id',
        'service_order_id',
        'user_id',
        'event_type',
        'description',
        'metadata',
    ];

    protected $casts = [
        'event_type' => ServiceOrderEventTypeEnum::class,
        'metadata' => 'array',
    ];

    public function serviceOrder(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class, 'service_order_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
