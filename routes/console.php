<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Marca automáticamente "No asistió" a citas que llevan +15 min sin check-in
Schedule::command('citas:marcar-no-asistio')->everyFiveMinutes();
