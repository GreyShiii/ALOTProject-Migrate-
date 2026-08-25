@extends('layouts.app')

@section('title', 'Overtime Requests')
@section('breadcrumb-parent', 'Management')
@section('breadcrumb-current', 'Overtime Requests')

@section('content')

    <div class="mb-6">
        <h1 class="text-xl font-bold tracking-tight text-gray-900 sm:text-2xl">
            Overtime Requests
        </h1>

        <p class="mt-1 text-sm text-gray-500">
            Monitor overtime requests across the organization.
        </p>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

        <div class="border-b border-gray-200 px-4 py-4 sm:px-6 sm:py-5">
            <h2 class="text-lg font-semibold text-gray-900">
                All Overtime Requests
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                View employee overtime requests and their current status.
            </p>
        </div>

        <div class="flex flex-col gap-3 border-b border-gray-200 px-4 py-4 sm:flex-row sm:items-center sm:px-6">

            <div class="relative flex-1">
                <input type="text" id="admin-overtime-search" placeholder="Search employee, reason..."
                    class="w-full rounded-lg border border-gray-300 bg-white py-2.5 px-3 text-sm text-gray-700 placeholder-gray-400 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">
            </div>

            <select id="admin-overtime-status"
                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-700 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 sm:w-48">
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
            </select>

            <select id="admin-overtime-department"
                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-700 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 sm:w-48">
                <option value="">All Departments</option>

                @foreach ($departments as $department)
                    <option value="{{ $department->id }}">
                        {{ $department->name }}
                    </option>
                @endforeach
            </select>

            <input type="date" id="admin-overtime-date"
                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-700 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 sm:w-48">

        </div>

        <div class="hidden overflow-x-auto md:block">
            <table class="w-full table-fixed divide-y divide-gray-200">

                <thead class="bg-gray-50">
                    <tr>
                        <th
                            class="whitespace-nowrap px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Employee
                        </th>

                        <th
                            class="whitespace-nowrap px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Department
                        </th>

                        <th
                            class="whitespace-nowrap px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Date
                        </th>

                        <th
                            class="whitespace-nowrap px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Hours
                        </th>

                        <th
                            class="whitespace-nowrap px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Status
                        </th>

                        <th
                            class="whitespace-nowrap px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Action
                        </th>
                    </tr>
                </thead>

                <tbody id="admin-overtime-table-body" class="divide-y divide-gray-200 bg-white"></tbody>

            </table>
        </div>

        <div id="admin-overtime-card-list" class="divide-y divide-gray-200 md:hidden"></div>

        <div id="admin-overtime-empty" class="hidden px-6 py-12 text-center">
            <p class="text-sm font-semibold text-gray-700">
                No overtime requests found.
            </p>

            <p class="mt-1 text-xs text-gray-500">
                Try changing your search or filters.
            </p>
        </div>

        <div id="admin-overtime-pagination" class="border-t border-gray-200 bg-white"></div>

    </div>

    <div id="admin-overtime-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4">
        <div class="w-full max-w-xl rounded-xl bg-white shadow-xl">

            <div class="flex items-start justify-between border-b border-gray-200 px-6 py-5">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">
                        Overtime Request Details
                    </h2>

                    <p class="mt-1 text-sm text-gray-500">
                        Complete information for this request.
                    </p>
                </div>

                <button type="button" id="close-admin-overtime-modal"
                    class="flex h-10 w-10 items-center justify-center rounded-full border border-teal-300 text-teal-500 transition hover:bg-teal-50"
                    aria-label="Close">
                    &times;
                </button>
            </div>

            <div class="space-y-5 px-6 py-5">

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Employee
                        </p>

                        <p id="admin-detail-employee" class="mt-1 text-sm font-semibold text-gray-900">
                            —
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Department
                        </p>

                        <p id="admin-detail-department" class="mt-1 text-sm text-gray-900">
                            —
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Position
                        </p>

                        <p id="admin-detail-position" class="mt-1 text-sm text-gray-900">
                            —
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Date
                        </p>

                        <p id="admin-detail-date" class="mt-1 text-sm text-gray-900">
                            —
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Hours
                        </p>

                        <p id="admin-detail-hours" class="mt-1 text-sm font-medium text-gray-900">
                            —
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Submitted
                        </p>

                        <p id="admin-detail-submitted" class="mt-1 text-sm text-gray-900">
                            —
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Status
                        </p>

                        <div id="admin-detail-status" class="mt-1">
                            —
                        </div>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Reviewed By
                        </p>

                        <p id="admin-detail-approver" class="mt-1 text-sm text-gray-900">
                            —
                        </p>
                    </div>

                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Reason
                    </p>

                    <div id="admin-detail-reason"
                        class="mt-2 break-words rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-700">
                        —
                    </div>
                </div>

                <div id="admin-detail-rejection-container" class="hidden">
                    <p class="text-xs font-semibold uppercase tracking-wide text-red-600">
                        Rejection Reason
                    </p>

                    <div id="admin-detail-rejection"
                        class="mt-2 break-words rounded-lg border border-red-100 bg-red-50 px-4 py-3 text-sm text-red-700">
                        —
                    </div>
                </div>

            </div>

            <div class="flex justify-end border-t border-gray-200 px-6 py-4">
                <button type="button" id="close-admin-overtime-footer"
                    class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
                    Close
                </button>
            </div>

        </div>
    </div>

    @vite('resources/js/admin/overtime.js')

@endsection
