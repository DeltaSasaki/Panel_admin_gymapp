<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Sincronización automática de tasa de cambio BCV (Apertura bancaria 6:00 AM y Cierre 5:00 PM)
Schedule::command('currency:sync-rates')->dailyAt('06:00')->timezone('America/Caracas');
Schedule::command('currency:sync-rates')->dailyAt('17:00')->timezone('America/Caracas');
