<?php

use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\FriendshipController;
use App\Http\Controllers\Api\GameStateReadController;
use App\Http\Controllers\Api\HabitReadController;
use App\Http\Controllers\Api\ExternalResourceController;
use App\Http\Controllers\Api\LogroReadController;
use App\Http\Controllers\Api\OnboardingHabitAssignController;
use App\Http\Controllers\Api\PlantillaReadController;
use App\Http\Controllers\Api\SocialPostController;
use App\Http\Controllers\Api\SocialCommentController;
use App\Http\Controllers\Api\SocialLikeController;
use App\Http\Controllers\Api\SocialImportController;
use App\Http\Controllers\Api\SocialReportController;
use App\Http\Controllers\Api\UserHomeReadController;
use App\Http\Controllers\Api\UserProfileController;
use App\Http\Controllers\Api\UserProfileReadController;
use App\Http\Controllers\Api\UserSearchController;
use App\Http\Controllers\WebRTCSignalController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutes usuari (middleware ensure.user)
|--------------------------------------------------------------------------
*/

Route::middleware('ensure.user')->group(function () {
    Route::get('/social/posts', [SocialPostController::class, 'index']);
    Route::post('/social/posts', [SocialPostController::class, 'store']);
    Route::get('/social/posts/{id}', [SocialPostController::class, 'show']);
    Route::delete('/social/posts/{id}', [SocialPostController::class, 'destroy']);

    Route::get('/social/comments/{postId}', [SocialCommentController::class, 'index']);
    Route::post('/social/comments', [SocialCommentController::class, 'store']);
    Route::delete('/social/comments/{id}', [SocialCommentController::class, 'destroy']);

    Route::post('/social/likes', [SocialLikeController::class, 'store']);
    Route::get('/social/likes/check', [SocialLikeController::class, 'check']);

    Route::post('/social/import/habit', [SocialImportController::class, 'importHabit']);
    Route::post('/social/import/plantilla', [SocialImportController::class, 'importPlantilla']);

    Route::post('/social/report', [SocialReportController::class, 'store']);

    Route::get('/habits', [HabitReadController::class, 'index']);
    Route::get('/habits/all', [HabitReadController::class, 'indexAll']);
    Route::get('/habits/progress', [HabitReadController::class, 'progress']);
    Route::get('/habits/logs', [HabitReadController::class, 'logs']);
    Route::get('/habits/{id}', [HabitReadController::class, 'show']);
    Route::post('/habits/complete', [HabitReadController::class, 'complete']);
    Route::get('/external/books', [ExternalResourceController::class, 'books']);
    Route::get('/external/workouts', [ExternalResourceController::class, 'workouts']);
    Route::get('/external/nutrition', [ExternalResourceController::class, 'nutrition']);
    Route::get('/external/exercise/{exerciseId}', [ExternalResourceController::class, 'exerciseDetail']);
    Route::get('/external/videos', [ExternalResourceController::class, 'videos']);
    Route::get('/external/weather', [ExternalResourceController::class, 'weather']);
    Route::post('/habits/assign', [OnboardingHabitAssignController::class, 'assign']);
    Route::get('/plantilles', [PlantillaReadController::class, 'index']);
    Route::get('/plantilles/{id}', [PlantillaReadController::class, 'show']);
    Route::get('/game-state', [GameStateReadController::class, 'show']);
    Route::get('/user/home', [UserHomeReadController::class, 'index']);
    Route::get('/logros', [LogroReadController::class, 'index']);
    Route::get('/user/profile', [UserProfileReadController::class, 'profile']);

    Route::get('/users/{id}/profile', [UserProfileController::class, 'getPublicProfile']);
    Route::get('/users/self/profile', [UserProfileController::class, 'getSelfProfile']);
    Route::get('/users', [UserSearchController::class, 'search']);

    Route::post('/friends/request', [FriendshipController::class, 'sendRequest']);
    Route::put('/friends/accept/{id}', [FriendshipController::class, 'acceptRequest']);
    Route::put('/friends/reject/{id}', [FriendshipController::class, 'rejectRequest']);
    Route::get('/friends', [FriendshipController::class, 'getFriendsList']);
    Route::get('/friends/pending', [FriendshipController::class, 'getPendingRequests']);

    Route::post('/chat/{receiverId}', [ChatController::class, 'sendMessage']);
    Route::get('/chat/{friendId}', [ChatController::class, 'getChatHistory']);
    Route::put('/chat/{friendId}/read', [ChatController::class, 'markAsRead']);
    Route::post('/webrtc-signal', [WebRTCSignalController::class, 'handleSignal']);
    Route::get('/webrtc-rooms/{friendId}', [WebRTCSignalController::class, 'getRoom']);
    Route::post('/webrtc-rooms/{friendId}/join', [WebRTCSignalController::class, 'joinRoom']);
});
