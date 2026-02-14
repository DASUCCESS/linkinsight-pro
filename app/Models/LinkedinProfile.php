<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

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

    public function audienceDemographics()
    {
        return $this->hasMany(LinkedinAudienceDemographic::class, 'linkedin_profile_id');
    }

    public function creatorAudienceMetrics()
    {
        return $this->hasMany(LinkedinCreatorAudienceMetric::class, 'linkedin_profile_id');
    }

    public function connections()
    {
        return $this->hasMany(LinkedinConnection::class, 'linkedin_profile_id');
    }

    public function scopeOwned($query)
    {
        return $query->where('profile_type', 'own');
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Centralised upsert for profiles.
     * One profile per LinkedIn account per user:
     * - Match by linkedin_id when provided.
     * - Otherwise match by normalized public_url.
     */
    public static function upsertFromPayload(User $user, array $data): self
    {
        $linkedinId = $data['linkedin_id'] ?? null;
        $publicUrl  = $data['public_url'] ?? null;

        $query = static::query()->forUser($user->id);

        if ($linkedinId) {
            $query->where('linkedin_id', $linkedinId);
        } elseif ($publicUrl) {
            $query->where('public_url', $publicUrl);
        }

        $profile = $query->first();

        if (!$profile) {
            $profile = new static();
            $profile->user_id = $user->id;

            $profileType = $data['profile_type'] ?? 'own';
            $profile->profile_type = $profileType;

            $hasPrimaryOwn = static::query()
                ->forUser($user->id)
                ->owned()
                ->where('is_primary', true)
                ->exists();

            if (!$hasPrimaryOwn && $profileType === 'own') {
                $profile->is_primary = true;
            }
        }

        $profile->linkedin_id       = $linkedinId ?? $profile->linkedin_id;
        $profile->public_url        = $publicUrl ?? $profile->public_url;
        $profile->name              = $data['name'] ?? $profile->name ?? 'LinkedIn profile';
        $profile->headline          = $data['headline'] ?? $profile->headline;
        $profile->profile_image_url = $data['profile_image_url'] ?? $profile->profile_image_url;
        $profile->location          = $data['location'] ?? $profile->location;
        $profile->industry          = $data['industry'] ?? $profile->industry;
        $profile->connections_count = $data['connections_count'] ?? $profile->connections_count ?? 0;
        $profile->followers_count   = $data['followers_count'] ?? $profile->followers_count ?? 0;
        $profile->profile_type      = $data['profile_type'] ?? $profile->profile_type ?? 'own';
        $profile->last_synced_at    = $data['last_synced_at'] ?? now();
        $profile->sync_status       = $data['sync_status'] ?? 'ok';
        $profile->sync_error        = $data['sync_error'] ?? null;

        $profile->save();

        return $profile;
    }
}
