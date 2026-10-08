<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use App\Http\Middleware\EnsureSuperadmin;
use App\Http\Middleware\EnsureWedding;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        // Cookie ini ditulis oleh app.js sebelum browser refresh. Cookie tidak
        // berisi data sensitif, hanya path menu aktif yang tetap divalidasi di
        // DashboardController sebelum dipakai sebagai redirect.
        $middleware->encryptCookies(except: ['weddingPlannerLastRoute']);
        $middleware->alias([
            'superadmin' => EnsureSuperadmin::class,
            'wedding' => EnsureWedding::class,
        ]);
        $middleware->appendToGroup('web', EnsureWedding::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
