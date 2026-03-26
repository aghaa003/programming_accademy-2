<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Platform extends Model
{
    protected $fillable = [
        'name', 'description', 'url', 'category', 'level', 'language',
        'rating', 'user_count', 'problem_count', 'features',
        'logo_url', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'features' => 'array',
        'rating' => 'float',
    ];

    public $timestamps = false;

    public function bookmarks()
    {
        return $this->hasMany(PlatformBookmark::class);
    }

    public function ratings()
    {
        return $this->hasMany(PlatformRating::class);
    }
}
