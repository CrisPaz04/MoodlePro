<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Programar el comando de notificaciones
Schedule::command('notifications:send-scheduled')
    ->dailyAt('09:00')
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/notifications.log'));