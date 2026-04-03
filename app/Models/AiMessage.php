<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiMessage extends Model
{
    protected $fillable = ['conversation_id', 'role', 'content', 'has_images'];

    protected $casts = ['has_images' => 'boolean'];

    public function conversation()
    {
        return $this->belongsTo(AiConversation::class);
    }
}
