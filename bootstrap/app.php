<?php

use App\Http\Middleware\EnsureRole;
use App\Http\Middleware\EnsureSellerApproved;
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
        $middleware->redirectUsersTo(fn () => route('home'));

        // Đăng kí alias (bí danh) cho các middleware
        $middleware->alias([
            'role' => EnsureRole::class,
            'seller.approved' => EnsureSellerApproved::class,
        ]);

        // Chỉ bỏ qua kiểm tra CSRF khi đang làm việc ở môi trường DEV (Local)
        if (env('APP_ENV') === 'local') {
            $middleware->validateCsrfTokens(except: [
                'register',
                'login',
                'seller/*',
            ]);
        }
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn ($request) => $request->is('api/*') || $request->expectsJson()
        );
    })->create();
