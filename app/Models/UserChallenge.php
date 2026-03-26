<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserChallenge extends Model
{
    protected $table = 'user_challenges';

    protected $fillable = [
        'user_id', 'challenge_id', 'attempts', 'completed',
        'best_score', 'last_attempted',
    ];

    protected $casts = [
        'completed' => 'boolean',
        'last_attempted' => 'datetime',
    ];

    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function challenge()
    {
        return $this->belongsTo(Challenge::class);
    }
}
