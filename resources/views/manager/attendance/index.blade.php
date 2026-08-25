@extends('layouts.app')

@section('title', 'Attendance')
@section('breadcrumb-parent', 'Manager')
@section('breadcrumb-current', 'Attendance')

@section('content')

    <div class="mb-6">

        <h1 class="text-xl font-bold tracking-tight text-gray-900 sm:text-2xl">
            Attendance
        </h1>

        <p class="mt-1 text-sm text-gray-500">
            Record your attendance and monitor your team's attendance.
        </p>

    </div>


    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

        <div class="bg-gradient-to-r from-[#11458c] to-teal-600 px-5 py-6 text-white">

            <p class="text-sm font-semibold uppercase tracking-wide">
                Today's Attendance
            </p>

            <div class="mt-2 flex items-center justify-between gap-4">

                <h2 class="text-2xl font-bold sm:text-3xl">
                    {{ now()->format('F j, Y') }}
                </h2>

                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-white/15">

                    <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="9" />

                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 2" />

                    </svg>

                </div>

            </div>

        </div>


        <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-4">

            <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">

                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Status
                </p>

                <p id="attendance-status-text" class="mt-2 text-lg font-semibold text-gray-900">

                    @if ($todayAttendance?->time_in && $todayAttendance?->time_out)
                        Completed
                    @elseif ($todayAttendance?->time_in)
                        Working
                    @endif

                </p>

            </div>


            <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">

                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Time In
                </p>

                <p id="attendance-time-in" class="mt-2 text-lg font-semibold text-gray-900">
                    {{ $todayAttendance?->time_in?->format('h:i A') ?? '—' }}
                </p>

            </div>


            <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">

                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Time Out
                </p>

                <p id="attendance-time-out" class="mt-2 text-lg font-semibold text-gray-900">
                    {{ $todayAttendance?->time_out?->format('h:i A') ?? '—' }}
                </p>

            </div>


            <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">

                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Total Hours
                </p>

                <p id="attendance-total-hours" class="mt-2 text-lg font-semibold text-gray-900">
                    —
                </p>

            </div>

        </div>


        <div id="attendance-action-area" class="flex justify-end border-t border-gray-200 px-5 py-4">

            @if (!$todayAttendance)
                <form id="manager-time-in-form" method="POST" action="{{ route('manager.attendance.timeIn') }}">
                    @csrf

                    <button type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#11458c] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-800">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 5l7 7-7 7M6 12h14" />
                        </svg>

                        Time In
                    </button>

                </form>
            @elseif (!$todayAttendance->time_out)
                <form id="manager-time-out-form" method="POST" action="{{ route('manager.attendance.timeOut') }}">
                    @csrf

                    <button type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-red-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-red-700">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5l-7 7 7 7M18 12H4" />
                        </svg>

                        Time Out
                    </button>

                </form>
            @else
                <p class="text-sm text-gray-500">
                    Your attendance for today is complete.
                </p>
            @endif

        </div>

    </div>


    <div class="mt-6 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

        <div class="border-b border-gray-200 px-5 py-4">

            <h2 class="text-lg font-semibold text-gray-900">
                My Attendance History
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                View your previous attendance records.
            </p>

        </div>


        <div class="border-b border-gray-200 bg-gray-50 px-5 py-4">

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">

                <input type="text" id="my-attendance-search" placeholder="Search by date"
                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-700 outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500">

                <input type="date" id="my-attendance-date"
                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-700 outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500">

                <select id="my-attendance-status"
                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-700 outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    <option value="">
                        All statuses
                    </option>

                    <option value="working">
                        Working
                    </option>

                    <option value="completed">
                        Completed
                    </option>
                </select>

            </div>

        </div>


        <div class="overflow-x-auto">

            <table class="w-full min-w-[700px] divide-y divide-gray-200">

                <thead class="bg-gray-50">

                    <tr>

                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Date
                        </th>

                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Time In
                        </th>

                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Time Out
                        </th>

                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Total Hours
                        </th>

                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Status
                        </th>

                    </tr>

                </thead>


                <tbody id="my-attendance-table-body" class="divide-y divide-gray-200 bg-white"></tbody>

            </table>

        </div>


        <div id="my-attendance-empty" class="hidden px-5 py-10 text-center text-sm text-gray-500">
            No attendance records found.
        </div>


        <div id="my-attendance-pagination" class="border-t border-gray-200 bg-white"></div>

    </div>


    <div class="mt-6 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

        <div class="border-b border-gray-200 px-5 py-4">

            <h2 class="text-lg font-semibold text-gray-900">
                Team Attendance
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Attendance records for employees directly assigned to you.
            </p>

        </div>


        <div class="border-b border-gray-200 bg-gray-50 px-5 py-4">

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">

                <input type="text" id="team-attendance-search" placeholder="Search employee or email"
                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-700 outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500">

                <input type="date" id="team-attendance-date"
                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-700 outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500">

                <select id="team-attendance-status"
                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-700 outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    <option value="">
                        All statuses
                    </option>

                    <option value="working">
                        Working
                    </option>

                    <option value="completed">
                        Completed
                    </option>
                </select>

            </div>

        </div>


        <div class="overflow-x-auto">

            <table class="w-full min-w-[700px] divide-y divide-gray-200">

                <thead class="bg-gray-50">

                    <tr>

                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Employee
                        </th>

                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Department
                        </th>

                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Date
                        </th>

                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Time In
                        </th>

                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Time Out
                        </th>

                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Status
                        </th>

                    </tr>

                </thead>


                <tbody id="team-attendance-table-body" class="divide-y divide-gray-200 bg-white"></tbody>

            </table>

        </div>


        <div id="team-attendance-empty" class="hidden px-5 py-10 text-center text-sm text-gray-500">
            No team attendance records found.
        </div>


        <div id="team-attendance-pagination" class="border-t border-gray-200 bg-white"></div>

    </div>


    @vite('resources/js/manager/attendance.js')

@endsection
