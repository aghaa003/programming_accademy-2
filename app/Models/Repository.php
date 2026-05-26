<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Repository extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'technologies',
        'repo_url',
        'live_demo_url',
        'file_url',
        'code_files_urls',
        'pdf_files_urls',
        'cover_image_url',
        'user_id',
        'likes',
        'is_public',
        'is_draft',
        'source_project',
    ];

    protected $casts = [
        'technologies' => 'array',
        'code_files_urls' => 'array',
        'pdf_files_urls' => 'array',
        'is_public' => 'boolean',
        'is_draft' => 'boolean',
        'likes' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function likes_relation()
    {
        return $this->hasMany(RepoLike::class, 'repository_id');
    }

    public function userLike($userId)
    {
        return $this->likes_relation()->where('user_id', $userId)->exists();
    }
}
