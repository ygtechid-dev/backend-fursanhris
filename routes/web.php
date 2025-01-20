<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers;
use App\Http\Middleware\checkAuth;

// Route::get('/', function () {
//     return view('pages.dashboard');
// });

// Route::get('/login', [Controllers\AuthenticationController::class, 'loginView'])
//     ->name('login.view')
//     ->middleware(checkAuth::class);

// Route::post('/login', [Controllers\AuthenticationController::class, 'login'])
//     ->name('login');

// Route::post('/logout', [Controllers\AuthenticationController::class, 'logout'])
//     ->name('logout');

// Route::group(['middleware' => 'auth'], function () {
//     Route::get('/', [Controllers\DashboardController::class, 'index'])
//         ->name('dashboard.index');
// });
