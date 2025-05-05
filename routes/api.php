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
    // get user details
    Route::put('/users/{id}', [\App\Http\Controllers\UserDetailController::class, 'update']);
    Route::get('/users/{id}', [\App\Http\Controllers\UserDetailController::class, 'get']);

    //follow reccomendations
    Route::get('/follows', [\App\Http\Controllers\FollowController::class, 'index']);

    //get user follows
    Route::get('/users/{id}/follows', [\App\Http\Controllers\FollowController::class, 'follows']);

    //follow user  
    Route::post('/users/{id}/follows', [\App\Http\Controllers\FollowController::class, 'follow']);

    //get all articles  
    Route::get('/articles', [\App\Http\Controllers\ArticleController::class, 'index']);

    //get article by id
    Route::get('/articles/{id}', [\App\Http\Controllers\ArticleController::class, 'get']);

    //get user articles
    Route::get('/users/{id}/articles', [\App\Http\Controllers\ArticleController::class, 'user_articles']);

    //create article
    Route::post('/articles', [\App\Http\Controllers\ArticleController::class, 'create']);

    //edit article
    Route::put('/articles/{id}', [\App\Http\Controllers\ArticleController::class, 'update']);

    //delete article 
    Route::delete('/articles/{id}', [\App\Http\Controllers\ArticleController::class, 'delete']);
});