<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'midtrans/notification',
        ]);
    })
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('payments:issue-spp-monthly')->monthlyOn(1, '6:00');
        $schedule->command('whatsapp:payment-due-reminder')->dailyAt('08:00');
        $schedule->command('whatsapp:class-reminder')->dailyAt('19:00');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
