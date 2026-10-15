<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaveController extends Controller
{
    public function index()
    {
        $employee = Auth::user()->employee;
        abort_unless($employee, 403, 'Employee record not found.');

        $leaveRequests = LeaveRequest::where('employee_id', $employee->id)
            ->latest('created_at')
            ->paginate(5);

        $pendingCount = LeaveRequest::where('employee_id', $employee->id)
            ->where('status', 'Pending')
            ->count();

        $approvedCount = LeaveRequest::where('employee_id', $employee->id)
            ->where('status', 'Approved')
            ->count();

        $rejectedCount = LeaveRequest::where('employee_id', $employee->id)
            ->where('status', 'Rejected')
            ->count();

        $totalCount = LeaveRequest::where('employee_id', $employee->id)
            ->count();

        return view('employee.leave.index', compact(
            'employee',
            'leaveRequests',
            'pendingCount',
            'approvedCount',
            'rejectedCount',
            'totalCount'
        ));
    }

    public function data()
    {
        $employee = Auth::user()->employee;
        abort_unless($employee, 403, 'Employee record not found.');

        $leaves = LeaveRequest::where('employee_id', $employee->id)
            ->latest('created_at')
            ->get();

        $pendingCount = LeaveRequest::where('employee_id', $employee->id)
            ->where('status', 'Pending')
            ->count();

        $approvedCount = LeaveRequest::where('employee_id', $employee->id)
            ->where('status', 'Approved')
            ->count();

        $rejectedCount = LeaveRequest::where('employee_id', $employee->id)
            ->where('status', 'Rejected')
            ->count();

        $totalCount = LeaveRequest::where('employee_id', $employee->id)
            ->count();

        return response()->json([
            'success' => true,
            'leaves' => $leaves,
            'pendingCount' => $pendingCount,
            'approvedCount' => $approvedCount,
            'rejectedCount' => $rejectedCount,
            'totalCount' => $totalCount,
        ]);
    }

    public function store(Request $request)
    {
        $employee = Auth::user()->employee;
        abort_unless($employee, 403, 'Employee record not found.');

        $validated = $request->validate([
            'leave_type' => 'required|in:Sick,Vacation,Emergency,Bereavement',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string|max:500',
        ]);

        $leave = LeaveRequest::create([
            'employee_id' => $employee->id,
            'leave_type' => $validated['leave_type'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'reason' => $validated['reason'] ?? null,
            'status' => 'Pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Leave request submitted successfully.',
            'data' => $leave,
        ]);
    }
}
