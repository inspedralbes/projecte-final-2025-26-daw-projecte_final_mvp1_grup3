<?php

use App\Http\Controllers\Api\GameStateController;
use App\Http\Controllers\Api\HabitController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Lectura d'hàbits
|--------------------------------------------------------------------------
|
| Rutes de consulta (GET) per al frontend. Les creacions i actualitzacions
| es gestionen de forma asíncrona via Redis.
|
*/

/*
|--------------------------------------------------------------------------
| Usuari per defecte: id 1 (administrador). Sense autenticació.
|--------------------------------------------------------------------------
*/

Route::get('/habits', [HabitController::class, 'index']);
Route::get('/habits/{id}', [HabitController::class, 'show']);
Route::get('/game-state', [GameStateController::class, 'show']);
