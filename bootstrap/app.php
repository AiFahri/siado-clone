<?php

use App\Http\Middleware\EnsureUserEnrolledInCourse;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Foundation\Application;
use App\Http\Middleware\JWTAuthentication;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'jwt.auth' => JWTAuthentication::class,
            'enrolled' => EnsureUserEnrolledInCourse::class,
            'teachesCourse' => \App\Http\Middleware\EnsureUserEnrolledInCourse::class,
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Throwable $e, Request $request) {
            if (!$request->is('api/*')) {
                return null;
            }

            $previous = $e->getPrevious();

            if ($e instanceof UnauthorizedHttpException) {
                if ($previous instanceof TokenExpiredException) {
                    return response()->json(['error' => 'token has expired'], 401);
                }

                if ($previous instanceof TokenInvalidException) {
                    return response()->json(['error' => 'token is invalid'], 401);
                }
            }

            if ($e instanceof NotFoundHttpException) {
                if ($previous instanceof ModelNotFoundException) {
                    return response()->json(['error' => 'resource not found'], 404);
                }

                return response()->json(['error' => 'route not found'], 404);
            }

            return null;
        });
    })->create();
