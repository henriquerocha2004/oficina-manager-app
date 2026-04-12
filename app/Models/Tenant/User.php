<?php

namespace App\Models\Tenant;

use App\Enum\Tenant\User\UserRoleEnum;
use App\Notifications\Tenant\ResetPasswordNotification;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'avatar_path',
    ];

    protected $appends = [
        'avatar',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRoleEnum::class,
            'is_active' => 'boolean',
        ];
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function getAvatarAttribute(): ?string
    {
        if (is_null($this->avatar_path) || $this->avatar_path === '') {
            return null;
        }

        return '/storage/' . $this->avatar_path;
    }

    public function getConnectionName(): ?string
    {
        return config('database.connections_names.tenant');
    }
}
