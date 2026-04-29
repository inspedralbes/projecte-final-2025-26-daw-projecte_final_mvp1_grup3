<?php

use App\Http\Controllers\Api\GameStateReadController;
use App\Http\Controllers\Api\HabitReadController;
use App\Http\Controllers\Api\ExternalResourceController;
use App\Http\Controllers\Api\LogroReadController;
use App\Http\Controllers\Api\OnboardingHabitAssignController;
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
});
