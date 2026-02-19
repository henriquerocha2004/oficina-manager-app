<?php

namespace App\Models\Tenant;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $product_id
 * @property string $movement_type
 * @property int $quantity
 * @property int|null $balance_after
 * @property string|null $reference_type
 * @property string|null $reference_id
 * @property string $reason
 * @property string|null $notes
 * @property int|null $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Product $product
 * @property-read User|null $user
 *
 * @method static Builder<static>|StockMovement newModelQuery()
 * @method static Builder<static>|StockMovement newQuery()
 * @method static Builder<static>|StockMovement query()
 * @method static Builder<static>|StockMovement whereId($value)
 * @method static Builder<static>|StockMovement whereProductId($value)
 * @method static Builder<static>|StockMovement whereMovementType($value)
 * @method static Builder<static>|StockMovement whereQuantity($value)
 * @method static Builder<static>|StockMovement whereBalanceAfter($value)
 * @method static Builder<static>|StockMovement whereReferenceType($value)
 * @method static Builder<static>|StockMovement whereReferenceId($value)
 * @method static Builder<static>|StockMovement whereReason($value)
 * @method static Builder<static>|StockMovement whereNotes($value)
 * @method static Builder<static>|StockMovement whereUserId($value)
 * @method static Builder<static>|StockMovement whereCreatedAt($value)
 * @method static Builder<static>|StockMovement whereUpdatedAt($value)
 *
 * @mixin Eloquent
 */
class StockMovement extends Model
{
    use HasFactory;
    use HasUlids;

    protected $table = 'stock_movements';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'product_id',
        'movement_type',
        'quantity',
        'balance_after',
        'reference_type',
        'reference_id',
        'reason',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'balance_after' => 'integer',
        'user_id' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
