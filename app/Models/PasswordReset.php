<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    protected $table = 'password_resets';

    protected $fillable = ['email', 'token', 'expires_at'];

    public $timestamps = false;
    const CREATED_AT = 'created_at';

    protected $casts = [
        'expires_at' => 'datetime',
    ];
}
