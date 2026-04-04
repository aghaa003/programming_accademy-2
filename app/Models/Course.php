<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;
    protected $fillable = [
        'title', 'description', 'main_points', 'category',
        'logo_path', 'level', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function lessons()
    {
        return $this->hasMany(Lesson::class)->orderBy('sort_order');
    }

    public function userProgress()
    {
        return $this->hasMany(UserCourseProgress::class);
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }
}
