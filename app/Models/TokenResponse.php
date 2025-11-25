<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TokenResponse extends Model
{
    protected $fillable = [
        'token_id',
        'consumer_name',
        'contact_number',
        'email',
        'model',
        'query',
        'type',
    ];

    public function token()
    {
        return $this->belongsTo(Token::class);
    }
}
