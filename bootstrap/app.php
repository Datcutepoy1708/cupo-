<?php

use App\Http\Middleware\EnsureRole;
use App\Http\Middleware\EnsureSellerApproved;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectUsersTo(fn () => route('home'));

        // Đăng ký alias (bí danh) cho các middleware
        $middleware->alias([
            'role' => EnsureRole::class,
            'seller.approved' => EnsureSellerApproved::class,
        ]);

        // Bỏ qua kiểm tra CSRF khi đang dev trên môi trường Local để test mượt mà bằng Postman
        if (env('APP_ENV') === 'local') {
            $middleware->validateCsrfTokens(except: [
                'register',
                'login',
                'seller/*',
                'admin/*',
                'profile',
                'cart',
                'cart/*',
                'checkout',
                'customer/*',
                'products/*',
                'api/*',
            ]);
        }
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn ($request) => $request->is('api/*') || $request->expectsJson()
        );
    })->create();
