<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $manager = Auth::user()->employee;
        abort_unless($manager, 403, 'Employee record not found.');

        $teamEmployeeIds = $manager->subordinates()
            ->pluck('id');

        $pendingLeaveCount = LeaveRequest::whereIn(
            'employee_id',
            $teamEmployeeIds
        )
            ->where('status', 'Pending')
            ->count();

        $pendingOvertimeCount = OvertimeRequest::whereIn(
            'employee_id',
            $teamEmployeeIds
        )
            ->where('status', 'Pending')
            ->count();

        $teamMemberCount = $teamEmployeeIds->count();

        $approvedLeaveCount = LeaveRequest::whereIn(
            'employee_id',
            $teamEmployeeIds
        )
            ->where('status', 'Approved')
            ->count();

        $approvedOvertimeCount = OvertimeRequest::whereIn(
            'employee_id',
            $teamEmployeeIds
        )
            ->where('status', 'Approved')
            ->count();

        $approvedRequestCount =
            $approvedLeaveCount +
            $approvedOvertimeCount;

        $pendingLeaveRequests = LeaveRequest::with([
            'employee.user',
            'employee.department',
        ])
            ->whereIn('employee_id', $teamEmployeeIds)
            ->where('status', 'Pending')
            ->latest('created_at')
            ->get();

        $pendingOvertimeRequests = OvertimeRequest::with([
            'employee.user',
            'employee.department',
        ])
            ->whereIn('employee_id', $teamEmployeeIds)
            ->where('status', 'Pending')
            ->latest('date')
            ->get();

        $pendingRequests = $pendingLeaveRequests->map(fn ($request) => [
            'id' => 'leave-'.$request->id,
            'type' => 'Leave',
            'employee' => $request->employee,
            'details' => $request->leave_type,
            'date' => $request->start_date?->format('Y-m-d'),
            'submitted' => $request->created_at,
            'route' => route('manager.leave.index'),
        ])->concat($pendingOvertimeRequests->map(fn ($request) => [
            'id' => 'overtime-'.$request->id,
            'type' => 'Overtime',
            'employee' => $request->employee,
            'details' => $request->hours.' hours',
            'date' => $request->date?->format('Y-m-d'),
            'submitted' => $request->created_at,
            'route' => route('manager.overtime.index'),
        ]))->sortByDesc('submitted')->take(5)->values();

        return Inertia::render('Manager/Dashboard', [
            ...compact(
                'pendingLeaveCount',
                'pendingOvertimeCount',
                'teamMemberCount',
                'approvedRequestCount'
            ),
            'pendingRequests' => $pendingRequests,
            'links' => [
                'leave' => route('manager.leave.index'),
                'overtime' => route('manager.overtime.index'),
                'team' => route('manager.team.index'),
            ],
        ]);
    }
}
