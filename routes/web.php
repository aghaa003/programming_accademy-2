<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/index.html');
});

Route::get('/login', function () {
    return redirect('/login1.html');
});
