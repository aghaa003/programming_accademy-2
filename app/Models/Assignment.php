<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'course_id', 'title', 'description', 'requirements',
        'difficulty', 'language', 'due_date', 'is_active',
        'question', 'assignment_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'due_date' => 'datetime',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function userAssignments()
    {
        return $this->hasMany(UserAssignment::class);
    }
}
