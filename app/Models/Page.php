<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'type',
        'is_home',
        'is_published',
        'content',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'og_title',
        'og_description',
        'og_image',
        'json_ld',
        'indexable',
    ];

    protected $casts = [
        'is_home'       => 'boolean',
        'is_published'  => 'boolean',
        'indexable'     => 'boolean',
        'json_ld'       => 'array',
    ];

    public function sections()
    {
        return $this->hasMany(PageSection::class)->orderBy('position');
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeHome($query)
    {
        return $query->where('is_home', true);
    }
}
