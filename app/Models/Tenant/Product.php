<?php

namespace App\Models\Tenant;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $name
 * @property string|null $description
 * @property string|null $sku
 * @property string|null $barcode
 * @property string|null $manufacturer
 * @property string $category
 * @property int|null $min_stock_level
 * @property string $unit
 * @property float $unit_price
 * @property float|null $suggested_price
 * @property bool $is_active
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static Builder<static>|Product newModelQuery()
 * @method static Builder<static>|Product newQuery()
 * @method static Builder<static>|Product onlyTrashed()
 * @method static Builder<static>|Product query()
 * @method static Builder<static>|Product whereId($value)
 * @method static Builder<static>|Product whereName($value)
 * @method static Builder<static>|Product whereDescription($value)
 * @method static Builder<static>|Product whereSku($value)
 * @method static Builder<static>|Product whereBarcode($value)
 * @method static Builder<static>|Product whereManufacturer($value)
 * @method static Builder<static>|Product whereCategory($value)
 * @method static Builder<static>|Product whereMinStockLevel($value)
 * @method static Builder<static>|Product whereUnit($value)
 * @method static Builder<static>|Product whereUnitPrice($value)
 * @method static Builder<static>|Product whereSuggestedPrice($value)
 * @method static Builder<static>|Product whereIsActive($value)
 * @method static Builder<static>|Product whereCreatedAt($value)
 * @method static Builder<static>|Product whereUpdatedAt($value)
 * @method static Builder<static>|Product whereDeletedAt($value)
 * @method static Builder<static>|Product withTrashed(bool $withTrashed = true)
 * @method static Builder<static>|Product withoutTrashed()
 *
 * @mixin Eloquent
 */
class Product extends Model
{
    use HasFactory;
    use HasUlids;
    use SoftDeletes;

    public const string CATEGORY_ENGINE = 'engine';

    public const string CATEGORY_SUSPENSION = 'suspension';

    public const string CATEGORY_BRAKES = 'brakes';

    public const string CATEGORY_ELECTRICAL = 'electrical';

    public const string CATEGORY_FILTERS = 'filters';

    public const string CATEGORY_FLUIDS = 'fluids';

    public const string CATEGORY_TIRES = 'tires';

    public const string CATEGORY_BODY_PARTS = 'body_parts';

    public const string CATEGORY_INTERIOR = 'interior';

    public const string CATEGORY_TOOLS = 'tools';

    public const string CATEGORY_OTHER = 'other';

    public const string UNIT_UNIT = 'unit';

    public const string UNIT_LITER = 'liter';

    public const string UNIT_KG = 'kg';

    public const string UNIT_METER = 'meter';

    public const string UNIT_BOX = 'box';

    protected $table = 'products';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'description',
        'sku',
        'barcode',
        'manufacturer',
        'category',
        'min_stock_level',
        'unit',
        'unit_price',
        'suggested_price',
        'is_active',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'suggested_price' => 'decimal:2',
        'is_active' => 'boolean',
        'min_stock_level' => 'integer',
    ];

    public static function getCategories(): array
    {
        return [
            self::CATEGORY_ENGINE,
            self::CATEGORY_SUSPENSION,
            self::CATEGORY_BRAKES,
            self::CATEGORY_ELECTRICAL,
            self::CATEGORY_FILTERS,
            self::CATEGORY_FLUIDS,
            self::CATEGORY_TIRES,
            self::CATEGORY_BODY_PARTS,
            self::CATEGORY_INTERIOR,
            self::CATEGORY_TOOLS,
            self::CATEGORY_OTHER,
        ];
    }

    public static function getUnits(): array
    {
        return [
            self::UNIT_UNIT,
            self::UNIT_LITER,
            self::UNIT_KG,
            self::UNIT_METER,
            self::UNIT_BOX,
        ];
    }
}
