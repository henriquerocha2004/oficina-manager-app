<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $id
 * @property string $name
 * @property string|null $description
 * @property float $base_price
 * @property string $category
 * @property int|null $estimated_time
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class Service extends Model
{
    use SoftDeletes;
    use HasFactory;
    use HasUlids;

    public const CATEGORY_MAINTENANCE = 'maintenance';
    public const CATEGORY_REPAIR = 'repair';
    public const CATEGORY_DIAGNOSTIC = 'diagnostic';
    public const CATEGORY_PAINTING = 'painting';
    public const CATEGORY_ALIGNMENT = 'alignment';
    public const CATEGORY_OTHER = 'other';

    protected $table = 'services';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'description',
        'base_price',
        'category',
        'estimated_time',
        'is_active',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'estimated_time' => 'integer',
        'is_active' => 'boolean',
    ];

    public static function getCategories(): array
    {
        return [
            self::CATEGORY_MAINTENANCE,
            self::CATEGORY_REPAIR,
            self::CATEGORY_DIAGNOSTIC,
            self::CATEGORY_PAINTING,
            self::CATEGORY_ALIGNMENT,
            self::CATEGORY_OTHER,
        ];
    }
}
