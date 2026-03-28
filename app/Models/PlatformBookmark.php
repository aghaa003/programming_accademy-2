<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformBookmark extends Model
{
    protected $table = 'platform_bookmarks';

    protected $fillable = ['user_id', 'platform_id'];

    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function platform()
    {
        return $this->belongsTo(Platform::class);
    }
}
