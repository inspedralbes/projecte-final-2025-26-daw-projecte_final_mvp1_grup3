<?php


/**
 * Capa Laravel: calendar.
 * Comentaris: agents/backend/AgentLaravel.md
 */

//================================ NAMESPACES / IMPORTS ============

use App\Http\Controllers\CalendarController;
use Illuminate\Support\Facades\Route;

Route::get('/calendar/snapshot/{usuariId}/{data}', [CalendarController::class, 'showDay']);
Route::get('/calendar/month/{usuariId}/{year}/{month}', [CalendarController::class, 'showMonth']);
