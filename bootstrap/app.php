<?php

use App\Http\Middleware\EnsureUserHasTenant;
use App\Http\Middleware\EnsureUserIsLandlord;
use App\Http\Middleware\EnsureUserCanManageTenant;
use App\Http\Middleware\EnsureTenantIsActive;
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
        // Forzar Content-Type: text/html; charset=UTF-8 en todas las respuestas HTML
        $middleware->append(function ($request, $next) {
            $response = $next($request);
            if (method_exists($response, 'header') && str_contains($response->headers->get('Content-Type', ''), 'text/html')) {
                $response->header('Content-Type', 'text/html; charset=UTF-8');
            }
            return $response;
        });

        $middleware->alias([
            'tenant' => EnsureUserHasTenant::class,
            'landlord' => EnsureUserIsLandlord::class,
            'tenant.manage' => EnsureUserCanManageTenant::class,
            'tenant.active' => EnsureTenantIsActive::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
