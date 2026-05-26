<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RepoLike extends Model
{
    use HasFactory;

    protected $fillable = ['repository_id', 'user_id'];
    public $timestamps = true;

    public function repository()
    {
        return $this->belongsTo(Repository::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
