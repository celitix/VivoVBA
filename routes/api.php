<?php

use App\Http\Controllers\Token;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;


Route::post("/save", [Token::class, 'store']);
Route::post("/sendOtp", [UserController::class, 'sendOtp']);
Route::post("/verifyOtp", [UserController::class, 'verifyOtp']);


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/allUsers', [UserController::class, 'allUsers']);
    Route::post('/createAdmin', [UserController::class, 'store']);
    Route::post('/login', [UserController::class, 'adminLogin']);
    Route::post('/createUser', [UserController::class, 'createUser']);
    Route::get('/getUserToken/{id}', [UserController::class, 'getUserToken']);
    Route::post('/delete/{id}', [UserController::class, 'destroy']);
    // Route::get('/allUsers', [UserController::class, 'allUsers']);


    Route::get("/response/{token}", [Token::class, 'getTokenResponse']);
});
