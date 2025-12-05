<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $fillable = [
        'token_responses_id',
        'is_converted',
        'imei',
        'remarks'
    ];
    public function tokenResponse()
    {
        return $this->belongsTo(TokenResponse::class, 'token_responses_id');
    }

}
