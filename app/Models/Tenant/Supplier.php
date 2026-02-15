<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $name
 * @property string|null $trade_name
 * @property string $document_number
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $mobile
 * @property string|null $website
 * @property string|null $street
 * @property string|null $number
 * @property string|null $complement
 * @property string|null $neighborhood
 * @property string|null $city
 * @property string|null $state
 * @property string|null $zip_code
 * @property string|null $contact_person
 * @property int|null $payment_term_days
 * @property string|null $notes
 * @property bool $is_active
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static Builder<static>|Supplier newModelQuery()
 * @method static Builder<static>|Supplier newQuery()
 * @method static Builder<static>|Supplier onlyTrashed()
 * @method static Builder<static>|Supplier query()
 * @method static Builder<static>|Supplier whereCity($value)
 * @method static Builder<static>|Supplier whereComplement($value)
 * @method static Builder<static>|Supplier whereContactPerson($value)
 * @method static Builder<static>|Supplier whereCreatedAt($value)
 * @method static Builder<static>|Supplier whereDeletedAt($value)
 * @method static Builder<static>|Supplier whereDocumentNumber($value)
 * @method static Builder<static>|Supplier whereEmail($value)
 * @method static Builder<static>|Supplier whereId($value)
 * @method static Builder<static>|Supplier whereIsActive($value)
 * @method static Builder<static>|Supplier whereMobile($value)
 * @method static Builder<static>|Supplier whereName($value)
 * @method static Builder<static>|Supplier whereNeighborhood($value)
 * @method static Builder<static>|Supplier whereNotes($value)
 * @method static Builder<static>|Supplier whereNumber($value)
 * @method static Builder<static>|Supplier wherePaymentTermDays($value)
 * @method static Builder<static>|Supplier wherePhone($value)
 * @method static Builder<static>|Supplier whereState($value)
 * @method static Builder<static>|Supplier whereStreet($value)
 * @method static Builder<static>|Supplier whereTradeName($value)
 * @method static Builder<static>|Supplier whereUpdatedAt($value)
 * @method static Builder<static>|Supplier whereWebsite($value)
 * @method static Builder<static>|Supplier whereZipCode($value)
 * @method static Builder<static>|Supplier withTrashed(bool $withTrashed = true)
 * @method static Builder<static>|Supplier withoutTrashed()
 *
 * @mixin \Eloquent
 */
class Supplier extends Model
{
    use HasFactory;
    use HasUlids;
    use SoftDeletes;

    protected $table = 'supplier';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'trade_name',
        'document_number',
        'email',
        'phone',
        'mobile',
        'website',
        'street',
        'number',
        'complement',
        'neighborhood',
        'city',
        'state',
        'zip_code',
        'contact_person',
        'payment_term_days',
        'notes',
        'is_active',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_supplier')
            ->withPivot([
                'supplier_sku',
                'cost_price',
                'lead_time_days',
                'min_order_quantity',
                'is_preferred',
                'notes',
            ])
            ->withTimestamps();
    }
}
