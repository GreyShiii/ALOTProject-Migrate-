@extends('layouts.app')

@section('title', 'Dashboard')
@section('breadcrumb-parent', 'Employee')
@section('breadcrumb-current', 'Dashboard')

@section('content')

    @php
        $attendanceStatus = 'not_started';

        if ($todayAttendance?->time_in && !$todayAttendance?->time_out) {
            $attendanceStatus = 'working';
        } elseif ($todayAttendance?->time_in && $todayAttendance?->time_out) {
            $attendanceStatus = 'completed';
        }
    @endphp

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="flex items-center justify-between bg-gradient-to-r from-[#11458c] to-teal-600 px-6 py-5">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-blue-100">
                    Today's Attendance
                </p>
                <p class="mt-1 text-2xl font-bold text-white">
                    {{ now()->format('F j, Y') }}
                </p>
            </div>
            <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-white/15 text-white">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="9" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 2" />
                </svg>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2 lg:grid-cols-5 lg:items-center">
            <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Status</p>
                <span id="attendance-status"
                    class="mt-2 inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold @if ($attendanceStatus === 'not_started') bg-gray-100 text-gray-600 @elseif ($attendanceStatus === 'working') bg-cyan-50 text-cyan-700 @else bg-green-50 text-green-700 @endif">
                    <span id="attendance-status-dot"
                        class="h-1.5 w-1.5 rounded-full @if ($attendanceStatus === 'not_started') bg-gray-400 @elseif ($attendanceStatus === 'working') bg-cyan-500 @else bg-green-500 @endif"></span>
                    <span id="attendance-status-text">
                        @if ($attendanceStatus === 'working')
                            Working
                        @else
                            Completed
                        @endif
                    </span>
                </span>
            </div>

            <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Time In</p>
                <p id="attendance-time-in" class="mt-2 text-lg font-semibold text-gray-900">
                    {{ $todayAttendance?->time_in?->format('h:i A') ?? '—' }}
                </p>
            </div>

            <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Time Out</p>
                <p id="attendance-time-out" class="mt-2 text-lg font-semibold text-gray-900">
                    {{ $todayAttendance?->time_out?->format('h:i A') ?? '—' }}
                </p>
            </div>

            <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Total Hours</p>
                <p id="attendance-total-hours" class="mt-2 text-lg font-semibold text-gray-900">
                    @if ($todayAttendance?->time_in && $todayAttendance?->time_out)
                        @php
                            $totalMinutes = (int) abs(
                                $todayAttendance->time_in->diffInMinutes($todayAttendance->time_out),
                            );
                            $hours = intdiv($totalMinutes, 60);
                            $minutes = $totalMinutes % 60;
                        @endphp
                        {{ $hours }}h {{ str_pad($minutes, 2, '0', STR_PAD_LEFT) }}m
                    @else
                        —
                    @endif
                </p>
            </div>

            <div class="flex justify-center lg:justify-end">
                @if ($attendanceStatus === 'not_started')
                    <form method="POST" action="{{ route('employee.attendance.timeIn') }}" id="time-in-form"
                        class="w-full lg:w-auto">
                        @csrf
                        <button type="submit"
                            class="flex w-full items-center justify-center gap-2 rounded-lg bg-[#11458c] px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-700 lg:w-auto">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4M10 17l5-5-5-5M15 12H3" />
                            </svg>
                            Time In
                        </button>
                    </form>
                @elseif ($attendanceStatus === 'working')
                    <form method="POST" action="{{ route('employee.attendance.timeOut') }}" id="time-out-form"
                        class="w-full lg:w-auto">
                        @csrf
                        <button type="submit"
                            class="flex w-full items-center justify-center gap-2 rounded-lg bg-red-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-red-700 lg:w-auto">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4M10 7l-5 5 5 5M5 12h10" />
                            </svg>
                            Time Out
                        </button>
                    </form>
                @else
                    <div id="attendance-complete-message" class="flex items-center justify-center px-2 text-center">
                        <p class="text-sm text-gray-500">Your attendance for today is complete.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">

        {{-- Pending Leave --}}
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex items-start justify-between">
                <p class="text-sm font-medium text-gray-500">Pending Leave</p>

                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-amber-50 text-amber-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <rect x="3" y="5" width="18" height="16" rx="2" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 9h18M8 3v4M16 3v4" />
                    </svg>
                </div>
            </div>

            <p class="mt-3 text-3xl font-bold text-gray-900">
                {{ $pendingLeaveCount }}
            </p>

            <p class="mt-2 text-xs text-gray-500">
                Awaiting manager review
            </p>
        </div>


        {{-- Pending Overtime --}}
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex items-start justify-between">
                <p class="text-sm font-medium text-gray-500">Pending Overtime</p>

                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-amber-50 text-amber-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <circle cx="12" cy="13" r="7" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v3M9 2h6" />
                    </svg>
                </div>
            </div>

            <p class="mt-3 text-3xl font-bold text-gray-900">
                {{ $pendingOvertimeCount }}
            </p>

            <p class="mt-2 text-xs text-gray-500">
                Awaiting manager review
            </p>
        </div>


        {{-- Approved Leave --}}
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex items-start justify-between">
                <p class="text-sm font-medium text-gray-500">Approved Leave</p>

                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-green-50 text-green-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12l4 4L19 6" />
                    </svg>
                </div>
            </div>

            <p class="mt-3 text-3xl font-bold text-gray-900">
                {{ $approvedLeaveCount }}
            </p>

            <p class="mt-2 text-xs text-gray-500">
                This year
            </p>
        </div>


        {{-- Approved Overtime --}}
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex items-start justify-between">
                <p class="text-sm font-medium text-gray-500">Approved Overtime</p>

                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-green-50 text-green-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <circle cx="12" cy="13" r="7" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v3M9 2h6" />
                    </svg>
                </div>
            </div>

            <p class="mt-3 text-3xl font-bold text-gray-900">
                {{ $approvedOvertimeCount }}
            </p>

            <p class="mt-2 text-xs text-gray-500">
                This year
            </p>
        </div>

    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 mt-7">

        <a href="{{ route('employee.leave.index') }}"
            class="group rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-blue-300 hover:shadow-md">
            <div class="flex items-start justify-between">
                <div>
                    <h3 class="font-semibold text-gray-900">
                        Request Leave
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Submit and manage your leave requests.
                    </p>
                </div>

                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                    →
                </span>
            </div>
        </a>

        <a href="{{ route('employee.overtime.index') }}"
            class="group rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-green-300 hover:shadow-md">
            <div class="flex items-start justify-between">
                <div>
                    <h3 class="font-semibold text-gray-900">
                        Request Overtime
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Submit and manage your overtime requests.
                    </p>
                </div>

                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-50 text-green-600">
                    →
                </span>
            </div>
        </a>

    </div>

    <div class="mt-6 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Recent Requests</h2>
                <p class="mt-1 text-sm text-gray-500">Your five most recent leave and overtime submissions.</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th
                            class="whitespace-nowrap px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Type</th>
                        <th
                            class="whitespace-nowrap px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Date / Range</th>
                        <th
                            class="whitespace-nowrap px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Reason</th>
                        <th
                            class="whitespace-nowrap px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Submitted</th>
                        <th
                            class="whitespace-nowrap px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Status</th>
                        <th
                            class="whitespace-nowrap px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">

                    @forelse ($recentRequests as $request)
                        <tr>
                            <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-gray-900 text-center">
                                {{ $request['type'] }}
                            </td>

                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-700 text-center">
                                {{ $request['date'] }}
                            </td>

                            <td class="px-4 py-3 text-sm text-gray-700 text-center">
                                {{ $request['reason'] ?? '—' }}
                            </td>

                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500 text-center">
                                {{ $request['submitted'] }}
                            </td>

                            <td class="whitespace-nowrap px-4 py-3 text-center">

                                @if ($request['status'] === 'Pending')
                                    <span
                                        class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">
                                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                        Pending
                                    </span>
                                @elseif ($request['status'] === 'Approved')
                                    <span
                                        class="inline-flex items-center gap-1.5 rounded-full bg-green-50 px-2.5 py-1 text-xs font-semibold text-green-700">
                                        <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>
                                        Approved
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1.5 rounded-full bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-700">
                                        <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                        {{ $request['status'] }}
                                    </span>
                                @endif

                            </td>

                            <td class="whitespace-nowrap px-4 py-3 text-center">
                                <a href="{{ $request['type'] === 'Overtime' ? route('employee.overtime.index') : route('employee.leave.index') }}"
                                    class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-700 transition hover:bg-gray-50">
                                    View
                                </a>
                            </td>
                        </tr>

                    @empty

                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-sm text-gray-500">
                                No recent requests.
                            </td>
                        </tr>
                    @endforelse

                </tbody>
            </table>
        </div>
    </div>

    @vite('resources/js/employee/employees.js')
    @vite('resources/js/employee/dashboard.js')

@endsection
