<?php

use App\Http\Controllers\HabitController;
use Illuminate\Support\Facades\Route;

Route::get('/habits', [HabitController::class, 'index']);
