<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LinkedinPostMetric extends Model
{
    use HasFactory;

    protected $fillable = [
        'linkedin_post_id',
        'metric_date',
        'impressions',
        'unique_impressions',
        'clicks',
        'reactions',
        'comments',
        'reposts',
        'saves',
        'video_views',
        'follows_from_post',
        'profile_visits_from_post',
        'engagement_rate',
    ];

    protected $casts = [
        'metric_date'             => 'date',
        'engagement_rate'         => 'float',
        'impressions'             => 'integer',
        'unique_impressions'      => 'integer',
        'clicks'                  => 'integer',
        'reactions'               => 'integer',
        'comments'                => 'integer',
        'reposts'                 => 'integer',
        'saves'                   => 'integer',
        'video_views'             => 'integer',
        'follows_from_post'       => 'integer',
        'profile_visits_from_post'=> 'integer',
    ];

    public function post()
    {
        return $this->belongsTo(LinkedinPost::class, 'linkedin_post_id');
    }
}
