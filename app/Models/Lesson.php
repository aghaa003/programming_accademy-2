<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    protected $fillable = [
        'course_id', 'title', 'description', 'sort_order',
        'video_data', 'video_mime', 'resources_code', 'views',
    ];

    protected $hidden = ['video_data'];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function userProgress()
    {
        return $this->hasMany(UserLessonProgress::class);
    }
}
