<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManagerBaseModel extends Model
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        if (app()->environment('testing')) {
            $this->connection = config('database.default');
        }
    }
}
