<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $employee = Auth::user()->employee;
        abort_unless($employee, 403, 'Employee record not found.');

        return Inertia::render('Employee/Attendance/Index', [
            'filters' => $request->only(['search', 'date', 'status']),
            'dataUrl' => route('employee.attendance.data'),
            'timezone' => config('app.timezone'),
        ]);
    }

    public function data(Request $request)
    {
        $employee = Auth::user()->employee;
        abort_unless($employee, 404, 'Employee record not found.');

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'date' => ['nullable', 'date_format:Y-m-d'],
            'status' => ['nullable', 'in:working,completed,not_started'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $query = Attendance::where('employee_id', $employee->id);

        if ($request->filled('search')) {
            $search = trim($filters['search']);
            foreach (['!F j, Y', '!M j, Y'] as $format) {
                $date = \DateTimeImmutable::createFromFormat($format, $search);
                if ($date && \DateTimeImmutable::getLastErrors() === false) {
                    $search = $date->format('Y-m-d');
                    break;
                }
            }
            $query->where('date', 'like', '%'.$search.'%');
        }

        if ($request->filled('date')) {
            $query->whereDate('date', $filters['date']);
        }

        if (($filters['status'] ?? '') === 'working') {
            $query->whereNotNull('time_in')->whereNull('time_out');
        } elseif (($filters['status'] ?? '') === 'completed') {
            $query->whereNotNull('time_in')->whereNotNull('time_out');
        } elseif (($filters['status'] ?? '') === 'not_started') {
            $query->whereNull('time_in')->whereNull('time_out');
        }

        $attendances = $query->latest('date')->latest('id')->paginate(10);

        return response()->json([
            'success' => true,
            'attendances' => collect($attendances->items())->map(fn (Attendance $attendance) => [
                ...$attendance->toArray(),
                'date' => $attendance->date?->format('Y-m-d'),
            ]),
            'pagination' => [
                'current_page' => $attendances->currentPage(),
                'last_page' => $attendances->lastPage(),
                'per_page' => $attendances->perPage(),
                'total' => $attendances->total(),
            ],
        ]);
    }


    public function timeIn()
    {
        $employee = Auth::user()->employee;

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee record not found.',
            ], 404);
        }

        $attendance = Attendance::firstOrCreate(
            [
                'employee_id' => $employee->id,
                'date' => today(),
            ],
            [
                'time_in' => now(),
            ]
        );

        if ($attendance->time_in) {
            return response()->json([
                'success' => true,
                'message' => 'Time in recorded successfully.',
                'attendance' => $attendance,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Unable to record time in.',
        ], 500);
    }


    public function timeOut()
    {
        $employee = Auth::user()->employee;

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee record not found.',
            ], 404);
        }

        $attendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('date', today())
            ->whereNotNull('time_in')
            ->whereNull('time_out')
            ->first();

        if (!$attendance) {
            return response()->json([
                'success' => false,
                'message' => 'No active attendance record found.',
            ], 404);
        }

        $attendance->update([
            'time_out' => now(),
        ]);

        $attendance->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Time out recorded successfully.',
            'attendance' => $attendance,
        ]);
    }
}
