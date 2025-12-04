<?php

use App\Http\Controllers\ModelController;
use App\Http\Controllers\Token;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;


Route::post("/save", [Token::class, 'store']);
Route::post("/sendOtp", [UserController::class, 'sendOtp']);
Route::post("/verifyOtp", [UserController::class, 'verifyOtp']);
Route::post('/login', [UserController::class, 'login']);
Route::get("/model", [ModelController::class, 'get']);


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/allUsers', [UserController::class, 'allUsers']);
    Route::post('/createAdmin', [UserController::class, 'store']);
    Route::post('/createUser', [UserController::class, 'createUser']);
    Route::get('/getUserToken/{id}', [UserController::class, 'getUserToken']);
    Route::post('/delete/{id}', [UserController::class, 'destroy']);
    // Route::get('/allUsers', [UserController::class, 'allUsers']);


    Route::get("/response/{token}", [Token::class, 'getTokenResponse']);

    //model
    // model/deleted/:id
    Route::post("/model", [ModelController::class, 'create']);
    Route::put("/model", [ModelController::class, 'update']);
    Route::delete("/model/{id}", [ModelController::class, 'delete']);
    Route::get("/model/deleted", [ModelController::class, 'deletedModel']);
    Route::post("/model/restore/{id}", [ModelController::class, 'restore']);
    Route::delete("/model/deleted/{id}", [ModelController::class, 'hardDelete']);
});
