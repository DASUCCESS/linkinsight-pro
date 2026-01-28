<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PageSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'page_id',
        'key',
        'title',
        'subtitle',
        'body',
        'image_path',
        'icon',
        'position',
        'is_visible',
        'settings',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
        'settings'   => 'array',
    ];

    public function page()
    {
        return $this->belongsTo(Page::class);
    }

    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }
}
