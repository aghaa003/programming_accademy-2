<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'firstName', 'lastName', 'email', 'username', 'password',
        'phone', 'country', 'experience', 'goal', 'interest',
        'preferred_language', 'avatar_path', 'provider', 'provider_id',
        // NOTE: 'is_admin' is intentionally excluded — set only via AdminUserController::toggleAdmin()
        // NOTE: 'is_suspended' is intentionally excluded — set only via AdminUserController::toggleSuspend()
    ];

    protected $hidden = ['password'];

    protected $casts = [
        'is_suspended' => 'boolean',
    ];

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

    public function repositories()
    {
        return $this->hasMany(Repository::class);
    }

    public function communityPosts()
    {
        return $this->hasMany(CommunityPost::class);
    }

    public function communityComments()
    {
        return $this->hasMany(CommunityPostComment::class);
    }

    public function lessonComments()
    {
        return $this->hasMany(LessonComment::class);
    }

    public function repositoryLikes()
    {
        return $this->hasMany(RepoLike::class);
    }

    public function communityLikes()
    {
        return $this->hasMany(CommunityPostLike::class);
    }

    public function lessonLikes()
    {
        return $this->hasMany(LessonLike::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function uploads()
    {
        return $this->hasMany(Upload::class);
    }

    public function hasRole(string $role): bool
    {
        return $this->roles()->where('name', $role)->exists();
    }
}
