<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Token extends Model
{
    protected $fillable = [
        'token',
        'user_id',
    ];

    public function tokenResponse()
    {
        return $this->hasMany(TokenResponse::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function responses()
    {
        return $this->hasMany(TokenResponse::class);
    }

}
