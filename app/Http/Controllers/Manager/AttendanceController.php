<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        return view(
            'manager.attendance.index',
            compact(
                'employee',
                'todayAttendance'
            )
        );
    }

    public function data(Request $request)
    {
        $manager = Auth::user()->employee;
        abort_unless($manager, 403, 'Employee record not found.');

        $filters = $request->validate([
            'scope' => ['nullable', 'in:team,mine'],
            'search' => ['nullable', 'string', 'max:100'],
            'date' => ['nullable', 'date_format:Y-m-d'],
            'status' => ['nullable', 'in:completed,working,not_started'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ]);

        $scope = $filters['scope'] ?? 'team';

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

        if (!empty($filters['search'])) {

            $search = $filters['search'];

            $query->whereHas(
                'employee.user',
                function ($userQuery) use ($search) {

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
                }
            );
        }

        if (!empty($filters['date'])) {

            $query->whereDate(
                'date',
                $filters['date']
            );
        }

        if (!empty($filters['status'])) {

            $status = $filters['status'];

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
            $attendances->items(),

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

    public function timeIn()
    {
        $employee =
            Auth::user()->employee;

        abort_unless($employee, 403, 'Employee record not found.');

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

        abort_unless($employee, 403, 'Employee record not found.');

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
