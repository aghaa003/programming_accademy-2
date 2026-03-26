<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthSessionMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->session()->has('user_id')) {
            return response()->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }

        return $next($request);
    }
}
