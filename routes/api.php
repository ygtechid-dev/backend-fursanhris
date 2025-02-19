<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers;
use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Mobile;
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


Route::prefix('mobile')->group(function () {
    Route::post('/sign-up', [AuthenticationController::class, 'registerMobile']);
    Route::post('/forgot-password', [AuthenticationController::class, 'forgotPassword']);
    Route::post('/check-reset-token', [AuthenticationController::class, 'checkResetPasswordToken']);
    Route::post('/reset-password', [AuthenticationController::class, 'resetPassword']);
});

// Protected routes with Sanctum
Route::middleware(['auth:sanctum'])->group(function () {
    /** Check Profile */
    Route::get('/me', [AuthenticationController::class, 'checkAuth']);


    /** Master */
    Route::apiResource('branches', Controllers\BranchController::class);
    Route::apiResource('departments', Controllers\DepartmentController::class);
    Route::apiResource('designations', Controllers\DesignationController::class);
    Route::apiResource('leave-types', Controllers\LeaveTypeController::class);

    // Route untuk mobile
    Route::middleware(CheckPlatformAccess::class . ':mobile')->prefix('mobile')->group(function () {
        Route::post('/update-account-profile', [AuthenticationController::class, 'updateAccountProfile']);

        /** Leave */
        Route::prefix('leaves')->group(function () {
            Route::get('/', [Mobile\LeaveController::class, 'index']);
            Route::post('/submit-request', [Mobile\LeaveController::class, 'store']);
            Route::get('/check-remaining-leave', [Mobile\LeaveController::class, 'checkRemainingLeave']);
        });

        /** Overtime */
        Route::prefix('overtimes')->group(function () {
            Route::get('/', [Mobile\OvertimeController::class, 'index']);
            Route::post('/submit-request', [Mobile\OvertimeController::class, 'store']);
        });

        /** Attendance */
        Route::prefix('attendance')->group(function () {
            Route::get('{id}', [Mobile\AttendanceEmployeeController::class, 'getAttendanceDetail']);
            Route::get('{id}/download', [Mobile\AttendanceEmployeeController::class, 'downloadAttendanceDetail']);
            Route::get('/employee/{employee_id}', [Mobile\AttendanceEmployeeController::class, 'getEmployeeHistory']);
            Route::post('/clock-in', [Mobile\AttendanceEmployeeController::class, 'attendance']);
            Route::post('/clock-out', [Mobile\AttendanceEmployeeController::class, 'clockOut']);
        });
    });

    // Route untuk web
    Route::middleware(CheckPlatformAccess::class . ':web')->prefix('web')->group(function () {
        // Route::get('/users',  [Controllers\UserController::class, 'index']);
        Route::apiResource('users', Controllers\UserController::class);
        Route::apiResource('roles', Controllers\RoleController::class);
        Route::apiResource('employees', Controllers\EmployeeController::class);
        Route::apiResource('attendance-employee', Controllers\AttendanceEmployeeController::class);

        Route::get('/companies', [Controllers\UserController::class, 'getCompanies']);

        /** Leave */
        Route::prefix('leaves')->group(function () {
            Route::get('/', [Controllers\LeaveController::class, 'index']);
            Route::post('/update-status/{id}', [Controllers\LeaveController::class, 'updateStatus']);
        });

        /** Overtimes */
        Route::prefix('overtimes')->group(function () {
            Route::get('/', [Controllers\OvertimeController::class, 'index']);
            Route::post('/update-status/{id}', [Controllers\OvertimeController::class, 'updateStatus']);
        });

        Route::get('company-setting', [Controllers\SettingsController::class, 'fetchCompanySettings']);
        Route::post('company-setting', [Controllers\SettingsController::class, 'saveCompanySettings']);
    });

    // Route umum (bisa diakses keduanya)
    Route::post('/logout', [Controllers\AuthenticationController::class, 'logout'])
        ->name('logout');
});
