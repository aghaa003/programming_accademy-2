<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'firstName', 'lastName', 'email', 'username', 'password',
        'phone', 'country', 'experience', 'goal', 'interest',
        'preferred_language', 'is_admin',
    ];

    protected $hidden = ['password', 'avatar_data'];

    protected $casts = ['is_admin' => 'boolean'];

    public $timestamps = false;

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    public function lessonProgress()
    {
        return $this->hasMany(UserLessonProgress::class);
    }

    public function courseProgress()
    {
        return $this->hasMany(UserCourseProgress::class);
    }

    public function challenges()
    {
        return $this->hasMany(UserChallenge::class);
    }

    public function assignments()
    {
        return $this->hasMany(UserAssignment::class);
    }

    public function platformBookmarks()
    {
        return $this->hasMany(PlatformBookmark::class);
    }

    public function platformRatings()
    {
        return $this->hasMany(PlatformRating::class);
    }

    public function preferences()
    {
        return $this->hasOne(UserPreference::class);
    }

    public function hasRole(string $role): bool
    {
        return $this->roles()->where('name', $role)->exists();
    }
}
