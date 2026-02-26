<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Admin\AdminConfiguracioController;
use App\Http\Controllers\Api\Admin\AdminDashboardController;
use App\Http\Controllers\Api\Admin\AdminHabitController;
use App\Http\Controllers\Api\Admin\AdminLogController;
use App\Http\Controllers\Api\Admin\AdminLogroController;
use App\Http\Controllers\Api\Admin\AdminMissioController;
use App\Http\Controllers\Api\Admin\AdminNotificacioController;
use App\Http\Controllers\Api\Admin\AdminPerfilController;
use App\Http\Controllers\Api\Admin\AdminPlantillaController;
use App\Http\Controllers\Api\Admin\AdminRankingController;
use App\Http\Controllers\Api\Admin\AdminReportController;
use App\Http\Controllers\Api\Admin\AdminUsuariController;
use App\Http\Controllers\Api\GameStateController;
use App\Http\Controllers\Api\HabitController;
use App\Http\Controllers\Api\LogroController;
use App\Http\Controllers\Api\UserController;
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

Route::post('/register', [AuthController::class, 'register']);
Route::get('/habits', [HabitController::class, 'index']);
Route::get('/habits/{id}', [HabitController::class, 'show']);
Route::get('/game-state', [GameStateController::class, 'show']);
Route::get('/logros', [LogroController::class, 'index']);
Route::get('/user/profile', [UserController::class, 'profile']);
Route::get('/preguntes-registre/{categoria_id}', [\App\Http\Controllers\PreguntaRegistreController::class, 'index']);
Route::get('/onboarding/questions', [\App\Http\Controllers\Api\OnboardingController::class, 'questions']);

/*
|--------------------------------------------------------------------------
| API Admin - Panel d'administració (MVP1: admin_id 1 per defecte)
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index']);
    Route::get('/notificacions/{page}/{per_page}/{llegida}', [AdminNotificacioController::class, 'index']);
    Route::patch('/notificacions/{id}', [AdminNotificacioController::class, 'marcarLlegida']);
    Route::get('/logs/{page}/{per_page}/{data_desde}/{data_fins}/{administrador_id}/{accio}/{cerca}', [AdminLogController::class, 'index']);
    Route::get('/rankings/{periodo}', [AdminRankingController::class, 'index']);
    Route::get('/usuaris/{tipus}/{page}/{per_page}/{prohibit}/{cerca}', [AdminUsuariController::class, 'index']);
    Route::patch('/usuaris/{id}/prohibir', [AdminUsuariController::class, 'prohibir']);
    Route::get('/plantilles/{page}/{per_page}', [AdminPlantillaController::class, 'index']);
    Route::get('/habits/{page}/{per_page}', [AdminHabitController::class, 'index']);
    Route::get('/logros/{page}/{per_page}', [AdminLogroController::class, 'index']);
    Route::get('/missions/{page}/{per_page}', [AdminMissioController::class, 'index']);
    Route::get('/reports/{page}/{per_page}', [AdminReportController::class, 'index']);
    Route::get('/perfil', [AdminPerfilController::class, 'show']);
    Route::get('/profile', [AdminPerfilController::class, 'show']); // Àlies per al frontend
    Route::put('/perfil', [AdminPerfilController::class, 'update']);
    Route::patch('/perfil/password', [AdminPerfilController::class, 'canviarPassword']);
    Route::get('/configuracio', [AdminConfiguracioController::class, 'show']);
    Route::put('/configuracio', [AdminConfiguracioController::class, 'update']);
});
