<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/createAdmin', [UserController::class, 'store']);
Route::post('/login', [UserController::class, 'adminLogin']);
Route::post('/createUser', [UserController::class, 'createUser']);
Route::get('/getUserToken/{id}', [UserController::class, 'getUserToken']);
Route::delete('/delete/{id}', [UserController::class, 'destroy']);
// ->middleware(['auth:sanctum', 'abilities:admin']);
