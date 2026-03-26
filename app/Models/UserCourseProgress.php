<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCourseProgress extends Model
{
    protected $table = 'user_course_progress';

    protected $primaryKey = null;
    public $incrementing = false;

    protected $fillable = [
        'user_id', 'course_id', 'percentage_completed', 'last_lesson_id',
    ];

    public $timestamps = false;
    const UPDATED_AT = 'last_accessed';
    const CREATED_AT = 'started_at';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function lastLesson()
    {
        return $this->belongsTo(Lesson::class, 'last_lesson_id');
    }
}
