<?php

use App\Http\Middleware\EnsureBaseExternaPermission;
use App\Http\Middleware\EnsureCoordenacaoPermission;
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
            'base-externa.permission' => EnsureBaseExternaPermission::class,
            'coordenacao.permission' => EnsureCoordenacaoPermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
