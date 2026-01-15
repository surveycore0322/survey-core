<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        // 例）APIグループの最低限強化（必要なものだけ）
        $middleware->api(append: [
            // 'throttle:api',  // Rate limit（必要なら有効化）
            // \App\Http\Middleware\ForceJsonResponse::class, // 任意：JSON強制
        ]);

        // 例）独自middleware alias（任意）
        // $middleware->alias([
        //     'force.json' => \App\Http\Middleware\ForceJsonResponse::class,
        // ]);

    })
    ->withExceptions(function (Exceptions $exceptions) {
        // 例）APIで例外レスポンスの統一をしたい場合にここで調整
        // $exceptions->render(function (\Throwable $e, $request) {
        //     if ($request->is('api/*')) { ... }
        // });
    })
    ->create();

