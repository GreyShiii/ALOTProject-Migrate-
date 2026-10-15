<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Department;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AttendanceController extends Controller
{
    public function index()
    {
        $departments = Department::orderBy('name')->get();

        return Inertia::render('Admin/Attendance/Index', [
            'departments' => $departments,
            'timezone' => config('app.timezone'),
            'attendances' => Attendance::with(['employee.user', 'employee.department'])
                ->latest('date')
                ->get()
                ->map(fn (Attendance $attendance) => [
                    ...$attendance->toArray(),
                    'date' => $attendance->date?->format('Y-m-d'),
                ]),
        ]);
    }

    public function data(Request $request)
    {
        $attendances = Attendance::with([
            'employee.user',
            'employee.department',
        ])
            ->latest('date')
            ->get();

        return response()->json([
            'success' => true,
            'attendances' => $attendances,
        ]);
    }
}
