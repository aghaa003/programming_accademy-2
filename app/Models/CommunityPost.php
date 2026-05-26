<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommunityPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'body',
        'tags',
        'likes_count',
        'comments_count',
    ];

    protected $casts = [
        'tags' => 'array',
        'likes_count' => 'integer',
        'comments_count' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->hasMany(CommunityPostComment::class, 'post_id');
    }

    public function likes()
    {
        return $this->hasMany(CommunityPostLike::class, 'post_id');
    }

    public function userLike($userId)
    {
        return $this->likes()->where('user_id', $userId)->exists();
    }
}
