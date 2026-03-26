<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $table = 'academy_reviews';
    public $timestamps = false;

    protected $fillable = ['user_id', 'rating', 'review_text'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
