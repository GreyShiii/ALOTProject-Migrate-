<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\LeaveRequest;
use Inertia\Inertia;

class LeaveController extends Controller
{
    public function index()
    {
        $leaveRequests = LeaveRequest::with([
            'employee.user',
            'employee.department',
            'approver',
        ])
            ->latest('created_at')
            ->get()
            ->map(fn (LeaveRequest $leave) => [
                ...$leave->toArray(),
                'start_date' => $leave->start_date?->format('Y-m-d'),
                'end_date' => $leave->end_date?->format('Y-m-d'),
            ]);

        $departments =
            Department::orderBy('name')->get();

        return Inertia::render(
            'Admin/Leave/Index',
            compact(
                'leaveRequests',
                'departments'
            )
        );
    }

    public function data()
    {
        $leaveRequests = LeaveRequest::with([
            'employee.user',
            'employee.department',
            'approver',
        ])
            ->latest('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'leaveRequests' => $leaveRequests,
        ]);
    }
}
