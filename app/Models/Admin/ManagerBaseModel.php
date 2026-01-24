<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class ManagerBaseModel extends Model
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->connection = config('database.connections_names.admin');
    }
}
