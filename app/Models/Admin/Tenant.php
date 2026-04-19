<?php

namespace App\Models\Admin;

use App\Enum\Admin\TenantStatus;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $subscription_id
 * @property string $name
 * @property string $document
 * @property string|null $street
 * @property string|null $city
 * @property string|null $state
 * @property string|null $zip_code
 * @property string|null $phone
 * @property string $domain
 * @property string $database_name
 * @property string $email
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read Subscription|null $subscription
 * @method static Builder<static>|Tenant newModelQuery()
 * @method static Builder<static>|Tenant newQuery()
 * @method static Builder<static>|Tenant query()
 * @method static Builder<static>|Tenant whereCity($value)
 * @method static Builder<static>|Tenant whereCreatedAt($value)
 * @method static Builder<static>|Tenant whereDatabaseName($value)
 * @method static Builder<static>|Tenant whereDeletedAt($value)
 * @method static Builder<static>|Tenant whereDocument($value)
 * @method static Builder<static>|Tenant whereDomain($value)
 * @method static Builder<static>|Tenant whereEmail($value)
 * @method static Builder<static>|Tenant whereId($value)
 * @method static Builder<static>|Tenant whereIsActive($value)
 * @method static Builder<static>|Tenant whereName($value)
 * @method static Builder<static>|Tenant wherePhone($value)
 * @method static Builder<static>|Tenant whereState($value)
 * @method static Builder<static>|Tenant whereStreet($value)
 * @method static Builder<static>|Tenant whereSubscriptionId($value)
 * @method static Builder<static>|Tenant whereUpdatedAt($value)
 * @method static Builder<static>|Tenant whereZipCode($value)
 * @mixin Eloquent
 */
class Tenant extends ManagerBaseModel
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'tenant';
    protected $guarded = [];

    protected $fillable = [
        'subscription_id',
        'domain',
        'email',
        'is_active',
        'name',
        'trade_name',
        'document',
        'database_name',
        'status',
        'trial_until',
        'client_id',
    ];

    protected $casts = [
        'trial_until' => 'datetime',
    ];

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class, 'subscription_id', 'id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }
}
