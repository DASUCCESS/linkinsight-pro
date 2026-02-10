<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LinkedinSyncJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'linkedin_profile_id',
        'source',
        'type',
        'status',
        'items_count', 
        'error_message',
        'payload',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'payload'     => 'array',
        'started_at'  => 'datetime',
        'finished_at' => 'datetime',
        'items_count' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function profile()
    {
        return $this->belongsTo(LinkedinProfile::class, 'linkedin_profile_id');
    }
}
