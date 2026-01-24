<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AdminUsers extends Model
{
    use HasFactory;

    protected $table = 'users';
    protected $guarded = [
        'password'
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];
}
