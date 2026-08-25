@extends('layouts.app')

@section('title', 'Attendance Management')
@section('breadcrumb-parent', 'Management')
@section('breadcrumb-current', 'Attendance')

@section('content')

    <div class="mb-6">
        <h1 class="text-xl font-bold tracking-tight text-gray-900 sm:text-2xl">
            Attendance Management
        </h1>

        <p class="mt-1 text-sm text-gray-500">
            Attendance records for all employees in the organization.
        </p>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

        <div class="border-b border-gray-200 px-4 py-4 sm:px-6 sm:py-5">
            <h2 class="text-lg font-semibold text-gray-900">
                Attendance Records
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Search and filter employee attendance records.
            </p>
        </div>

        <div class="flex flex-col gap-3 border-b border-gray-200 px-4 py-4 sm:flex-row sm:items-center sm:px-6">

            <div class="relative flex-1">
                <input
                    type="text"
                    id="attendance-search"
                    placeholder="Search employee or email"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30"
                >
            </div>

            <select
                id="attendance-department"
                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-700 transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30 sm:w-52"
            >
                <option value="">All departments</option>

                @foreach ($departments as $department)
                    <option value="{{ $department->id }}">
                        {{ $department->name }}
                    </option>
                @endforeach
            </select>

            <input
                type="date"
                id="attendance-date"
                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-700 transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30 sm:w-52"
            >

            <select
                id="attendance-status"
                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-700 transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30 sm:w-52"
            >
                <option value="">All statuses</option>
                <option value="completed">Completed</option>
                <option value="working">Working</option>
            </select>

        </div>

        <div class="overflow-x-auto">
            <table class="min-w-[950px] w-full divide-y divide-gray-200">

                <thead class="bg-gray-50">
                    <tr>
                        <th class="whitespace-nowrap px-5 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Employee
                        </th>

                        <th class="whitespace-nowrap px-5 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Department
                        </th>

                        <th class="whitespace-nowrap px-5 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Date
                        </th>

                        <th class="whitespace-nowrap px-5 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Time In
                        </th>

                        <th class="whitespace-nowrap px-5 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Time Out
                        </th>

                        <th class="whitespace-nowrap px-5 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Total Hours
                        </th>

                        <th class="whitespace-nowrap px-5 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Status
                        </th>
                    </tr>
                </thead>

                <tbody
                    id="attendance-table-body"
                    class="divide-y divide-gray-200 bg-white"
                ></tbody>

            </table>
        </div>

        <div
            id="attendance-empty"
            class="hidden px-5 py-10 text-center"
        >
            <p class="text-sm font-semibold text-gray-700">
                No attendance records found.
            </p>

            <p class="mt-1 text-sm text-gray-500">
                Try changing your search or filters.
            </p>
        </div>

        <div
            id="attendance-pagination"
            class="border-t border-gray-200 bg-white"
        ></div>

    </div>

    @vite('resources/js/admin/attendance.js')

@endsection
