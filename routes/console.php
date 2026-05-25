<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Cierre automático de cajas: se ejecuta cada día a las 00:01 hora Lima (Perú, UTC-5)
Schedule::command('caja:cerrar-automatico')
    ->dailyAt('00:01')
    ->timezone('America/Lima')
    ->withoutOverlapping()
    ->runInBackground();
