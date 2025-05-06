<?php

use App\Http\Controllers\ArticleVerificationController;
use App\Http\Controllers\LikeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/register', [\App\Http\Controllers\AuthController::class, 'register']);
Route::post('/login', [\App\Http\Controllers\AuthController::class, 'login']);

//create route group for authenticated users
Route::middleware('auth:sanctum')->group(function () {
    // update user details
    Route::put('/users/{id}', [\App\Http\Controllers\UserDetailController::class, 'update']);

    //get user details
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
    
    //like article
    Route::post('/articles/{id}/likes', [LikeController::class, 'like']);
    
    //unlike article
    Route::delete('/articles/{id}/likes', [LikeController::class, 'unlike']);
    
    //get all communities
    Route::get('/communities', [\App\Http\Controllers\CommunityController::class, 'index']);
    
    //get community by id
    Route::get('/communities/{id}', [\App\Http\Controllers\CommunityController::class, 'get']);
    
    //join community
    Route::post('/communities/{id}/join', [\App\Http\Controllers\CommunityController::class, 'join']);

    //leave community
    Route::delete('/communities/{id}/leave', [\App\Http\Controllers\CommunityController::class, 'leave']);

    // route group for doctor role
    Route::middleware('role:doctor')->group(function () {
        //create community
        Route::post('/communities', [\App\Http\Controllers\CommunityController::class, 'create']);

        //edit community
        Route::put('/communities/{id}', [\App\Http\Controllers\CommunityController::class, 'update']);

        //delete community
        Route::delete('/communities/{id}', [\App\Http\Controllers\CommunityController::class, 'delete']);

       

        //verify article by doctor role
        Route::post('/articles/{id}/verifies', [ArticleVerificationController::class, 'verify'])->middleware('role:doctor');

        //unverify article by doctor role
        Route::delete('/articles/{id}/verifies', [ArticleVerificationController::class, 'unverify'])->middleware('role:doctor');        
    });
});