<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (AccessDeniedHttpException $exception, Request $request) {
            $message = $exception->getMessage() ?: 'У вас недостаточно прав для этого действия.';

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => $message], 403);
            }

            return response()->view('errors.authorization-denied', ['message' => $message], 403);
        });
    })->create();
