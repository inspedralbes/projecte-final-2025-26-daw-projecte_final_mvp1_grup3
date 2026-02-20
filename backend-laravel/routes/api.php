<?php

use App\Http\Controllers\HabitController;
use App\Http\Controllers\PreguntaRegistreController;
use Illuminate\Support\Facades\Route;

Route::get('/habits', [HabitController::class, 'index']);

// Preguntes de registre: GET /api/preguntes-registre/{categoria_id}
Route::get('/preguntes-registre/{categoria_id}', [PreguntaRegistreController::class, 'index']);
