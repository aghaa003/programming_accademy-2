<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Example extends Model
{
    protected $fillable = [
        'title', 'description', 'category', 'difficulty', 'image_url',
        'code_snippet', 'code_language', 'technologies', 'demo_url',
        'requires_special_env', 'special_env_message', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'requires_special_env' => 'boolean',
        'technologies' => 'array',
    ];
}
