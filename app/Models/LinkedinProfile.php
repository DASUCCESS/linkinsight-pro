<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LinkedinProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'linkedin_id',
        'public_url',
        'name',
        'headline',
        'profile_image_url',
        'location',
        'industry',
        'connections_count',
        'followers_count',
        'profile_type',
        'is_primary',
        'last_synced_at',
        'sync_status',
        'sync_error',
    ];

    protected $casts = [
        'is_primary'     => 'boolean',
        'last_synced_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function metrics()
    {
        return $this->hasMany(LinkedinProfileMetric::class);
    }

    public function posts()
    {
        return $this->hasMany(LinkedinPost::class);
    }

    public function audienceInsights()
    {
        return $this->hasMany(LinkedinAudienceInsight::class);
    }

    public function scopeOwned($query)
    {
        return $query->where('profile_type', 'own');
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}
