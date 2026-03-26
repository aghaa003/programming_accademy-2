<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserLessonProgress extends Model
{
    protected $table = 'user_lesson_progress';

    protected $fillable = [
        'user_id', 'lesson_id', 'completed_at', 'last_position',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    public $timestamps = false;
    const UPDATED_AT = 'updated_at';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }
}
