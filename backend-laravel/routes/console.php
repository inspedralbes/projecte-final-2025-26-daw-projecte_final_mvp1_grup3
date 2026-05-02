<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Redis worker unificat: php artisan redis:unified-worker (o via Docker backend-laravel-redis-worker)

// Reset diari de ratxes (Europe/Madrid) a les 00:00
Schedule::command('ratxes:reset-diary')
    ->dailyAt('00:00')
    ->timezone('Europe/Madrid');

// XP proporcional diari a les 00:05 (Europe/Madrid)
Schedule::command('habits:partial-xp')
    ->dailyAt('00:05')
    ->timezone('Europe/Madrid');
