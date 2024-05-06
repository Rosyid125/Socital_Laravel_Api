<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;
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
Route::get('/{userid}/allposts', [PostController::class, 'getallposts']); 
Route::get('/{userid}/alluserposts', [PostController::class, 'getalluserposts']); 
Route::post('/post/create', [PostController::class, 'createpost']); 
Route::get('/post/{postid}', [PostController::class, 'postdetails']); //belum dikerjakan
Route::post('/post/{postid}/edit', [PostController::class, 'editapost']); 
Route::delete('/post/{postid}/delete', [PostController::class, 'deleteapost']); 
