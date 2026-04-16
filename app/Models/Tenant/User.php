<?php

namespace App\Models\Tenant;

use App\Enum\Tenant\User\UserRoleEnum;
use App\Notifications\Tenant\ResetPasswordNotification;
use Illuminate\Support\Str;
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
        'ulid',
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'avatar_path',
        'preferences',
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
            'ulid' => 'string',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRoleEnum::class,
            'is_active' => 'boolean',
            'preferences' => 'array',
        ];
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    protected static function booted(): void
    {
        static::creating(function (self $user): void {
            if (!is_null($user->ulid) && $user->ulid !== '') {
                return;
            }

            $user->ulid = (string) Str::ulid();
        });
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

    public function getAuthIdentifierName(): string
    {
        return 'ulid';
    }

    public function legacyId(): int
    {
        return $this->id;
    }
}
