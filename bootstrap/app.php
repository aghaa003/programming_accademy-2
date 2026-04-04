<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Exclude OAuth callback URLs from CSRF verification
        $middleware->validateCsrfTokens(except: [
            'auth/*/callback',
        ]);

        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
        ]);

        // Ensure session + Sanctum SPA auth are available for API routes.
        // EnsureFrontendRequestsAreStateful internally runs EncryptCookies → StartSession
        // → VerifyCsrfToken in the correct order for stateful (SPA) requests.
        // Do NOT prepend a standalone StartSession here — it would run before
        // EncryptCookies, reading the encrypted cookie as a raw session ID and
        // opening an empty session, breaking auth for all API requests.
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);

        // Return 401 JSON instead of redirecting to a "login" route for API requests
        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                abort(401, 'Unauthenticated.');
            }
            return '/login1.html';
        });

        // Apply security response headers to all requests
        $middleware->append(\App\Http\Middleware\SecurityHeadersMiddleware::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Return JSON for API error responses instead of HTML pages
        $exceptions->renderable(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
        });

        $exceptions->renderable(function (\Illuminate\Validation\ValidationException $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(['message' => $e->getMessage(), 'errors' => $e->errors()], 422);
            }
        });

        $exceptions->renderable(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(['message' => 'Not found.'], 404);
            }
        });

        $exceptions->renderable(function (\Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(['message' => 'Method not allowed.'], 405);
            }
        });

        $exceptions->renderable(function (\Throwable $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
                $message = config('app.debug') ? $e->getMessage() : 'Server error.';
                return response()->json(['message' => $message], $status);
            }
        });
    })->create();

