<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformRating extends Model
{
    protected $table = 'platform_ratings';

    protected $fillable = ['user_id', 'platform_id', 'rating'];

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
