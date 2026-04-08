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
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\VerificarRol::class,
        ]);

        // Máximo 10 intentos de login por minuto por IP
        $middleware->throttleApi('10,1');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Todas las excepciones retornan JSON (sin HTML)
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            return response()->json(['message' => 'No autenticado.'], 401);
        });

        $exceptions->render(function (\Illuminate\Auth\Access\AuthorizationException $e, $request) {
            return response()->json(['message' => 'No tienes permiso para realizar esta acción.'], 403);
        });

        $exceptions->render(function (\Illuminate\Database\Eloquent\ModelNotFoundException $e, $request) {
            $modelo = class_basename($e->getModel());
            return response()->json(['message' => "{$modelo} no encontrado."], 404);
        });

        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, $request) {
            return response()->json([
                'message' => 'Los datos enviados no son válidos.',
                'errores' => $e->errors(),
            ], 422);
        });
    })->create();
