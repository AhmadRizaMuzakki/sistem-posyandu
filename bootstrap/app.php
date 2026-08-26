<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // --- TAMBAHKAN BAGIAN INI ---
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
        // -----------------------------
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Di production: jangan tampilkan halaman debug Ignition, tampilkan halaman status.
        $exceptions->render(function (Throwable $e, Request $request) {
            if (! app()->isProduction() || $request->expectsJson()) {
                return null;
            }

            if ($e instanceof HttpExceptionInterface) {
                $status = $e->getStatusCode();
                $view = "errors.{$status}";
                if (view()->exists($view)) {
                    return response()->view($view, [
                        'exception' => $e,
                        'message' => $e->getMessage() ?: null,
                    ], $status);
                }
            }

            return response()->view('errors.500', [
                'exception' => $e,
                'message' => 'Sistem sedang mengalami gangguan sementara. Tim kami akan segera memperbaikinya.',
            ], 500);
        });
    })->create();
