<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    public const ROLE_USER  = 'user';
    public const ROLE_ADMIN = 'admin';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'avatar_path',
        'is_admin',
        'extension_api_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'extension_api_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'is_admin'          => 'boolean',
    ];

    public function isAdmin(): bool
    {
        return $this->is_admin === true || $this->role === self::ROLE_ADMIN;
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function linkedinProfiles()
    {
        return $this->hasMany(LinkedinProfile::class);
    }

    public function linkedinPosts()
    {
        return $this->hasManyThrough(
            LinkedinPost::class,
            LinkedinProfile::class,
            'user_id',
            'linkedin_profile_id',
            'id',
            'id'
        );
    }

    public function issueExtensionToken(): string
    {
        if ($this->extension_api_token) {
            return $this->extension_api_token;
        }

        $token = Str::random(60);

        $this->forceFill([
            'extension_api_token' => $token,
        ])->save();

        return $token;
    }
}
