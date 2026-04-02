<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Challenge extends Model
{
    use HasFactory;
    protected $fillable = [
        'title', 'description', 'category', 'difficulty', 'points',
        'starter_code', 'code_language', 'test_cases',
        'solution_template', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'test_cases' => 'array',
    ];

    public function userChallenges()
    {
        return $this->hasMany(UserChallenge::class);
    }

    public function attempts()
    {
        return $this->hasMany(ChallengeAttempt::class);
    }
}
