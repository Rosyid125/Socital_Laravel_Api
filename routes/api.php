<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\LikeController;
use App\Http\Middleware\Authenticate;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::middleware('auth:sanctum')->group(function(){
    Route::delete('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
});

//btw ini belum terproteksi untuk testing
Route::get('/{userid}/allposts', [PostController::class, 'getAllPosts']); 
Route::get('/{userid}/alluserposts', [PostController::class, 'getAllUserPosts']); 
Route::post('/post/create', [PostController::class, 'createPost']); 
Route::get('/post/{postid}', [PostController::class, 'postDetails']);
Route::post('/post/{postid}/edit', [PostController::class, 'editPost']); 
Route::delete('/post/{postid}/delete', [PostController::class, 'deletePost']); 
Route::post('/post/{postid}/like', [LikeController::class, 'likePost']); 
Route::delete('/post/{postid}/dislike', [LikeController::class, 'dislikePost']); 
Route::get('/post/{postid}/likedby', [LikeController::class, 'likedBy']); 

