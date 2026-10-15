<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('auth')->group(function () {
    require __DIR__.'/employee.php';
    require __DIR__.'/manager.php';
    require __DIR__.'/admin.php';
});
