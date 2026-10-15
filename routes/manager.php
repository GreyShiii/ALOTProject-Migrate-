<?php

use App\Http\Controllers\Manager\AttendanceController as ManagerAttendanceController;
use App\Http\Controllers\Manager\DashboardController as ManagerDashboardController;
use App\Http\Controllers\Manager\LeaveController as ManagerLeaveController;
use App\Http\Controllers\Manager\OvertimeController as ManagerOvertimeController;
use App\Http\Controllers\Manager\TeamController;
use App\Http\Controllers\Manager\ProfileController as ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/manager/dashboard', [ManagerDashboardController::class, 'index'])
    ->middleware(['role:manager', 'permission:dashboard.view.manager'])
    ->name('manager.dashboard');

Route::prefix('manager/leave')
    ->middleware(['role:manager', 'permission:leave.review.team'])
    ->name('manager.leave.')
    ->group(function () {
        Route::get('/', [ManagerLeaveController::class, 'index'])->name('index');
        Route::get('/data', [ManagerLeaveController::class, 'data'])->name('data');
        Route::post('/{leaveRequest}/approve', [ManagerLeaveController::class, 'approve'])->name('approve');
        Route::post('/{leaveRequest}/reject', [ManagerLeaveController::class, 'reject'])->name('reject');
    });

Route::prefix('manager/overtime')
    ->middleware(['role:manager', 'permission:overtime.review.team'])
    ->name('manager.overtime.')
    ->group(function () {
        Route::get('/', [ManagerOvertimeController::class, 'index'])->name('index');
        Route::get('/data', [ManagerOvertimeController::class, 'data'])->name('data');
        Route::post('/{overtimeRequest}/approve', [ManagerOvertimeController::class, 'approve'])->name('approve');
        Route::post('/{overtimeRequest}/reject', [ManagerOvertimeController::class, 'reject'])->name('reject');
    });

Route::get('/manager/team', [TeamController::class, 'index'])
    ->middleware(['role:manager', 'permission:team.view'])
    ->name('manager.team.index');

Route::prefix('manager/attendance')
    ->middleware(['role:manager', 'permission:attendance.manage.team'])
    ->name('manager.attendance.')
    ->group(function () {
        Route::get('/', [ManagerAttendanceController::class, 'index'])->name('index');
        Route::get('/data', [ManagerAttendanceController::class, 'data'])->name('data');
        Route::post('/time-in', [ManagerAttendanceController::class, 'timeIn'])->name('timeIn');
        Route::post('/time-out', [ManagerAttendanceController::class, 'timeOut'])->name('timeOut');
    });

Route::prefix('manager/profile')
    ->middleware(['role:manager', 'permission:profile.update'])
    ->name('manager.profile.')
    ->group(function () {
        Route::get('/', [ProfileController::class, 'index'])->name('index');
        Route::put('/', [ProfileController::class, 'update'])->name('update');
        Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password.update');
    });
