<?php

use App\Http\Controllers\Employee\AttendanceController as EmployeeAttendanceController;
use App\Http\Controllers\Employee\DashboardController as EmployeeDashboardController;
use App\Http\Controllers\Employee\LeaveController as EmployeeLeaveController;
use App\Http\Controllers\Employee\OvertimeController as EmployeeOvertimeController;
use App\Http\Controllers\Employee\ProfileController as EmployeeProfileController;
use Illuminate\Support\Facades\Route;

Route::prefix('employee/profile')
    ->middleware(['role:employee', 'permission:profile.update'])
    ->name('employee.profile.')
    ->group(function () {
        Route::get('/', [EmployeeProfileController::class, 'index'])->name('index');
        Route::put('/', [EmployeeProfileController::class, 'update'])->name('update');
        Route::put('/password', [EmployeeProfileController::class, 'updatePassword'])->name('password.update');
    });

Route::get('/employee/dashboard', [EmployeeDashboardController::class, 'index'])
    ->middleware(['role:employee', 'permission:dashboard.view.employee'])
    ->name('employee.dashboard');

Route::get('/employee/dashboard/data', [EmployeeDashboardController::class, 'data'])
    ->middleware(['role:employee', 'permission:dashboard.view.employee'])
    ->name('employee.dashboard.data');

Route::prefix('employee/attendance')
    ->middleware(['role:employee', 'permission:attendance.manage.own'])
    ->name('employee.attendance.')
    ->group(function () {
        Route::get('/', [EmployeeAttendanceController::class, 'index'])->name('index');
        Route::post('/time-in', [EmployeeAttendanceController::class, 'timeIn'])->name('timeIn');
        Route::post('/time-out', [EmployeeAttendanceController::class, 'timeOut'])->name('timeOut');
    });

Route::prefix('employee/leave')
    ->middleware('role:employee')
    ->name('employee.leave.')
    ->group(function () {
        Route::get('/', [EmployeeLeaveController::class, 'index'])
            ->middleware('permission:leave.view.own')->name('index');
        Route::get('/data', [EmployeeLeaveController::class, 'data'])
            ->middleware('permission:leave.view.own')->name('data');
        Route::post('/', [EmployeeLeaveController::class, 'store'])
            ->middleware('permission:leave.create.own')->name('store');
    });

Route::prefix('employee/overtime')
    ->middleware('role:employee')
    ->name('employee.overtime.')
    ->group(function () {
        Route::get('/', [EmployeeOvertimeController::class, 'index'])
            ->middleware('permission:overtime.view.own')->name('index');
        Route::get('/data', [EmployeeOvertimeController::class, 'data'])
            ->middleware('permission:overtime.view.own')->name('data');
        Route::post('/', [EmployeeOvertimeController::class, 'store'])
            ->middleware('permission:overtime.create.own')->name('store');
    });
