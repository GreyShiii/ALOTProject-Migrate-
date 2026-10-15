<?php

use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\LeaveController as AdminLeaveController;
use App\Http\Controllers\Admin\OvertimeController as AdminOvertimeController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('role:admin')
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])
            ->middleware('permission:dashboard.view.admin')->name('dashboard');

        Route::middleware('permission:employees.manage')->group(function () {
            Route::resource('employees', EmployeeController::class)
                ->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);
        });

        Route::middleware('permission:users.manage')->group(function () {
            Route::resource('users', UserController::class)->only(['index', 'update']);
            Route::post('/users/{user}/deactivate', [UserController::class, 'deactivate'])->name('users.deactivate');
            Route::post('/users/{user}/activate', [UserController::class, 'activate'])->name('users.activate');
        });

        Route::middleware('permission:departments.manage')->group(function () {
            Route::resource('departments', DepartmentController::class)
                ->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);
        });

        Route::get('/attendance', [AdminAttendanceController::class, 'index'])
            ->middleware('permission:attendance.view.all')->name('attendance.index');
        Route::get('/attendance/data', [AdminAttendanceController::class, 'data'])
            ->middleware('permission:attendance.view.all')->name('attendance.data');

        Route::get('/leave', [AdminLeaveController::class, 'index'])
            ->middleware('permission:leave.view.all')->name('leave.index');
        Route::get('/leave/data', [AdminLeaveController::class, 'data'])
            ->middleware('permission:leave.view.all')->name('leave.data');

        Route::get('/overtime', [AdminOvertimeController::class, 'index'])
            ->middleware('permission:overtime.view.all')->name('overtime.index');
        Route::get('/overtime/data', [AdminOvertimeController::class, 'data'])
            ->middleware('permission:overtime.view.all')->name('overtime.data');

        Route::prefix('profile')
            ->middleware('permission:profile.update')
            ->name('profile.')
            ->group(function () {
                Route::get('/', [AdminProfileController::class, 'index'])->name('index');
                Route::put('/', [AdminProfileController::class, 'update'])->name('update');
                Route::put('/password', [AdminProfileController::class, 'updatePassword'])->name('password.update');
            });
    });
