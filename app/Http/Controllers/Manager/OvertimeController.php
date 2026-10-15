<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\OvertimeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OvertimeController extends Controller
{
    public function index()
    {
        return view('manager.overtime.index');
    }

    public function data()
    {
        $manager = Auth::user()->employee;
        abort_unless($manager, 403, 'Employee record not found.');

        $overtimeRequests = OvertimeRequest::with([
            'employee.user',
            'employee.department',
            'approver',
        ])
            ->whereHas('employee', function ($query) use ($manager) {
                $query->where(
                    'manager_id',
                    $manager->id
                );
            })
            ->latest('date')
            ->get();

        $overtimeRequests->each(
            function ($overtime) {

                if ($overtime->date) {
                    $overtime->setAttribute(
                        'date',
                        $overtime->date->format('Y-m-d')
                    );
                }
            }
        );

        return response()->json([
            'success' => true,
            'overtimeRequests' => $overtimeRequests,
        ]);
    }

    public function approve(
        OvertimeRequest $overtimeRequest
    ) {
        $manager = Auth::user()->employee;
        abort_unless($manager, 403, 'Employee record not found.');

        $belongsToManager =
            $overtimeRequest->employee &&
            $overtimeRequest->employee->manager_id ===
            $manager->id;

        if (!$belongsToManager) {

            return response()->json([
                'message' =>
                    'You are not allowed to manage this overtime request.',
            ], 403);
        }

        if (
            $overtimeRequest->status !==
            'Pending'
        ) {

            return response()->json([
                'message' =>
                    'Only pending overtime requests can be approved.',
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

        if ($overtimeRequest->date) {
            $overtimeRequest->setAttribute(
                'date',
                $overtimeRequest->date->format('Y-m-d')
            );
        }

        return response()->json([
            'success' => true,
            'message' =>
                'Overtime request approved successfully.',
            'overtimeRequest' =>
                $overtimeRequest,
        ]);
    }

    public function reject(
        Request $request,
        OvertimeRequest $overtimeRequest
    ) {
        $manager = Auth::user()->employee;
        abort_unless($manager, 403, 'Employee record not found.');

        $belongsToManager =
            $overtimeRequest->employee &&
            $overtimeRequest->employee->manager_id ===
            $manager->id;

        if (!$belongsToManager) {

            return response()->json([
                'message' =>
                    'You are not allowed to manage this overtime request.',
            ], 403);
        }

        if (
            $overtimeRequest->status !==
            'Pending'
        ) {

            return response()->json([
                'message' =>
                    'Only pending overtime requests can be rejected.',
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
            'rejection_reason' =>
                $validated['rejection_reason'],
        ]);

        $overtimeRequest->load([
            'employee.user',
            'employee.department',
            'approver',
        ]);

        if ($overtimeRequest->date) {
            $overtimeRequest->setAttribute(
                'date',
                $overtimeRequest->date->format('Y-m-d')
            );
        }

        return response()->json([
            'success' => true,
            'message' =>
                'Overtime request rejected successfully.',
            'overtimeRequest' =>
                $overtimeRequest,
        ]);
    }
}
