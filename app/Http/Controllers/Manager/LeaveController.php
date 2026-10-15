<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class LeaveController extends Controller
{
    /**
     * Display manager leave requests page.
     */
    public function index()
    {
        $manager = Auth::user()->employee;
        abort_unless($manager, 403, 'Employee record not found.');

        return Inertia::render('Manager/Leave/Index', [
            'leaveRequests' => $this->teamRequests($manager),
        ]);
    }

    /**
     * Return leave requests belonging to the manager's team.
     */
    public function data()
    {
        $manager = Auth::user()->employee;
        abort_unless($manager, 404, 'Employee record not found.');

        return response()->json([
            'success' => true,
            'leaveRequests' => $this->teamRequests($manager),
        ]);
    }

    private function teamRequests(Employee $manager): Collection
    {
        return LeaveRequest::with([
            'employee.user',
            'employee.department',
            'approver',
        ])
            ->whereHas('employee', function ($query) use ($manager) {
                $query->where('manager_id', $manager->id);
            })
            ->latest('created_at')
            ->get()
            ->map(fn (LeaveRequest $leave) => $this->leavePayload($leave));
    }

    private function leavePayload(LeaveRequest $leave): array
    {
        return [
            ...$leave->toArray(),
            'start_date' => $leave->start_date?->format('Y-m-d'),
            'end_date' => $leave->end_date?->format('Y-m-d'),
            'approveUrl' => route('manager.leave.approve', $leave),
            'rejectUrl' => route('manager.leave.reject', $leave),
        ];
    }

    /**
     * Approve a leave request.
     */
    public function approve(LeaveRequest $leaveRequest)
    {
        $manager = Auth::user()->employee;
        abort_unless($manager, 403, 'Employee record not found.');

        $belongsToManager =
            $leaveRequest->employee &&
            $leaveRequest->employee->manager_id === $manager->id;

        if (!$belongsToManager) {
            return response()->json([
                'message' => 'You are not allowed to manage this leave request.',
            ], 403);
        }

        if ($leaveRequest->status !== 'Pending') {
            return response()->json([
                'message' => 'Only pending leave requests can be approved.',
            ], 422);
        }

        $leaveRequest->update([
            'status' => 'Approved',
            'approved_by' => Auth::id(),
            'approval_date' => now(),
            'rejection_reason' => null,
        ]);

        $leaveRequest->load([
            'employee.user',
            'employee.department',
            'approver',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Leave request approved successfully.',
            'leaveRequest' => $this->leavePayload($leaveRequest),
        ]);
    }

    /**
     * Reject a leave request.
     */
    public function reject(
        Request $request,
        LeaveRequest $leaveRequest
    ) {
        $manager = Auth::user()->employee;
        abort_unless($manager, 403, 'Employee record not found.');

        $belongsToManager =
            $leaveRequest->employee &&
            $leaveRequest->employee->manager_id === $manager->id;

        if (!$belongsToManager) {
            return response()->json([
                'message' => 'You are not allowed to manage this leave request.',
            ], 403);
        }

        if ($leaveRequest->status !== 'Pending') {
            return response()->json([
                'message' => 'Only pending leave requests can be rejected.',
            ], 422);
        }

        $validated = $request->validate([
            'rejection_reason' => [
                'required',
                'string',
                'max:500',
            ],
        ]);

        $leaveRequest->update([
            'status' => 'Rejected',
            'approved_by' => Auth::id(),
            'approval_date' => now(),
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        $leaveRequest->load([
            'employee.user',
            'employee.department',
            'approver',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Leave request rejected successfully.',
            'leaveRequest' => $this->leavePayload($leaveRequest),
        ]);
    }
}
