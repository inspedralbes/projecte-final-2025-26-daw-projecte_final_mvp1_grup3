<?php


/**
 * Capa Laravel: user.
 * Comentaris: agents/backend/AgentLaravel.md
 */

//================================ NAMESPACES / IMPORTS ============

use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\FriendshipController;
use App\Http\Controllers\Api\GameStateReadController;
use App\Http\Controllers\Api\HabitReadController;
use App\Http\Controllers\Api\ExternalResourceController;
use App\Http\Controllers\Api\LogroReadController;
use App\Http\Controllers\Api\MonsterChoiceController;
use App\Http\Controllers\Api\OnboardingHabitAssignController;
use App\Http\Controllers\Api\PlantillaReadController;
use App\Http\Controllers\Api\ShopController;
use App\Http\Controllers\Api\SocialPostController;
use App\Http\Controllers\Api\SocialCommentController;
use App\Http\Controllers\Api\SocialLikeController;
use App\Http\Controllers\Api\SocialImportController;
use App\Http\Controllers\Api\SocialReportController;
use App\Http\Controllers\Api\UserHomeReadController;
use App\Http\Controllers\Api\UserProfileController;
use App\Http\Controllers\Api\UserProfileReadController;
use App\Http\Controllers\Api\ClanController;
use App\Http\Controllers\Api\ClanRequestController;
use App\Http\Controllers\Api\UserSearchController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| RutesChat PÚBLIQUES (fora del middleware d'autenticació)
|--------------------------------------------------------------------------
*/
Route::post('/chat/{receiverId}', [ChatController::class, 'sendMessageDebug']);
Route::get('/chat/{friendId}', [ChatController::class, 'getChatHistoryDebug']);
Route::put('/chat/{friendId}/read', [ChatController::class, 'markAsReadDebug']);

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
    Route::post('/user/monster-choice', [MonsterChoiceController::class, 'store']);
    Route::get('/user/monster', [MonsterChoiceController::class, 'show']);

    Route::get('/users/{id}/profile', [UserProfileController::class, 'getPublicProfile']);
    Route::get('/users/{id}/logs', [UserProfileController::class, 'getPublicLogs']);
    Route::get('/users/self/profile', [UserProfileController::class, 'getSelfProfile']);
    Route::put('/users/self/showcase', [UserProfileController::class, 'updateShowcase']);
    Route::put('/users/self/account', [UserProfileController::class, 'updateAccount']);
    Route::get('/users', [UserSearchController::class, 'search']);

    Route::post('/friends/request', [FriendshipController::class, 'sendRequest']);
    Route::put('/friends/accept/{id}', [FriendshipController::class, 'acceptRequest']);
    Route::put('/friends/reject/{id}', [FriendshipController::class, 'rejectRequest']);
    Route::delete('/friends/{id}', [FriendshipController::class, 'removeFriend']);
    Route::get('/friends', [FriendshipController::class, 'getFriendsList']);
    Route::get('/friends/pending', [FriendshipController::class, 'getPendingRequests']);

    Route::get('/clans', [ClanController::class, 'index']);
    Route::post('/clans', [ClanController::class, 'create']);
    Route::get('/clans/me', [ClanController::class, 'myClan']);
    Route::get('/clans/{id}', [ClanController::class, 'show']);
    Route::put('/clans/{id}', [ClanController::class, 'update']);
    Route::post('/clans/{id}/leave', [ClanController::class, 'leave']);
    Route::get('/clans/{id}/members', [ClanController::class, 'members']);
    Route::get('/clans/{id}/messages', [ClanController::class, 'messages']);
    Route::post('/clans/{id}/messages', [ClanController::class, 'sendMessage']);
    Route::post('/clans/{id}/share/habit', [ClanController::class, 'shareHabit']);
    Route::post('/clans/{id}/share/plantilla', [ClanController::class, 'sharePlantilla']);
    Route::post('/clans/{id}/import/habit/{messageId}', [ClanController::class, 'importHabit']);
    Route::post('/clans/{id}/import/plantilla/{messageId}', [ClanController::class, 'importPlantilla']);

    Route::get('/shop', [ShopController::class, 'index']);
    Route::post('/shop/comprar/{itemId}', [ShopController::class, 'comprar']);
    Route::post('/shop/equipar/{usuariItemId}', [ShopController::class, 'equipar']);
    Route::post('/shop/usar/{usuariItemId}', [ShopController::class, 'usarConsumible']);

    Route::post('/clans/{id}/join', [ClanRequestController::class, 'joinPublic']);
    Route::post('/clans/{id}/request', [ClanRequestController::class, 'requestJoin']);
    Route::post('/clans/{id}/invite', [ClanRequestController::class, 'invite']);
    Route::get('/clans/{id}/requests', [ClanRequestController::class, 'getPendingRequests']);
    Route::put('/clan-requests/{requestId}/accept', [ClanRequestController::class, 'acceptRequest']);
    Route::put('/clan-requests/{requestId}/reject', [ClanRequestController::class, 'rejectRequest']);
    Route::delete('/clans/{id}/members/{memberId}', [ClanRequestController::class, 'removeMember']);
    Route::put('/clan-invitations/{invitationId}/accept', [ClanRequestController::class, 'acceptInvitation']);
});
