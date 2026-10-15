<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use App\Models\User;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $today = today();

        $totalEmployees = User::role('employee')->count();

        $totalManagers = User::role('manager')->count();

        $totalDepartments = Department::count();

        // Attendance Overview
        $workingEmployees = Attendance::whereDate('date', $today)
            ->whereNotNull('time_in')
            ->whereNull('time_out')
            ->count();

        $completedEmployees = Attendance::whereDate('date', $today)
            ->whereNotNull('time_in')
            ->whereNotNull('time_out')
            ->count();

        $employeesOnLeave = LeaveRequest::where('status', 'Approved')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->distinct('employee_id')
            ->count('employee_id');

        // Recent Employees
        $recentEmployees = Employee::with([
            'user',
            'department',
        ])
            ->latest()
            ->take(5)
            ->get()
            ->map(fn (Employee $employee) => [
                ...$employee->toArray(),
                'hire_date' => $employee->hire_date?->format('Y-m-d'),
            ]);

        // Recent Leave Requests
        $recentLeaveRequests = LeaveRequest::with([
            'employee.user',
            'employee.department',
        ])
            ->latest('created_at')
            ->take(5)
            ->get();

        // Recent Overtime Requests
        $recentOvertimeRequests = OvertimeRequest::with([
            'employee.user',
            'employee.department',
        ])
            ->latest('created_at')
            ->take(5)
            ->get();

        // Combine leave and overtime requests
        $recentRequests = $recentLeaveRequests
            ->map(function ($request) {
                return [
                    'type' => 'Leave',
                    'employee' => $request->employee,
                    'date' => $request->start_date?->format('Y-m-d'),
                    'status' => $request->status,
                    'created_at' => $request->created_at,
                ];
            })
            ->merge(
                $recentOvertimeRequests->map(function ($request) {
                    return [
                        'type' => 'Overtime',
                        'employee' => $request->employee,
                        'date' => $request->date?->format('Y-m-d'),
                        'status' => $request->status,
                        'created_at' => $request->created_at,
                    ];
                })
            )
            ->sortByDesc('created_at')
            ->take(5)
            ->values();

        return Inertia::render('Admin/Dashboard', compact(
            'totalEmployees',
            'totalManagers',
            'totalDepartments',
            'workingEmployees',
            'completedEmployees',
            'employeesOnLeave',
            'recentEmployees',
            'recentRequests'
        ));
    }
}
