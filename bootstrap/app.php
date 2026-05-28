<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\SecurityHeadersMiddleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Exclude public endpoints from CSRF verification
        // Login and register don't require CSRF because:
        // 1. They're public endpoints (not authenticated)
        // 2. Security comes from credential validation, not CSRF
        // 3. First-time SPA requests don't have session initialized yet
        $middleware->validateCsrfTokens(except: [
            'api/auth/login',
            'api/auth/register',
            'api/login',
            'api/register',
            'api/check-availability',
            'api/forgot-password',
            'api/reset-password',
            'auth/*/callback',
        ]);

        $middleware->alias([
            'admin' => AdminMiddleware::class,
        ]);

        // Ensure session + Sanctum SPA auth are available for API routes.
        // EnsureFrontendRequestsAreStateful internally runs EncryptCookies → StartSession
        // → VerifyCsrfToken in the correct order for stateful (SPA) requests.
        // Do NOT prepend a standalone StartSession here — it would run before
        // EncryptCookies, reading the encrypted cookie as a raw session ID and
        // opening an empty session, breaking auth for all API requests.
        $middleware->api(prepend: [
            EnsureFrontendRequestsAreStateful::class,
        ]);

        // Return 401 JSON instead of redirecting to a "login" route for API requests
        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                abort(401, 'Unauthenticated.');
            }

            return '/login1.html';
        });

        // Apply security response headers to all requests
        $middleware->append(SecurityHeadersMiddleware::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Return JSON for API error responses instead of HTML pages
        $exceptions->renderable(function (AuthenticationException $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
        });

        $exceptions->renderable(function (ValidationException $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(['message' => $e->getMessage(), 'errors' => $e->errors()], 422);
            }
        });

        $exceptions->renderable(function (NotFoundHttpException $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(['message' => 'Not found.'], 404);
            }
        });

        $exceptions->renderable(function (MethodNotAllowedHttpException $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(['message' => 'Method not allowed.'], 405);
            }
        });

        $exceptions->renderable(function (Throwable $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
                $message = config('app.debug') ? $e->getMessage() : 'Server error.';

                return response()->json(['message' => $message], $status);
            }
        });
    })->create();
