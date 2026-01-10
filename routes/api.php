<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PlayerController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\EventCommentController;
use App\Http\Controllers\Api\FollowController;
use App\Http\Controllers\Api\LikeController;
use App\Http\Controllers\Api\PlayerCommentController;
use Illuminate\Support\Facades\Route;

// Public Player Routes
Route::get('/players', [PlayerController::class, 'index']);
Route::get('/players/{id}', [PlayerController::class, 'show'])->withoutMiddleware(['auth:sanctum', 'throttle:60,1']);

// Public Event Routes
Route::get('/events', [EventController::class, 'index']);
Route::get('/events/{id}', [EventController::class, 'show']);
Route::get('/events/{id}/share', [EventController::class, 'share']);
Route::get('/events/{id}/comments', [EventCommentController::class, 'index']);
Route::get('/profile/{uid}', [ProfileController::class, 'show']);
Route::get('/profile/{uid}/events', [ProfileController::class, 'events']);

// Player Comments (Public Read)
Route::get('/players/{id}/comments', [PlayerCommentController::class, 'index']);

// Personal Lists (Public Read)
Route::get('/following/{uid}', [FollowController::class, 'following']);
Route::get('/followers/{uid}', [FollowController::class, 'followers']);
Route::get('/likes/{uid}', [LikeController::class, 'index']);

// Protected Routes (requires LINE login)
Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::put('/user/settings', [UserController::class, 'updateSettings']);
    
    // Profile
    Route::post('/profile/update', [ProfileController::class, 'update']);

    // Player Management (authenticated)
    Route::post('/players', [PlayerController::class, 'store']);
    Route::get('/my-cards', [PlayerController::class, 'myCards']);
    Route::put('/players/{id}', [PlayerController::class, 'update']);
    Route::delete('/players/{id}', [PlayerController::class, 'destroy']);
    Route::post('/players/{id}/photo', [PlayerController::class, 'uploadPhoto']);
    
    // Messages
    Route::get('/messages', [MessageController::class, 'index']);
    Route::post('/messages', [MessageController::class, 'store']);
    Route::get('/messages/unread-count', [MessageController::class, 'unreadCount']);
    Route::get('/messages/sent', [MessageController::class, 'sent']);
    Route::get('/messages/chat/{uid}', [MessageController::class, 'chat']);
    Route::get('/messages/{id}', [MessageController::class, 'show']);
    Route::put('/messages/{id}/read', [MessageController::class, 'markRead']);
    Route::delete('/messages/{id}', [MessageController::class, 'destroy']);
    
    // Events (authenticated)
    Route::post('/events', [EventController::class, 'store']);
    Route::put('/events/{id}', [EventController::class, 'update']);
    Route::delete('/events/{id}', [EventController::class, 'destroy']);
    Route::post('/events/{id}/join', [EventController::class, 'join']);
    Route::post('/events/{id}/leave', [EventController::class, 'leave']);
    Route::post('/events/{id}/comments', [EventCommentController::class, 'store']);
    Route::delete('/events/comments/{id}', [EventCommentController::class, 'destroy']);

    // Player Comments (Authenticated Write)
    Route::post('/players/{id}/comments', [PlayerCommentController::class, 'store']);
    Route::delete('/players/comments/{id}', [PlayerCommentController::class, 'destroy']);
    Route::get('/my-events/organized', [EventController::class, 'myOrganized']);
    Route::get('/my-events/joined', [EventController::class, 'myJoined']);

    // Social Actions
    Route::post('/follow/{uid}', [FollowController::class, 'follow']);
    Route::post('/unfollow/{uid}', [FollowController::class, 'unfollow']);
    Route::get('/follow/status/{uid}', [FollowController::class, 'status']);
    
    Route::post('/like/{playerId}', [LikeController::class, 'like']);
    Route::post('/unlike/{playerId}', [LikeController::class, 'unlike']);
    Route::get('/like/status/{playerId}', [LikeController::class, 'status']);
});
