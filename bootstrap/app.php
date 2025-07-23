<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'webLang' => \App\Http\Middleware\WebLang::class,
            'lang' => \App\Http\Middleware\Lang::class,
            'CheckDeliveryman' => \App\Http\Middleware\CheckDeliveryman::class,
        ]);
    })

    ->withExceptions(function (Exceptions $exceptions) {
        // $exceptions->render(function (NotFoundHttpException $e, Request $request) {
        //     if ($request->is('api/*')) {
        //         return Response::api(__('message.Not Found'), 404, false, 404);
        //     }
        // });

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return Response::api(__('message.Unauthorized'), 401, false, 401);
            }
        });

        // $exceptions->render(function (Throwable $e, Request $request) {
        //     if ($request->is('api/*')) {
        //         return Response::api(__('message.Internal Server Error'), 500, false, 500);
        //     }
        // });
    })->create();
