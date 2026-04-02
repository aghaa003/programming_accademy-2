<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/index.html');
});

Route::get('/login', function () {
    return redirect('/login1.html');
});

// Admin SPA — requires active session + admin role
// Move /public/admin.html behind this route once this is confirmed working
Route::get('/admin', function () {
    return response()->file(resource_path('views/admin.html'));
})->middleware(['auth', 'admin']);
