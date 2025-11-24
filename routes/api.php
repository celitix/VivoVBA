<?php

use App\Http\Controllers\Token;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/createAdmin', [UserController::class, 'store']);
Route::post('/login', [UserController::class, 'adminLogin']);
Route::post('/createUser', [UserController::class, 'createUser']);
Route::get('/getUserToken/{id}', [UserController::class, 'getUserToken']);
Route::post('/delete/{id}', [UserController::class, 'destroy']);


Route::get("/response/{token}", [Token::class, 'getTokenResponse']);
Route::post("/save", [Token::class, 'store']);


// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     Route::post('/createAdmin', [UserController::class, 'store']);
//     Route::post('/createUser', [UserController::class, 'createUser']);
//     Route::get('/getUserToken/{id}', [UserController::class, 'getUserToken']);
//     Route::post('/delete/{id}', [UserController::class, 'destroy']);

//     Route::get("/response/{token}", [UserController::class, 'getTokenResponse']);
// });
