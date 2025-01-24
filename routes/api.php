<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers;
use App\Http\Controllers\AuthenticationController;
use App\Http\Middleware\CheckPlatformAccess;
use Illuminate\Cache\Events\RetrievingKey;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::get('/login', function (Request $request) {
//     return response()->json(['message' => 'welcome to hris']);
// });
Route::post('/login', [Controllers\AuthenticationController::class, 'login'])
    ->name('login');

Route::post('/mobile/sign-up', [AuthenticationController::class, 'registerMobile']);

// Protected routes with Sanctum
Route::middleware(['auth:sanctum'])->group(function () {
    // Route untuk mobile
    Route::middleware(CheckPlatformAccess::class . ':mobile')->prefix('mobile')->group(function () {
        Route::get('/mobile-test', function () {
            return response()->json([
                'status' => true,
                'message' => 'Auth Check'
            ], 200);
        });

        Route::post('/update-account-profile', [AuthenticationController::class, 'updateAccountProfile']);
        Route::post('/forgot-password', [AuthenticationController::class, 'forgotPassword']);
        Route::post('/check-reset-token', [AuthenticationController::class, 'checkResetPasswordToken']);
        Route::post('/reset-password', [AuthenticationController::class, 'resetPassword']);
    });

    // Route untuk web
    Route::middleware(CheckPlatformAccess::class . ':web')->prefix('web')->group(function () {
        // Route::get('/users',  [Controllers\UserController::class, 'index']);
        Route::apiResource('users', Controllers\UserController::class);
        Route::apiResource('employees', Controllers\EmployeeController::class);
    });

    // Route umum (bisa diakses keduanya)
    Route::post('/logout', [Controllers\AuthenticationController::class, 'logout'])
        ->name('logout');
});
