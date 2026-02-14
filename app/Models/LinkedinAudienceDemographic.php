<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LinkedinAudienceDemographic extends Model
{
    use HasFactory;

    protected $fillable = [
        'linkedin_profile_id',
        'snapshot_date',
        'demographics',
        'followers_count',
        'source_hash',
    ];

    protected $casts = [
        'snapshot_date'   => 'date',
        'demographics'    => 'array',
        'followers_count' => 'integer',
    ];

    public function profile()
    {
        return $this->belongsTo(LinkedinProfile::class, 'linkedin_profile_id');
    }
}
