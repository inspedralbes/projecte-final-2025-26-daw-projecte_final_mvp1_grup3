<?php

use App\Http\Controllers\Api\GameStateReadController;
use App\Http\Controllers\Api\HabitReadController;
use App\Http\Controllers\Api\LogroReadController;
use App\Http\Controllers\Api\PlantillaReadController;
use App\Http\Controllers\Api\UserHomeReadController;
use App\Http\Controllers\Api\UserProfileReadController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutes usuari (middleware ensure.user)
|--------------------------------------------------------------------------
*/

Route::middleware('ensure.user')->group(function () {
    Route::get('/habits', [HabitReadController::class, 'index']);
    Route::get('/habits/all', [HabitReadController::class, 'indexAll']);
    Route::get('/habits/{id}', [HabitReadController::class, 'show']);
    Route::get('/habits/progress', [HabitReadController::class, 'progress']);
    Route::get('/habits/logs', [HabitReadController::class, 'logs']);
    Route::post('/habits/complete', [HabitReadController::class, 'complete']);
    Route::get('/plantilles', [PlantillaReadController::class, 'index']);
    Route::get('/plantilles/{id}', [PlantillaReadController::class, 'show']);
    Route::get('/game-state', [GameStateReadController::class, 'show']);
    Route::get('/user/home', [UserHomeReadController::class, 'index']);
    Route::get('/logros', [LogroReadController::class, 'index']);
    Route::get('/user/profile', [UserProfileReadController::class, 'profile']);
});
