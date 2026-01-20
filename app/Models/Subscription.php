<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends ManagerBaseModel
{
    protected $connection = 'manager';

    protected $table = 'subscriptions';
    protected $fillable = [
        'slug',
        'label',
        'price',
    ];

    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class, 'subscription_id', 'id');
    }
}
