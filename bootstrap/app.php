<?php

use App\Http\Middleware\EnsureApiJson;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->prependToGroup('api', EnsureApiJson::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->respond(function (Response $response, Throwable $exception, Request $request): Response {
            if (! $request->is('api/*') || ! $response instanceof JsonResponse) {
                return $response;
            }

            $payload = $response->getData(true);

            $response->setData([
                'message' => $response->getStatusCode() >= 500
                    ? 'Terjadi kesalahan pada server.'
                    : ($payload['message'] ?? 'Request gagal.'),
                'data' => null,
                'errors' => $payload['errors'] ?? null,
            ]);

            return $response;
        });
    })->create();
