<?php

use App\Http\Controllers\Api\Admin\AdminAuthController;
use App\Http\Controllers\Api\OnboardingQuestionReadController;
use App\Http\Controllers\Api\UserAuthController;
use App\Http\Controllers\PreguntaRegistreReadController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutes públiques (sense autenticació): auth, onboarding, preguntes
|--------------------------------------------------------------------------
*/

Route::post('/auth/login', [UserAuthController::class, 'login']);
Route::post('/admin/auth/login', [AdminAuthController::class, 'login']);
Route::post('/auth/register', [UserAuthController::class, 'register']);
Route::post('/auth/refresh', [UserAuthController::class, 'refresh']);
Route::post('/auth/logout', [UserAuthController::class, 'logout']);
Route::get('/onboarding/questions', [OnboardingQuestionReadController::class, 'questions']);
Route::get('/preguntes-registre/{categoria_id}', [PreguntaRegistreReadController::class, 'index']);
