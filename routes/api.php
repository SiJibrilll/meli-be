<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/register', [\App\Http\Controllers\AuthController::class, 'register']);
Route::post('/login', [\App\Http\Controllers\AuthController::class, 'login']);

//create route group for authenticated users
Route::middleware('auth:sanctum')->group(function () {
    Route::put('/user/{id}', [\App\Http\Controllers\UserDetailController::class, 'update']);
});