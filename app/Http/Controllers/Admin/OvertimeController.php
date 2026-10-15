<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\OvertimeRequest;
use Inertia\Inertia;

class OvertimeController extends Controller
{
    public function index()
    {
        $departments =
            Department::orderBy('name')->get();

        return Inertia::render('Admin/Overtime/Index', [
            'departments' => $departments,
            'overtimeRequests' => OvertimeRequest::with([
                'employee.user', 'employee.department', 'approver',
            ])
                ->latest('date')
                ->get()
                ->map(fn (OvertimeRequest $overtime) => [
                    ...$overtime->toArray(),
                    'date' => $overtime->date?->format('Y-m-d'),
                ]),
        ]);
    }

    public function data()
    {
        $overtimeRequests =
            OvertimeRequest::with([
                'employee.user',
                'employee.department',
                'approver',
            ])
                ->latest('date')
                ->get();

        return response()->json([
            'success' => true,
            'overtimeRequests' =>
                $overtimeRequests,
        ]);
    }
}
