<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAssignment extends Model
{
    protected $table = 'user_assignments';

    protected $fillable = [
        'user_id', 'assignment_id', 'solution', 'score',
        'status', 'is_completed', 'completed_at',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public $timestamps = false;
    const CREATED_AT = 'submitted_at';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignment()
    {
        return $this->belongsTo(Assignment::class);
    }
}
