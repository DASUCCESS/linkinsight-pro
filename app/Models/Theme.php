<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Theme extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'version',
        'is_active',
        'config',
        'path',
        'author',
        'screenshot',
        'is_installed',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'config' => 'array',
        'is_installed' => 'boolean',
    ];

    public static function active(): ?self
    {
        return static::where('is_active', true)->first();
    }
}
