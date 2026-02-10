<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LinkedinPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'linkedin_profile_id',
        'linkedin_post_id',
        'permalink',
        'target_permalink',
        'posted_at',
        'post_type',
        'activity_category',
        'media_type',
        'is_reshare',
        'is_sponsored',
        'content_excerpt',
    ];

    protected $casts = [
        'posted_at'    => 'datetime',
        'is_reshare'   => 'boolean',
        'is_sponsored' => 'boolean',
    ];

    public function profile()
    {
        return $this->belongsTo(LinkedinProfile::class, 'linkedin_profile_id');
    }

    public function metrics()
    {
        return $this->hasMany(LinkedinPostMetric::class);
    }
}
