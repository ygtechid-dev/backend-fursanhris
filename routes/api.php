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
        Route::post('/resign', [Mobile\ResignationController::class, 'store']);
        Route::get('/get-employee-working-period', [Mobile\AttendanceEmployeeController::class, 'getEmployeeWorkingPeriod']);

        Route::get('/profile', [AuthenticationController::class, 'checkAuth']);
        Route::get('/office-assets', [Mobile\AssetController::class, 'index']);

        /** Leave */
        Route::prefix('leaves')->group(function () {
            Route::get('/', [Mobile\LeaveController::class, 'index']);
            Route::post('/submit-request', [Mobile\LeaveController::class, 'store']);
            Route::get('/check-remaining-leave', [Mobile\LeaveController::class, 'checkRemainingLeave']);
        });

        /** Payslip */
        Route::prefix('payslip')->group(function () {
            Route::get('/', [Mobile\PayslipController::class, 'index']);
            Route::get('{id}', [Mobile\PayslipController::class, 'show']);
            Route::get('{id}/export-pdf', [Mobile\PayslipController::class, 'exportPdf']);
        });

        /** Project */
        Route::prefix('projects')->group(function () {
            Route::get('/', [Mobile\ProjectController::class, 'index']);

            Route::get('all-task', [Mobile\TaskController::class, 'getAssignedTasks']);
            Route::prefix('tasks')->group(function () {
                Route::put('{taskId}/update-status', [Mobile\TaskController::class, 'updateTaskStatus']);
                Route::put('create-task', [Mobile\TaskController::class, 'createOwnTask']);
                Route::get('get-employees', [Mobile\TaskController::class, 'getEmployees']);
                Route::post('{taskId}/add-comment', [Mobile\TaskController::class, 'addComment']);
                Route::post('{taskId}/add-attachment', [Mobile\TaskController::class, 'addAttachment']);
                Route::get('{taskId}', [Mobile\TaskController::class, 'getTaskDetail']);
                Route::get('get-employees', [Mobile\TaskController::class, 'getEmployee']);
            });
        });

        /** Events / Calendar */
        Route::prefix('events')->group(function () {
            Route::get('/', [Mobile\EventController::class, 'index']);
        });

        /** Reimbursements */
        Route::prefix('reimbursements')->group(function () {
            Route::get('/', [Mobile\ReimbursementController::class, 'index']);
            Route::post('/', [Mobile\ReimbursementController::class, 'store']);
            Route::get('/categories', [Mobile\ReimbursementController::class, 'getCategories']);
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
        Route::apiResource('users', Controllers\UserController::class);
        Route::apiResource('roles', Controllers\RoleController::class);
        Route::apiResource('attendance-employee', Controllers\AttendanceEmployeeController::class);
        Route::apiResource('rewards', Controllers\RewardController::class);
        Route::apiResource('reward_types', Controllers\RewardTypeController::class);
        Route::apiResource('resignations', Controllers\ResignationController::class);
        Route::apiResource('trips', Controllers\TripController::class);
        Route::apiResource('promotions', Controllers\PromotionController::class);
        Route::apiResource('complaints', Controllers\ComplaintController::class);
        Route::apiResource('warnings', Controllers\WarningController::class);
        Route::apiResource('terminations', Controllers\TerminationController::class);
        Route::apiResource('termination-types', Controllers\TerminationTypeController::class);

        Route::apiResource('employees', Controllers\EmployeeController::class);

        /** Dashboard */
        Route::get('/dashboard/card-stats', [Controllers\DashboardController::class, 'getCardStats']);

        /** User */
        Route::get('/get-users', [Controllers\UserController::class, 'getUser']);

        /** Employee Allowance */
        Route::get('/employees/{id}/allowances', [Controllers\EmployeeAllowanceController::class, 'getAllowances']);
        Route::post('/employees/{id}/allowances', [Controllers\EmployeeAllowanceController::class, 'store']);
        Route::put('/employees/{id}/allowances/{allowanceId}', [Controllers\EmployeeAllowanceController::class, 'update']);
        Route::delete('/employees/{id}/allowances/{allowanceId}', [Controllers\EmployeeAllowanceController::class, 'destroy']);

        /** Employee Deduction */
        Route::get('/employees/{id}/deductions', [Controllers\EmployeeDeductionController::class, 'getDeductions']);
        Route::post('/employees/{id}/deductions', [Controllers\EmployeeDeductionController::class, 'store']);
        Route::put('/employees/{id}/deductions/{deductionId}', [Controllers\EmployeeDeductionController::class, 'update']);
        Route::delete('/employees/{id}/deductions/{deductionId}', [Controllers\EmployeeDeductionController::class, 'destroy']);

        /** Employee Overtime */
        Route::get('/employees/{id}/overtimes', [Controllers\EmployeeOvertimeController::class, 'getOvertimes']);
        Route::post('/employees/{id}/overtimes', [Controllers\EmployeeOvertimeController::class, 'store']);
        Route::put('/employees/{id}/overtimes/{overtimeId}', [Controllers\EmployeeOvertimeController::class, 'update']);
        Route::delete('/employees/{id}/overtimes/{overtimeId}', [Controllers\EmployeeOvertimeController::class, 'destroy']);

        Route::apiResource('salaries', Controllers\EmployeeSalaryController::class);
        /** Payslip */
        Route::prefix('payslips')->group(function () {
            Route::get('/', [Controllers\PayslipController::class, 'index']);
            Route::get('/{id}', [Controllers\PayslipController::class, 'show']);
            Route::post('/update-payment-status/{id}', [Controllers\PayslipController::class, 'updatePaymentStatus']);
            Route::get('{id}/export-pdf', [Controllers\PayslipController::class, 'getPayslipPdf']);
            Route::post('generate', [Controllers\PayslipController::class, 'generatePayslips']);
            Route::delete('/{id}', [Controllers\PayslipController::class, 'destroy']);
        });

        /** Project */
        Route::prefix('project-dashboard')->group(function () {
            Route::get('/show', [App\Http\Controllers\ProjectDashboardController::class, 'showProject']);
            Route::get('/stats', [App\Http\Controllers\ProjectDashboardController::class, 'getStats']);
            Route::get('/project-completion', [App\Http\Controllers\ProjectDashboardController::class, 'getProjectCompletion']);
            Route::get('/recent-activities', [App\Http\Controllers\ProjectDashboardController::class, 'getRecentActivities']);
        });

        Route::prefix('projects')->group(function () {
            Route::get('/', [Controllers\ProjectController::class, 'index']);
            Route::post('/', [Controllers\ProjectController::class, 'store']);
            Route::put('/{id}', [Controllers\ProjectController::class, 'update']);
            Route::delete('/{id}', [Controllers\ProjectController::class, 'destroy']);

            Route::prefix('{id}/tasks')->group(function () {
                Route::get('/', [Controllers\TaskController::class, 'index']);
            });
        });
        Route::prefix('tasks')->group(function () {
            Route::post('/', [Controllers\TaskController::class, 'store']);
            Route::get('/{id}', [Controllers\TaskController::class, 'show']);
            Route::post('/reorder', [Controllers\TaskController::class, 'reorder']);
            Route::post('{task}/status', [Controllers\TaskController::class, 'updateStatus']);
            Route::put('{task}', [Controllers\TaskController::class, 'update']);
            Route::delete('{id}', [Controllers\TaskController::class, 'destroy']);

            // Comment routes
            Route::get('{task}/comments', [Controllers\TaskController::class, 'getComments']);
            Route::post('{task}/comments', [Controllers\TaskController::class, 'addComment']);
            Route::delete('{task}/comments/{comment}', [Controllers\TaskController::class, 'deleteComment']);

            // Attachment routes
            Route::get('{task}/attachments', [Controllers\TaskController::class, 'getAttachments']);
            Route::post('{task}/attachments', [Controllers\TaskController::class, 'uploadAttachment']);
            Route::delete('{task}/attachments/{attachment}', [Controllers\TaskController::class, 'deleteAttachment']);
        });

        Route::get('/companies', [Controllers\UserController::class, 'getCompanies']);

        /** Leave */
        Route::prefix('leaves')->group(function () {
            Route::get('/', [Controllers\LeaveController::class, 'index']);
            Route::post('/', [Controllers\LeaveController::class, 'store']);
            Route::put('/{id}', [Controllers\LeaveController::class, 'update']);
            Route::delete('/{id}', [Controllers\LeaveController::class, 'destroy']);
            Route::post('/update-status/{id}', [Controllers\LeaveController::class, 'updateStatus']);
        });

        /** Overtimes */
        Route::prefix('overtimes')->group(function () {
            Route::get('/', [Controllers\OvertimeController::class, 'index']);
            Route::post('/', [Controllers\OvertimeController::class, 'store']);
            Route::delete('/{id}', [Controllers\OvertimeController::class, 'destroy']);
            Route::post('/update-status/{id}', [Controllers\OvertimeController::class, 'updateStatus']);
        });

        Route::get('company-setting', [Controllers\SettingsController::class, 'fetchCompanySettings']);
        Route::post('company-setting', [Controllers\SettingsController::class, 'saveCompanySettings']);
    });

    // Route umum (bisa diakses keduanya)
    Route::post('/logout', [Controllers\AuthenticationController::class, 'logout'])
        ->name('logout');
});
