<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
        ->withMiddleware(function (Middleware $middleware) {
        $middleware->redirectUsersTo(fn (\Illuminate\Http\Request $request) => route($request->user()->dashboardRouteName()));

        $middleware->alias([
            'bs.atomic' => \App\Http\Middleware\AtomicBsRequest::class,
            'event.atomic' => \App\Http\Middleware\AtomicEventRequest::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'company.active' => \App\Http\Middleware\CheckActiveCompany::class,
            'atasan.active'  => \App\Http\Middleware\CheckActiveAtasan::class, // 🎯 BARU
        ]);
        

         $middleware->web(append: [
            \App\Http\Middleware\InjectNotificationBell::class, // 🎯 Lonceng di semua halaman
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
