<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class LeaveController extends Controller
{
    public function index()
    {
        $employee = Auth::user()->employee;
        abort_unless($employee, 403, 'Employee record not found.');

        return Inertia::render('Employee/Leave/Index', [
            ...$this->ownRequests($employee),
            'storeUrl' => route('employee.leave.store'),
            'canCreate' => Auth::user()->can('leave.create.own'),
        ]);
    }

    public function data()
    {
        $employee = Auth::user()->employee;
        abort_unless($employee, 404, 'Employee record not found.');

        return response()->json([
            'success' => true,
            ...$this->ownRequests($employee),
        ]);
    }

    private function ownRequests(Employee $employee): array
    {
        $leaves = LeaveRequest::with('approver:id,first_name,last_name')
            ->where('employee_id', $employee->id)
            ->latest('created_at')->latest('id')->get();

        return [
            'leaves' => $leaves->map(fn (LeaveRequest $leave) => $this->leavePayload($leave)),
            'pendingCount' => $leaves->where('status', 'Pending')->count(),
            'approvedCount' => $leaves->where('status', 'Approved')->count(),
            'rejectedCount' => $leaves->where('status', 'Rejected')->count(),
            'totalCount' => $leaves->count(),
        ];
    }

    private function leavePayload(LeaveRequest $leave): array
    {
        return [
            ...$leave->toArray(),
            'start_date' => $leave->start_date?->format('Y-m-d'),
            'end_date' => $leave->end_date?->format('Y-m-d'),
        ];
    }

    public function store(Request $request)
    {
        $employee = Auth::user()->employee;
        abort_unless($employee, 404, 'Employee record not found.');

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
            'data' => $this->leavePayload($leave),
        ]);
    }
}
