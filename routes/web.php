<?php

use App\Http\Controllers\SocialAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/index.html');
});

Route::get('/login', function () {
    return redirect('/login1.html');
});

// Social OAuth login (Google, GitHub, LinkedIn)
Route::get('/auth/{provider}/redirect', [SocialAuthController::class, 'redirect'])
    ->where('provider', 'google|github|linkedin-openid');
Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'callback'])
    ->where('provider', 'google|github|linkedin-openid');

// Admin SPA — requires active session + admin role
// Move /public/admin.html behind this route once this is confirmed working
Route::get('/admin', function () {
    return response()->file(resource_path('views/admin.html'));
})->middleware(['auth', 'admin']);
