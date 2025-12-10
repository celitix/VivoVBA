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
        'model_id',
        'query',
        'type',
        "convered_at",
        "message"
    ];

    public function leads()
    {
        return $this->hasOne(Lead::class, "token_responses_id");
    }

    public function lead()
    {
        return $this->hasMany(Lead::class, "token_responses_id");
    }

    public function token()
    {
        return $this->belongsTo(\App\Models\Token::class);
    }

    public function model()
    {
        return $this->belongsTo(\App\Models\MobileModel::class, 'model_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

}
