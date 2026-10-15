<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\LeaveController as AdminLeaveController;
use App\Http\Controllers\Admin\OvertimeController as AdminOvertimeController;


use App\Http\Controllers\Employee\AttendanceController as EmployeeAttendanceController;
use App\Http\Controllers\Employee\DashboardController as EmployeeDashboardController;
use App\Http\Controllers\Employee\LeaveController as EmployeeLeaveController;
use App\Http\Controllers\Employee\OvertimeController;
use App\Http\Controllers\Employee\ProfileController;

use App\Http\Controllers\Manager\LeaveController as ManagerLeaveController;
use App\Http\Controllers\Manager\OvertimeController as ManagerOvertimeController;
use App\Http\Controllers\Manager\TeamController;
use App\Http\Controllers\Manager\DashboardController as ManagerDashboardController;
use App\Http\Controllers\Manager\AttendanceController as ManagerAttendanceController;

use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});


/*
|--------------------------------------------------------------------------
| Guest Routes
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Logout
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | Employee Profile
    |--------------------------------------------------------------------------
    */

    Route::prefix('employee/profile')
        ->middleware(['role:employee', 'permission:profile.update'])
        ->name('employee.profile.')
        ->group(function () {

            Route::get('/', [ProfileController::class, 'index'])
                ->name('index');

            Route::put('/', [ProfileController::class, 'update'])
                ->name('update');

            Route::put('/password', [ProfileController::class, 'updatePassword'])
                ->name('password.update');

        });


    /*
    |--------------------------------------------------------------------------
    | Employee Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get('/employee/dashboard', [EmployeeDashboardController::class, 'index'])
        ->middleware(['role:employee', 'permission:dashboard.view.employee'])
        ->name('employee.dashboard');

    Route::get('/employee/dashboard/data', [EmployeeDashboardController::class, 'data'])
        ->middleware(['role:employee', 'permission:dashboard.view.employee'])
        ->name('employee.dashboard.data');


    /*
    |--------------------------------------------------------------------------
    | Employee Attendance
    |--------------------------------------------------------------------------
    */

    Route::prefix('employee/attendance')
        ->middleware(['role:employee', 'permission:attendance.manage.own'])
        ->name('employee.attendance.')
        ->group(function () {

            Route::get('/', [EmployeeAttendanceController::class, 'index'])
                ->name('index');

            Route::post('/time-in', [EmployeeAttendanceController::class, 'timeIn'])
                ->name('timeIn');

            Route::post('/time-out', [EmployeeAttendanceController::class, 'timeOut'])
                ->name('timeOut');

        });


    /*
    |--------------------------------------------------------------------------
    | Employee Leave
    |--------------------------------------------------------------------------
    */

    Route::prefix('employee/leave')
        ->middleware('role:employee')
        ->name('employee.leave.')
        ->group(function () {

            Route::get('/', [EmployeeLeaveController::class, 'index'])
                ->middleware('permission:leave.view.own')
                ->name('index');

            Route::get('/data', [EmployeeLeaveController::class, 'data'])
                ->middleware('permission:leave.view.own')
                ->name('data');

            Route::post('/', [EmployeeLeaveController::class, 'store'])
                ->middleware('permission:leave.create.own')
                ->name('store');

        });


    /*
    |--------------------------------------------------------------------------
    | Employee Overtime
    |--------------------------------------------------------------------------
    */

Route::prefix('employee/overtime')
    ->middleware('role:employee')
    ->name('employee.overtime.')
    ->group(function () {

        Route::get('/', [OvertimeController::class, 'index'])
            ->middleware('permission:overtime.view.own')
            ->name('index');

        Route::get('/data', [OvertimeController::class, 'data'])
            ->middleware('permission:overtime.view.own')
            ->name('data');

        Route::post('/', [OvertimeController::class, 'store'])
            ->middleware('permission:overtime.create.own')
            ->name('store');

    });


    /*
    |--------------------------------------------------------------------------
    | Manager Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get('/manager/dashboard', [ManagerDashboardController::class, 'index'])
        ->middleware(['role:manager', 'permission:dashboard.view.manager'])
        ->name('manager.dashboard');


    /*
    |--------------------------------------------------------------------------
    | Manager Leave Requests
    |--------------------------------------------------------------------------
    */

    Route::prefix('manager/leave')
        ->middleware(['role:manager', 'permission:leave.review.team'])
        ->name('manager.leave.')
        ->group(function () {

            Route::get('/', [ManagerLeaveController::class, 'index'])
                ->name('index');

            Route::get('/data', [ManagerLeaveController::class, 'data'])
                ->name('data');

            Route::post('/{leaveRequest}/approve', [ManagerLeaveController::class, 'approve'])
                ->name('approve');

            Route::post('/{leaveRequest}/reject', [ManagerLeaveController::class, 'reject'])
                ->name('reject');

        });


    /*
    |--------------------------------------------------------------------------
    | Manager Overtime Requests
    |--------------------------------------------------------------------------
    */

    Route::prefix('manager/overtime')
        ->middleware(['role:manager', 'permission:overtime.review.team'])
        ->name('manager.overtime.')
        ->group(function () {

            Route::get('/', [ManagerOvertimeController::class, 'index'])
                ->name('index');

            Route::get('/data', [ManagerOvertimeController::class, 'data'])
                ->name('data');

            Route::post('/{overtimeRequest}/approve', [ManagerOvertimeController::class, 'approve'])
                ->name('approve');

            Route::post('/{overtimeRequest}/reject', [ManagerOvertimeController::class, 'reject'])
                ->name('reject');

        });


    /*
    |--------------------------------------------------------------------------
    | Manager Team
    |--------------------------------------------------------------------------
    */

    Route::get('/manager/team', [TeamController::class, 'index'])
        ->middleware(['role:manager', 'permission:team.view'])
        ->name('manager.team.index');


    Route::prefix('manager/attendance')
    ->middleware(['role:manager', 'permission:attendance.manage.team'])
    ->name('manager.attendance.')
    ->group(function () {

        Route::get('/', [ManagerAttendanceController::class, 'index'])
            ->name('index');

        Route::get('/data', [ManagerAttendanceController::class, 'data'])
            ->name('data');

        Route::post('/time-in', [ManagerAttendanceController::class, 'timeIn'])
            ->name('timeIn');

        Route::post('/time-out', [ManagerAttendanceController::class, 'timeOut'])
            ->name('timeOut');

    });

    /*
    |--------------------------------------------------------------------------
    | Manager Profile
    |--------------------------------------------------------------------------
    |
    | Uses the same ProfileController as Employee Profile.
    | The Blade decides which route prefix to use.
    |
    */

    Route::prefix('manager/profile')
        ->middleware(['role:manager', 'permission:profile.update'])
        ->name('manager.profile.')
        ->group(function () {

            Route::get('/', [ProfileController::class, 'index'])
                ->name('index');

            Route::put('/', [ProfileController::class, 'update'])
                ->name('update');

            Route::put('/password', [ProfileController::class, 'updatePassword'])
                ->name('password.update');

        });


    /*
    |--------------------------------------------------------------------------
    | Admin Routes
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:admin')
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {

            // Dashboard
            Route::get('/dashboard', [AdminDashboardController::class, 'index'])
                ->middleware('permission:dashboard.view.admin')
                ->name('dashboard');


            // Employees
            Route::middleware('permission:employees.manage')->group(function () {
                Route::resource('employees', EmployeeController::class)
                    ->only([
                        'index',
                        'store',
                        'show',
                        'update',
                        'destroy',
                    ]);
            });


            // Users
            Route::middleware('permission:users.manage')->group(function () {
                Route::resource('users', UserController::class)
                    ->only([
                        'index',
                        'update',
                    ]);

                Route::post('/users/{user}/deactivate', [UserController::class, 'deactivate'])
                    ->name('users.deactivate');

                Route::post('/users/{user}/activate', [UserController::class, 'activate'])
                    ->name('users.activate');
            });


            // Departments
            Route::middleware('permission:departments.manage')->group(function () {
                Route::resource('departments', DepartmentController::class)
                    ->only([
                        'index',
                        'store',
                        'update',
                        'destroy',
                    ]);
            });


            // Attendance
            Route::get('/attendance', [AdminAttendanceController::class, 'index'])
                ->middleware('permission:attendance.view.all')
                ->name('attendance.index');

            Route::get('/attendance/data', [AdminAttendanceController::class, 'data'])
                ->middleware('permission:attendance.view.all')
                ->name('attendance.data');


            // Leave
            Route::get('/leave', [AdminLeaveController::class, 'index'])
                ->middleware('permission:leave.view.all')
                ->name('leave.index');

            Route::get('/leave/data', [AdminLeaveController::class, 'data'])
                ->middleware('permission:leave.view.all')
                ->name('leave.data');


            // Overtime
            Route::get('/overtime', [AdminOvertimeController::class, 'index'])
                ->middleware('permission:overtime.view.all')
                ->name('overtime.index');

            Route::get('/overtime/data', [AdminOvertimeController::class, 'data'])
                ->middleware('permission:overtime.view.all')
                ->name('overtime.data');


            // =================================================
            // ADMIN PROFILE
            // =================================================

            Route::prefix('profile')
                ->middleware('permission:profile.update')
                ->name('profile.')
                ->group(function () {

                    Route::get('/', [ProfileController::class, 'index'])
                        ->name('index');

                    Route::put('/', [ProfileController::class, 'update'])
                        ->name('update');

                    Route::put('/password', [ProfileController::class, 'updatePassword'])
                        ->name('password.update');

                });

        });

});
