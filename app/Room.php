<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    public function users()
    {
        return $this->belongsToMany('App\User', 'user_chat');
    }
    public function messages()
    {
        return $this->hasMany('App\Message');
    }
}
