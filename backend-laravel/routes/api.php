<?php

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

Route::middleware('auth:api')->group(function () {
    Route::get('/habits', [HabitController::class, 'index']);
    Route::get('/habits/{id}', [HabitController::class, 'show']);
});
