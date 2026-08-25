@extends('layouts.app')

@section('title', 'Attendance')
@section('breadcrumb-parent', 'Employee')
@section('breadcrumb-current', 'Attendance')

@section('content')

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

        <div class="border-b border-gray-200 px-4 py-4 sm:px-6 sm:py-5">
            <h2 class="text-lg font-semibold text-gray-900">
                Attendance History
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Record your daily time in and time out, and review your history.
            </p>
        </div>

        <div class="flex flex-col gap-3 border-b border-gray-200 px-4 py-4 sm:flex-row sm:items-center sm:px-6">

            <div class="relative flex-1">
                <input type="text" id="attendance-search" placeholder="Search by date"
                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 pl-10 text-sm text-gray-700 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">

                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400"
                    fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="7" />

                    <path stroke-linecap="round" stroke-linejoin="round" d="m20 20-4-4" />
                </svg>
            </div>

            <input type="date" id="attendance-date"
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-700 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 sm:w-52">

            <select id="attendance-status"
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-700 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 sm:w-52">
                <option value="">All statuses</option>

                <option value="working">
                    Working
                </option>

                <option value="completed">
                    Completed
                </option>
            </select>

        </div>

        <div id="attendance-results">
            @include('employee.attendance.partials.table')
        </div>

    </div>

    @vite('resources/js/employee/attendance.js')

@endsection
