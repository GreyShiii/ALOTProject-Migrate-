<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AttendanceController extends Controller
{
    public function index()
    {
        $employee = Auth::user()->employee;
        abort_unless($employee, 403, 'Employee record not found.');

        $todayAttendance = Attendance::where(
            'employee_id',
            $employee->id
        )
            ->whereDate('date', today())
            ->first();

        return Inertia::render('Manager/Attendance/Index', [
            'todayAttendance' => $todayAttendance ? $this->attendancePayload($todayAttendance) : null,
            'todayLabel' => today()->format('F j, Y'),
            'timezone' => config('app.timezone'),
            'links' => [
                'data' => route('manager.attendance.data'),
                'timeIn' => route('manager.attendance.timeIn'),
                'timeOut' => route('manager.attendance.timeOut'),
            ],
        ]);
    }

    public function data(Request $request)
    {
        $manager = Auth::user()->employee;
        abort_unless($manager, 404, 'Employee record not found.');

        $scope = $request->input(
            'scope',
            'team'
        );

        $query = Attendance::with([
            'employee.user',
            'employee.department',
        ]);

        if ($scope === 'mine') {

            $query->where(
                'employee_id',
                $manager->id
            );
        } else {

            $teamEmployeeIds =
                $manager->subordinates()
                ->pluck('id');

            $query->whereIn(
                'employee_id',
                $teamEmployeeIds
            );
        }

        if ($request->filled('search')) {

            $search =
                $request->input('search');

            if ($scope === 'mine') {
                $query->where('date', 'like', '%'.$search.'%');
            } else {
                $query->whereHas('employee.user', function ($userQuery) use ($search) {
                    $userQuery
                        ->where(
                            'first_name',
                            'LIKE',
                            '%' . $search . '%'
                        )
                        ->orWhere(
                            'last_name',
                            'LIKE',
                            '%' . $search . '%'
                        )
                        ->orWhere(
                            'email',
                            'LIKE',
                            '%' . $search . '%'
                        );
                });
            }
        }

        if ($request->filled('date')) {

            $query->whereDate(
                'date',
                $request->input('date')
            );
        }

        if ($request->filled('status')) {

            $status =
                $request->input('status');

            if ($status === 'completed') {

                $query
                    ->whereNotNull('time_in')
                    ->whereNotNull('time_out');
            } elseif ($status === 'working') {

                $query
                    ->whereNotNull('time_in')
                    ->whereNull('time_out');
            } elseif ($status === 'not_started') {

                $query
                    ->whereNull('time_in')
                    ->whereNull('time_out');
            }
        }

        $attendances =
            $query
            ->latest('date')
            ->paginate(10);

        return response()->json([
            'success' => true,

            'attendances' =>
            collect($attendances->items())->map(fn (Attendance $attendance) => $this->attendancePayload($attendance)),

            'pagination' => [
                'current_page' =>
                $attendances->currentPage(),

                'last_page' =>
                $attendances->lastPage(),

                'per_page' =>
                $attendances->perPage(),

                'total' =>
                $attendances->total(),
            ],
        ]);
    }

    private function attendancePayload(Attendance $attendance): array
    {
        return [
            ...$attendance->toArray(),
            'date' => $attendance->date?->format('Y-m-d'),
        ];
    }

    public function timeIn()
    {
        $employee =
            Auth::user()->employee;

        if (!$employee) {

            return response()->json([
                'success' => false,
                'message' =>
                'Employee record not found.',
            ], 404);
        }

        $lastAttendance =
            Attendance::where(
                'employee_id',
                $employee->id
            )
            ->whereNotNull(
                'time_in'
            )
            ->latest('time_in')
            ->first();

        if ($lastAttendance) {

            $waitSeconds =
                24 * 60 * 60;

            $nextTimeIn =
                $lastAttendance->time_in
                ->copy()
                ->addSeconds(
                    $waitSeconds
                );

            if (
                now()->lessThan(
                    $nextTimeIn
                )
            ) {

                $remainingSeconds =
                    now()->diffInSeconds(
                        $nextTimeIn
                    );

                $minutes =
                    floor(
                        $remainingSeconds /
                            60
                    );

                $seconds =
                    $remainingSeconds %
                    60;

                return response()->json([
                    'success' => false,
                    'message' =>
                    "Please wait {$minutes}m {$seconds}s before timing in again.",
                ], 422);
            }
        }

        $attendance =
            Attendance::firstOrCreate(
                [
                    'employee_id' =>
                    $employee->id,

                    'date' =>
                    today(),
                ],
                [
                    'time_in' =>
                    now(),
                ]
            );

        if (
            !$attendance->time_in
        ) {

            $attendance->update([
                'time_in' =>
                now(),
            ]);

            $attendance->refresh();
        }

        return response()->json([
            'success' => true,

            'message' =>
            'Time in recorded successfully.',

            'attendance' =>
            $attendance,
        ]);
    }

    public function timeOut()
    {
        $employee =
            Auth::user()->employee;

        if (!$employee) {

            return response()->json([
                'success' => false,
                'message' =>
                'Employee record not found.',
            ], 404);
        }

        $attendance =
            Attendance::where(
                'employee_id',
                $employee->id
            )
            ->whereDate(
                'date',
                today()
            )
            ->whereNotNull(
                'time_in'
            )
            ->whereNull(
                'time_out'
            )
            ->first();

        if (!$attendance) {

            return response()->json([
                'success' => false,
                'message' =>
                'No active attendance record found.',
            ], 404);
        }

        $attendance->update([
            'time_out' =>
            now(),
        ]);

        $attendance->refresh();

        return response()->json([
            'success' => true,

            'message' =>
            'Time out recorded successfully.',

            'attendance' =>
            $attendance,
        ]);
    }
}
