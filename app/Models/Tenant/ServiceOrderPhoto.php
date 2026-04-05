<?php

namespace App\Models\Tenant;

use App\Models\Tenant\User;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

/**
 * @property string $id
 * @property string $service_order_id
 * @property string $file_path
 * @property string $original_filename
 * @property int $file_size
 * @property string $mime_type
 * @property int|null $width
 * @property int|null $height
 * @property int $display_order
 * @property int $uploaded_by
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read ServiceOrder $serviceOrder
 * @property-read User $uploader
 * @property-read string $url
 */
class ServiceOrderPhoto extends Model
{
    use HasUlids;
    use SoftDeletes;

    protected $connection = 'tenant';
    protected $table = 'service_order_photos';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'service_order_id',
        'file_path',
        'original_filename',
        'file_size',
        'mime_type',
        'width',
        'height',
        'display_order',
        'uploaded_by',
    ];

    protected $appends = ['url'];

    protected $casts = [
        'file_size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'display_order' => 'integer',
        'uploaded_by' => 'integer',
    ];

    public function serviceOrder(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class, 'service_order_id', 'id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by', 'id');
    }

    public function getUrlAttribute(): string
    {
        return '/storage/' . $this->file_path;
    }

    protected static function boot()
    {
        parent::boot();

        // Delete physical file when model is deleted
        static::deleting(function (ServiceOrderPhoto $photo) {
            if ($photo->file_path && Storage::disk('public')->exists($photo->file_path)) {
                Storage::disk('public')->delete($photo->file_path);
            }
        });
    }
}
