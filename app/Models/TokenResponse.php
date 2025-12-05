<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// use App\Models\Token;

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
        "converted_at"
    ];

    public function leads()
    {
        return $this->hasOne(Lead::class , "token_responses_id");
    }

    public function token()
    {
        return $this->belongsTo(\App\Models\Token::class);
    }

}
