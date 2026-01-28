<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
        'subject',
        'body_html',
    ];

    public function scopeByKey($query, string $key)
    {
        return $query->where('key', $key);
    }

    public static function findByKey(string $key): ?self
    {
        return static::where('key', $key)->first();
    }
}
