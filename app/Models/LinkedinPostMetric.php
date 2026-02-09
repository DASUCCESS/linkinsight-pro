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
        'clicks',
        'reactions',
        'comments',
        'reposts',
        'saves',
        'engagement_rate',
    ];

    protected $casts = [
        'metric_date'     => 'date',
        'engagement_rate' => 'float',
    ];

    public function post()
    {
        return $this->belongsTo(LinkedinPost::class, 'linkedin_post_id');
    }
}
