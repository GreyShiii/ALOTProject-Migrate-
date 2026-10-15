<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\OvertimeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class OvertimeController extends Controller
{
    public function index()
    {
        $employee = Auth::user()->employee;
        abort_unless($employee, 404, 'Employee record not found.');

        return Inertia::render('Employee/Overtime/Index', [
            ...$this->ownRequests($employee),
            'storeUrl' => route('employee.overtime.store'),
            'canCreate' => Auth::user()->can('overtime.create.own'),
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
        $overtimeRequests = OvertimeRequest::with('approver:id,first_name,last_name')
            ->where('employee_id', $employee->id)
            ->latest('date')->latest('id')->get();

        return [
            'overtimeRequests' => $overtimeRequests->map(fn (OvertimeRequest $overtime) => $this->overtimePayload($overtime)),
            'pendingCount' => $overtimeRequests->where('status', 'Pending')->count(),
            'approvedCount' => $overtimeRequests->where('status', 'Approved')->count(),
            'rejectedCount' => $overtimeRequests->where('status', 'Rejected')->count(),
            'totalCount' => $overtimeRequests->count(),
            'approvedHours' => number_format($overtimeRequests->where('status', 'Approved')->sum('hours'), 2, '.', ''),
        ];
    }

    private function overtimePayload(OvertimeRequest $overtime): array
    {
        return [
            ...$overtime->toArray(),
            'date' => $overtime->date?->format('Y-m-d'),
        ];
    }

    public function store(Request $request)
    {
        $employee = Auth::user()->employee;
        abort_unless($employee, 404, 'Employee record not found.');

        $validated = $request->validate([
            'date' => 'required|date',
            'hours' => 'required|numeric|min:0.5|max:6',
            'reason' => 'nullable|string|max:500',
        ]);

        $overtime = OvertimeRequest::create([
            'employee_id' => $employee->id,
            'date' => $validated['date'],
            'hours' => $validated['hours'],
            'reason' => $validated['reason'] ?? null,
            'status' => 'Pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Overtime request submitted successfully.',
            'data' => $this->overtimePayload($overtime),
        ]);
    }
}
