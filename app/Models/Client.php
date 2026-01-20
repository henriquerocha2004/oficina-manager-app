<?php

namespace App\Models;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property string $id
 * @property string $name
 * @property string $email
 * @property string $document_number
 * @property string|null $street
 * @property string|null $city
 * @property string|null $state
 * @property string|null $zip_code
 * @property string|null $phone
 * @property string|null $observations
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder<static>|Client newModelQuery()
 * @method static Builder<static>|Client newQuery()
 * @method static Builder<static>|Client onlyTrashed()
 * @method static Builder<static>|Client query()
 * @method static Builder<static>|Client whereCity($value)
 * @method static Builder<static>|Client whereCreatedAt($value)
 * @method static Builder<static>|Client whereDeletedAt($value)
 * @method static Builder<static>|Client whereDocumentNumber($value)
 * @method static Builder<static>|Client whereEmail($value)
 * @method static Builder<static>|Client whereId($value)
 * @method static Builder<static>|Client whereName($value)
 * @method static Builder<static>|Client whereObservations($value)
 * @method static Builder<static>|Client wherePhone($value)
 * @method static Builder<static>|Client whereState($value)
 * @method static Builder<static>|Client whereStreet($value)
 * @method static Builder<static>|Client whereUpdatedAt($value)
 * @method static Builder<static>|Client whereZipCode($value)
 * @method static Builder<static>|Client withTrashed(bool $withTrashed = true)
 * @method static Builder<static>|Client withoutTrashed()
 * @mixin \Eloquent
 */
class Client extends Model
{
    use SoftDeletes;
    use HasFactory;
    use HasUlids;

    protected $table = 'client';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'id',
        'name',
        'email',
        'document_number',
        'street',
        'city',
        'state',
        'zip_code',
        'phone',
        'observations',
    ];
}
