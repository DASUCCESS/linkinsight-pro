<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LinkedinAudienceInsight extends Model
{
    use HasFactory;

    protected $fillable = [
        'linkedin_profile_id',
        'snapshot_date',
        'top_job_titles',
        'top_industries',
        'top_locations',
        'engagement_sources',
    ];

    protected $casts = [
        'snapshot_date'      => 'date',
        'top_job_titles'     => 'array',
        'top_industries'     => 'array',
        'top_locations'      => 'array',
        'engagement_sources' => 'array',
    ];

    public function profile()
    {
        return $this->belongsTo(LinkedinProfile::class, 'linkedin_profile_id');
    }
}
