<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\OvertimeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class OvertimeController extends Controller
{
    /**
     * Display manager overtime requests page.
     */
    public function index()
    {
        $manager = Auth::user()->employee;
        abort_unless($manager, 403, 'Employee record not found.');

        return Inertia::render('Manager/Overtime/Index', [
            'overtimeRequests' => $this->teamRequests($manager),
        ]);
    }

    /**
     * Return overtime requests belonging to the manager's team.
     */
    public function data()
    {
        $manager = Auth::user()->employee;
        abort_unless($manager, 404, 'Employee record not found.');

        return response()->json([
            'success' => true,
            'overtimeRequests' => $this->teamRequests($manager),
        ]);
    }

    private function teamRequests(Employee $manager): Collection
    {
        return OvertimeRequest::with([
            'employee.user',
            'employee.department',
            'approver',
        ])
            ->whereHas('employee', function ($query) use ($manager) {
                $query->where('manager_id', $manager->id);
            })
            ->latest('date')
            ->get()
            ->map(fn (OvertimeRequest $overtime) => $this->overtimePayload($overtime));
    }

    private function overtimePayload(OvertimeRequest $overtime): array
    {
        return [
            ...$overtime->toArray(),
            'date' => $overtime->date?->format('Y-m-d'),
            'approveUrl' => route('manager.overtime.approve', $overtime),
            'rejectUrl' => route('manager.overtime.reject', $overtime),
        ];
    }

    /**
     * Approve an overtime request.
     */
    public function approve(OvertimeRequest $overtimeRequest)
    {
        $manager = Auth::user()->employee;
        abort_unless($manager, 403, 'Employee record not found.');

        $belongsToManager =
            $overtimeRequest->employee &&
            $overtimeRequest->employee->manager_id === $manager->id;

        if (!$belongsToManager) {
            return response()->json([
                'message' => 'You are not allowed to manage this overtime request.',
            ], 403);
        }

        if ($overtimeRequest->status !== 'Pending') {
            return response()->json([
                'message' => 'Only pending overtime requests can be approved.',
            ], 422);
        }

        $overtimeRequest->update([
            'status' => 'Approved',
            'approved_by' => Auth::id(),
            'approval_date' => now(),
            'rejection_reason' => null,
        ]);

        $overtimeRequest->load([
            'employee.user',
            'employee.department',
            'approver',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Overtime request approved successfully.',
            'overtimeRequest' => $this->overtimePayload($overtimeRequest),
        ]);
    }

    /**
     * Reject an overtime request.
     */
    public function reject(
        Request $request,
        OvertimeRequest $overtimeRequest
    ) {
        $manager = Auth::user()->employee;
        abort_unless($manager, 403, 'Employee record not found.');

        $belongsToManager =
            $overtimeRequest->employee &&
            $overtimeRequest->employee->manager_id === $manager->id;

        if (!$belongsToManager) {
            return response()->json([
                'message' => 'You are not allowed to manage this overtime request.',
            ], 403);
        }

        if ($overtimeRequest->status !== 'Pending') {
            return response()->json([
                'message' => 'Only pending overtime requests can be rejected.',
            ], 422);
        }

        $validated = $request->validate([
            'rejection_reason' => [
                'required',
                'string',
                'max:500',
            ],
        ]);

        $overtimeRequest->update([
            'status' => 'Rejected',
            'approved_by' => Auth::id(),
            'approval_date' => now(),
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        $overtimeRequest->load([
            'employee.user',
            'employee.department',
            'approver',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Overtime request rejected successfully.',
            'overtimeRequest' => $this->overtimePayload($overtimeRequest),
        ]);
    }
}
