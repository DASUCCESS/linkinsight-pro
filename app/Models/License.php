<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class License extends Model
{
    protected $fillable = [
        'purchase_code',
        'buyer',
        'email',
        'domain',
        'item_id',
        'license_token',
        'status',
        'last_checked_at',
        'support_ends_at',
        'is_owner_license',
    ];

    protected $casts = [
        'last_checked_at' => 'datetime',
        'support_ends_at' => 'datetime',
        'is_owner_license' => 'boolean',
    ];

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isSupportActive(): bool
    {
        return $this->support_ends_at && $this->support_ends_at->isFuture();
    }
}
