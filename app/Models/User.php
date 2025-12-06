<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
// use App\Models\Token;

// class User extends Authenticatable
// {
//     /** @use HasFactory<\Database\Factories\UserFactory> */
//     use HasFactory, Notifiable, HasApiTokens;

//     /**
//      * The attributes that are mass assignable.
//      *
//      * @var list<string>
//      */
//     protected $fillable = [
//         'name',
//         'email',
//         'mobile',
//         "isLogin",
//         "password",
//         "role"
//     ];


//     /**
//      * Get the attributes that should be cast.
//      *
//      * @return array<string, string>
//      */
//     protected function casts(): array
//     {
//         return [
//             'password' => 'hashed',
//         ];
//     }


//     public function tokenResponses()
//     {
//         return $this->hasManyThrough(
//             TokenResponse::class,
//             \App\Models\Token::class,
//             'user_id', // Foreign key on tokens
//             'token_id', // Foreign key on token_responses
//             'id', // Local key on users
//             'id'  // Local key on tokens
//         );
//     }

//     public function leads()
//     {
//         return $this->hasManyThrough(
//             Lead::class,
//             TokenResponse::class,
//             'token_id',   // FK on token_responses
//             'token_responses_id', // FK on leads
//             'id',          // users.id
//             'id'           // token_responses.id
//         );
//     }
// }

// namespace App\Models;

// use Laravel\Sanctum\HasApiTokens;
// use Illuminate\Foundation\Auth\User as Authenticatable;
// use Illuminate\Notifications\Notifiable;
// use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'mobile',
        'isLogin',
        'password',
        'role'
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    protected $hidden = ['password', 'isLogin','role'];


    // -----------------------------------------
    // REQUIRED FIX: This was missing!
    // -----------------------------------------
    public function token()
    {
        return $this->hasMany(\App\Models\Token::class, 'user_id');
    }

    public function tokenResponses()
    {
        return $this->hasManyThrough(
            \App\Models\TokenResponse::class,
            \App\Models\Token::class,
            'user_id',
            'token_id',
            'id',
            'id'
        );
    }

    // public function leads()
    // {
    //     return $this->hasManyThrough(
    //         \App\Models\Lead::class,
    //         \App\Models\TokenResponse::class,
    //         'token_id',
    //         'token_responses_id',
    //         'id',
    //         'id'
    //     );
    // }

    public function leads()
    {
        return $this->hasManyThrough(
            \App\Models\Lead::class,          // Final model
            \App\Models\TokenResponse::class, // Intermediate model
            'token_id',                       // FK on TokenResponse → token.id
            'token_responses_id',             // FK on Lead → token_responses.id
            'id',                             // Local key on Token
            'id'                              // Local key on TokenResponse
        );
    }

}

