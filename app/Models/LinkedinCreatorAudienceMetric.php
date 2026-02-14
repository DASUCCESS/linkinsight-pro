<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LinkedinCreatorAudienceMetric extends Model
{
    use HasFactory;

    protected $fillable = [
        'linkedin_profile_id',
        'metric_date',
        'metrics',
        'source_hash',
    ];

    protected $casts = [
        'metric_date' => 'date',
        'metrics'     => 'array',
    ];

    public function profile()
    {
        return $this->belongsTo(LinkedinProfile::class, 'linkedin_profile_id');
    }
}
