<?php

use App\Http\Middleware\EnsureUserHasTenant;
use App\Http\Middleware\EnsureUserIsLandlord;
use App\Http\Middleware\EnsureUserCanManageTenant;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'tenant' => EnsureUserHasTenant::class,
            'landlord' => EnsureUserIsLandlord::class,
            'tenant.manage' => EnsureUserCanManageTenant::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
