<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends ManagerBaseModel
{
    use HasFactory;
    use HasUlids;
    use SoftDeletes;

    protected $table = 'clients';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'email',
        'document',
        'phone',
        'street',
        'city',
        'state',
        'zip_code',
    ];

    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class, 'client_id');
    }
}
