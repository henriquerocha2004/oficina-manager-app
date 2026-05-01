<?php

namespace App\Models\Tenant;

use App\Enum\Tenant\ServiceOrder\ServiceOrderItemTypeEnum;
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
 * @property ServiceOrderItemTypeEnum $type
 * @property string|null $service_id
 * @property string|null $product_id
 * @property string $description
 * @property int $quantity
 * @property float $unit_price
 * @property float $subtotal
 * @property string|null $notes
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read ServiceOrder $serviceOrder
 * @property-read Service|null $service
 * @property-read Product|null $product
 */
class ServiceOrderItem extends Model
{
    use SoftDeletes;
    use HasFactory;
    use HasUlids;

    protected $table = 'service_order_items';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'service_order_id',
        'type',
        'service_id',
        'product_id',
        'description',
        'quantity',
        'unit_price',
        'subtotal',
        'notes',
    ];

    protected $casts = [
        'type' => ServiceOrderItemTypeEnum::class,
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function serviceOrder(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class, 'service_order_id', 'id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id', 'id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}
