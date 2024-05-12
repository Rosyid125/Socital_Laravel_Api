<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\NotificationController;
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

//auth 
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);
//users
Route::prefix('/users')->group(function(){
    Route::get('/', [userController::class, 'getAllUsers']);
    Route::get('/search', [userController::class, 'searchUser']);
    Route::prefix('/{userid}')->group(function(){
        Route::get('/', [userController::class, 'userDetails']);
        //users: posts
        Route::get('/allposts', [PostController::class, 'getAllPosts']);
        Route::get('/posts', [PostController::class, 'getAllUserPosts']);
    });
});
//posts
Route::prefix('/posts/{postid}')->group(function(){
    Route::get('/', [PostController::class, 'postDetails']);
    //posts: likes
    Route::get('/likes', [LikeController::class, 'getLikes']);
    //posts: comments
    Route::get('/comments', [CommentController::class, 'getComments']);
});
 //follows
Route::prefix('/follows/{userid}')->group(function(){
    Route::get('/followers', [FollowController::class, 'getFollowers']);
    Route::get('/followings', [FollowController::class, 'getFollowing']);
});
//notifications
Route::get('/notifications/{userid}', [FollowController::class, 'getNotifications']);

//protected routes
Route::middleware('auth:sanctum')->group(function(){
    //auth
    Route::delete('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    //users
    Route::patch('/users/{userid}', [userController::class, 'editUser']);
    //posts
    Route::prefix('/posts')->group(function(){
        Route::post('/create', [PostController::class, 'createPost']);
        Route::prefix('/{postid}')->group(function(){
            Route::patch('/edit', [PostController::class, 'editPost']);
            Route::delete('/delete', [PostController::class, 'deletePost']);
            //posts: likes
            Route::prefix('/likes')->group(function(){
                Route::post('/like', [LikeController::class, 'likePost']);
                Route::delete('/{likeid}', [LikeController::class, 'dislikePost']);
            });
            //posts: comments
            Route::prefix('/comments')->group(function(){
                Route::post('/add', [CommentController::class, 'addComment']);
                Route::delete('/{commentid}', [CommentController::class, 'deleteComment']);
            });
        });
    });
    //follows
    Route::prefix('/follows')->group(function(){
        Route::post('/{userid}', [FollowController::class, 'follow']);
        Route::delete('/{followid}', [FollowController::class, 'unfollow']);
    });
    //notifications
    Route::patch('/notifications/{notificationid}', [NotificationController::class, 'markAsRead']);
});
