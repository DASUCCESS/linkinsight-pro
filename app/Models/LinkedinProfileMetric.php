<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LinkedinProfileMetric extends Model
{
    use HasFactory;

    protected $fillable = [
        'linkedin_profile_id',
        'metric_date',
        'connections_count',
        'followers_count',
        'profile_views',
        'search_appearances',
        'posts_count',
        'impressions_7d',
        'engagements_7d',
    ];

    protected $casts = [
        'metric_date' => 'date',
    ];

    public function profile()
    {
        return $this->belongsTo(LinkedinProfile::class, 'linkedin_profile_id');
    }
}
