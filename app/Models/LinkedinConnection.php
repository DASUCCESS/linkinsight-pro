<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LinkedinConnection extends Model
{
    use HasFactory;

    protected $fillable = [
        'linkedin_profile_id',
        'linkedin_connection_id',
        'public_identifier',
        'profile_url',
        'full_name',
        'headline',
        'location',
        'industry',
        'profile_image_url',
        'degree',
        'mutual_connections_count',
        'connected_at',
        'last_seen_at',
        'dedupe_key',
        'source_hash',
    ];

    protected $casts = [
        'connected_at'            => 'datetime',
        'last_seen_at'            => 'datetime',
        'degree'                  => 'integer',
        'mutual_connections_count'=> 'integer',
    ];

    public function profile()
    {
        return $this->belongsTo(LinkedinProfile::class, 'linkedin_profile_id');
    }
}
