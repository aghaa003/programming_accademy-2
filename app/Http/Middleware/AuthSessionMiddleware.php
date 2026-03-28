<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Symfony\Component\HttpFoundation\Response;

class AuthSessionMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->session()->has('user_id')) {
            // Try to restore session from remember-me cookie
            $cookie = $request->cookie('remember_user');
            if ($cookie) {
                try {
                    $userId = (int) Crypt::decryptString($cookie);
                    $user   = User::find($userId);
                    if ($user) {
                        $roles = $user->roles()->pluck('name')->toArray();
                        $request->session()->put('user_id', $userId);
                        $request->session()->put('roles', $roles);
                    }
                } catch (DecryptException $e) {
                    // Tampered or invalid cookie — just reject
                }
            }

            if (!$request->session()->has('user_id')) {
                return response()->json(['success' => false, 'message' => 'Not authenticated'], 401);
            }
        }

        return $next($request);
    }
}
