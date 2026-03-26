<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $userId = $request->session()->get('user_id');
        $roles  = $request->session()->get('roles', []);

        if (!$userId || !in_array('admin', $roles)) {
            return response()->json(['success' => false, 'message' => 'غير مصرح لك بالوصول إلى هذه الصفحة'], 403);
        }

        return $next($request);
    }
}
