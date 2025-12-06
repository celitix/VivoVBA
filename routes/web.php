<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/export/{token}', [App\Http\Controllers\TokenController::class, 'export']);