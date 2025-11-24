<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/createAdmin', [UserController::class, 'store']);
Route::post('/login', [UserController::class, 'adminLogin']);
Route::post('/createUser', [UserController::class, 'createUser'])->middleware(['auth:sanctum', 'abilities:admin']);
