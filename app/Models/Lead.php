<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $fillable = [
        'token_id',
        'is_converted',
        'imei',
        'remarks'
    ];

    public function token()
    {
        return $this->belongsTo(Token::class);
    }
}
