<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        channels: __DIR__ . '/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(replace: [
            \Illuminate\Session\Middleware\StartSession::class => \App\Http\Middleware\StartSession::class,
        ]);

        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
            'shift.kasir' => \App\Http\Middleware\PastikanShiftKasirAktif::class,
        ]);

        // Belum login: area /mobile -> login pelanggan, selain itu (panel admin) -> login admin
        $middleware->redirectGuestsTo(fn (Request $request) => $request->is('mobile', 'mobile/*')
            ? route('member.login')
            : route('admin.login'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->report(function (Throwable $e) {
            try {
                activity()
                    ->withProperties([
                        'message' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'url' => request()->fullUrl(),
                        'method' => request()->method(),
                        'user' => auth()->id(),
                    ])
                    ->log('problem pada bagian ' . request()->path());
            } catch (\Throwable $logError) {
                // Jangan sampai kegagalan catat activity_log (mis. database offline)
                // memicu recursive error loop.
            }
        });

        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, \Illuminate\Http\Request $request) {
            return back()
                ->withInput($request->except(['password', 'password_confirmation', 'pin', 'token', '_token']))
                ->with('error', 'Sesi kamu sudah kadaluwarsa. Silakan coba klik/kirim lagi.');
        });
    })->create();