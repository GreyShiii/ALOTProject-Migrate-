<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaveController extends Controller
{
    /**
     * Display manager leave requests page.
     */
    public function index()
    {
        return view('manager.leave.index');
    }

    /**
     * Return leave requests belonging to the manager's team.
     */
    public function data()
    {
        $manager = Auth::user()->employee;
        abort_unless($manager, 403, 'Employee record not found.');

        $leaveRequests = LeaveRequest::with([
            'employee.user',
            'employee.department',
            'approver',
        ])
            ->whereHas('employee', function ($query) use ($manager) {
                $query->where('manager_id', $manager->id);
            })
            ->latest('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'leaveRequests' => $leaveRequests,
        ]);
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
            'leaveRequest' => $leaveRequest,
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
            'leaveRequest' => $leaveRequest,
        ]);
    }
}
