<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\LikeController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\FollowController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ConversationController;
use App\Http\Controllers\Api\MessageController;
use Illuminate\Support\Facades\Route;

// Test route - nota che non serve più il prefisso 'api/'
Route::get('/test', function () {
    return response()->json(['message' => 'API is working']);
});

// Auth routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);                    

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('posts', PostController::class);
    Route::post('/posts/{post}/like', [LikeController::class, 'toggle']);
    Route::get('/posts/{post}/is-liked', [LikeController::class, 'isLiked']);
    
    Route::get('/posts/{post}/comments', [CommentController::class, 'index']);
    Route::post('/posts/{post}/comments', [CommentController::class, 'store']);
    Route::put('/posts/{post}/comments/{comment}', [CommentController::class, 'update']);
    Route::delete('/posts/{post}/comments/{comment}', [CommentController::class, 'destroy']);

    Route::post('/users/{user}/follow', [FollowController::class, 'toggle']);
    Route::post('/follows/{follow}/accept', [FollowController::class, 'acceptRequest']);
    Route::post('/follows/{follow}/reject', [FollowController::class, 'rejectRequest']);
    Route::get('/follows/pending', [FollowController::class, 'getPendingRequests']);
    Route::get('/users/{user}/followers', [FollowController::class, 'followers']);
    Route::get('/users/{user}/following', [FollowController::class, 'following']);

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread', [NotificationController::class, 'unread']);
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy']);

    Route::get('/conversations', [ConversationController::class, 'index']);
    Route::post('/conversations', [ConversationController::class, 'store']);
    Route::get('/conversations/{conversation}', [ConversationController::class, 'show']);
    
    Route::post('/conversations/{conversation}/messages', [MessageController::class, 'store']);
    Route::post('/conversations/{conversation}/read', [MessageController::class, 'markAsRead']);

    Route::get('/conversations/search', [ConversationController::class, 'search']);
    Route::get('/conversations/{conversation}/messages/search', [MessageController::class, 'search']);
});