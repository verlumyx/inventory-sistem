<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Programar verificación de licencias
Schedule::command('license:check-expiration')
    ->daily()
    ->at('09:00')
    ->description('Verificar licencias próximas a vencer');

// También verificar cada 6 horas para casos críticos
Schedule::command('license:check-expiration')
    ->everySixHours()
    ->description('Verificación frecuente de licencias críticas');

// Limpiar logs antiguos mensualmente
Schedule::command('license:cleanup-logs --days=90')
    ->monthly()
    ->description('Limpiar logs de licencia antiguos');
