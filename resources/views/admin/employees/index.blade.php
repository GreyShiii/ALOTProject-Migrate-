@extends('layouts.app')

@section('title', 'Employee Management')
@section('breadcrumb-parent', 'Management')
@section('breadcrumb-current', 'Employee Management')

@section('content')

    @if (session('success'))
        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="text-xl font-bold tracking-tight text-gray-900 sm:text-2xl">
                Employee Management
            </h1>
            <p class="mt-1 text-sm text-gray-500">
                All employee records across the organization.
            </p>
        </div>

        <button type="button" id="add-employee-btn"
            class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-[#11458c] px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:w-auto">
            <span class="text-base leading-none">+</span>
            Add Employee
        </button>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

        <div class="border-b border-gray-200 px-4 py-4 sm:px-6 sm:py-5">
            <h2 class="text-lg font-semibold text-gray-900">
                Employees
            </h2>
        </div>

        <div class="flex flex-col gap-3 border-b border-gray-200 px-4 py-4 sm:flex-row sm:items-center sm:px-6">
            <div class="relative flex-1">
                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <circle cx="11" cy="11" r="7"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>

                <input type="text" id="employee-search" placeholder="Search name, email or employee ID"
                    class="w-full rounded-lg border border-gray-300 bg-white py-2.5 pl-10 pr-3 text-sm text-gray-900 placeholder-gray-400 transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30">
            </div>

            <select id="filter-department"
                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-700 transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30 sm:w-52">
                <option value="">All departments</option>
                @foreach ($departments as $department)
                    <option value="{{ $department->name }}">
                        {{ $department->name }}
                    </option>
                @endforeach
            </select>

            <select id="filter-manager"
                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-700 transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30 sm:w-52">
                <option value="">All managers</option>
                @foreach ($managers as $manager)
                    <option value="{{ $manager->user->first_name }} {{ $manager->user->last_name }}">
                        {{ $manager->user->first_name }} {{ $manager->user->last_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="hidden overflow-x-auto md:block">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th
                            class="whitespace-nowrap px-3 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Employee ID</th>
                        <th
                            class="whitespace-nowrap px-3 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Employee Name</th>
                        <th
                            class="whitespace-nowrap px-3 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Email</th>
                        <th class="px-3 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Position</th>
                        <th
                            class="whitespace-nowrap px-3 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Department</th>
                        <th
                            class="whitespace-nowrap px-3 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Manager</th>
                        <th
                            class="whitespace-nowrap px-3 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Hire Date</th>
                        <th
                            class="whitespace-nowrap px-3 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Status</th>
                        <th
                            class="whitespace-nowrap px-3 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Actions</th>
                    </tr>
                </thead>

                <tbody id="employee-table-body" class="divide-y divide-gray-200 bg-white">
                    @forelse ($employees as $employee)
                        <tr id="employee-row-{{ $employee->id }}" class="transition hover:bg-gray-50"
                            data-department="{{ $employee->department->name }}"
                            data-manager="{{ $employee->manager ? $employee->manager->user->first_name . ' ' . $employee->manager->user->last_name : '' }}">
                            <td class="whitespace-nowrap px-3 py-4 text-center font-mono text-xs text-gray-500">
                                {{ 'EMP-' . str_pad($employee->id, 2, '0', STR_PAD_LEFT) }}
                            </td>

                            <td class="px-3 py-4 text-center text-sm font-semibold text-gray-900">
                                {{ $employee->user->first_name }} {{ $employee->user->last_name }}
                            </td>

                            <td class="break-all px-3 py-4 text-center text-sm text-gray-500">
                                {{ $employee->user->email }}
                            </td>

                            <td class="max-w-[160px] px-3 py-4 text-center text-sm text-gray-700">
                                {{ $employee->position }}
                            </td>

                            <td class="px-3 py-4 text-center text-sm text-gray-700">
                                {{ $employee->department->name }}
                            </td>

                            <td class="px-3 py-4 text-center text-sm text-gray-700">
                                @if ($employee->manager)
                                    {{ $employee->manager->user->first_name }} {{ $employee->manager->user->last_name }}
                                @else
                                    <span class="text-gray-400">None</span>
                                @endif
                            </td>

                            <td class="whitespace-nowrap px-3 py-4 text-center text-sm text-gray-700">
                                {{ $employee->hire_date?->format('M j, Y') ?? 'N/A' }}
                            </td>

                            <td class="whitespace-nowrap px-3 py-4 text-center">
                                @if ($employee->user->status === 'active')
                                    <span
                                        class="inline-flex items-center rounded-full border border-green-200 bg-green-50 px-2.5 py-1 text-xs font-semibold text-green-700">
                                        Active
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center rounded-full border border-gray-200 bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600">
                                        Inactive
                                    </span>
                                @endif
                            </td>

                            <td class="whitespace-nowrap px-3 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button"
                                        class="view-employee-btn rounded-md border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 shadow-sm transition hover:bg-slate-100 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-300/60"
                                        data-id="{{ $employee->id }}" data-first-name="{{ $employee->user->first_name }}"
                                        data-last-name="{{ $employee->user->last_name }}"
                                        data-email="{{ $employee->user->email }}" data-role="{{ $employee->user->primaryRoleName() }}"
                                        data-status="{{ $employee->user->status }}"
                                        data-position="{{ $employee->position }}"
                                        data-department="{{ $employee->department->name }}"
                                        data-manager="{{ $employee->manager ? $employee->manager->user->first_name . ' ' . $employee->manager->user->last_name : 'None' }}"
                                        data-hire-date="{{ $employee->hire_date?->format('M j, Y') ?? 'N/A' }}">
                                        View
                                    </button>

                                    <button type="button"
                                        class="edit-employee-btn rounded-md border border-slate-200 bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-700 transition hover:bg-slate-200 focus:outline-none focus:ring-2 focus:ring-slate-300/60"
                                        data-id="{{ $employee->id }}">
                                        Edit
                                    </button>

                                    <button type="button"
                                        class="delete-employee-btn rounded-md bg-red-600 px-3 py-1.5 text-xs font-medium text-white shadow-sm transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500/40"
                                        data-id="{{ $employee->id }}" data-first-name="{{ $employee->user->first_name }}"
                                        data-last-name="{{ $employee->user->last_name }}">
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr id="no-employees">
                            <td colspan="9" class="px-6 py-12 text-center text-sm text-gray-500">
                                No employees yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div id="employee-card-list" class="divide-y divide-gray-200 md:hidden">
            @forelse ($employees as $employee)
                <div id="employee-card-{{ $employee->id }}" class="px-4 py-4"
                    data-department="{{ $employee->department->name }}"
                    data-manager="{{ $employee->manager ? $employee->manager->user->first_name . ' ' . $employee->manager->user->last_name : '' }}">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-gray-900">
                                {{ $employee->user->first_name }} {{ $employee->user->last_name }}
                            </p>

                            <p class="truncate text-sm text-gray-500">
                                {{ $employee->user->email }}
                            </p>

                            <p class="mt-1 font-mono text-xs text-gray-400">
                                {{ 'EMP-' . str_pad($employee->id, 2, '0', STR_PAD_LEFT) }}
                            </p>
                        </div>

                        @if ($employee->user->status === 'active')
                            <span
                                class="inline-flex shrink-0 items-center rounded-full border border-green-200 bg-green-50 px-2.5 py-1 text-xs font-semibold text-green-700">
                                Active
                            </span>
                        @else
                            <span
                                class="inline-flex shrink-0 items-center rounded-full border border-gray-200 bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600">
                                Inactive
                            </span>
                        @endif
                    </div>

                    <dl class="mt-3 grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-gray-400">Position</dt>
                            <dd class="text-gray-700">{{ $employee->position }}</dd>
                        </div>

                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-gray-400">Department</dt>
                            <dd class="text-gray-700">{{ $employee->department->name }}</dd>
                        </div>

                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-gray-400">Manager</dt>
                            <dd class="text-gray-700">
                                @if ($employee->manager)
                                    {{ $employee->manager->user->first_name }} {{ $employee->manager->user->last_name }}
                                @else
                                    <span class="text-gray-400">None</span>
                                @endif
                            </dd>
                        </div>

                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-gray-400">Hire Date</dt>
                            <dd class="text-gray-700">
                                {{ $employee->hire_date?->format('M j, Y') ?? 'N/A' }}
                            </dd>
                        </div>
                    </dl>

                    <div class="mt-4 flex items-center gap-2">
                        <button type="button"
                            class="edit-employee-btn flex-1 rounded-md border border-slate-200 bg-slate-100 px-3 py-2 text-xs font-medium text-slate-700 transition hover:bg-slate-200 focus:outline-none focus:ring-2 focus:ring-slate-300/60"
                            data-id="{{ $employee->id }}">
                            Edit
                        </button>

                        <button type="button"
                            class="delete-employee-btn flex-1 rounded-md bg-red-600 px-3 py-2 text-xs font-medium text-white transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500/40"
                            data-id="{{ $employee->id }}" data-first-name="{{ $employee->user->first_name }}"
                            data-last-name="{{ $employee->user->last_name }}">
                            Delete
                        </button>
                    </div>
                </div>
            @empty
                <div class="px-4 py-12 text-center text-sm text-gray-500">
                    No employees yet.
                </div>
            @endforelse
        </div>

        <div id="employee-pagination" class="border-t border-gray-200 bg-white"></div>
    </div>

    <div id="add-employee-modal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/75"></div>

        <div
            class="absolute left-1/2 top-1/2 max-h-[90vh] w-[calc(100%-2rem)] max-w-2xl -translate-x-1/2 -translate-y-1/2 overflow-y-auto rounded-xl bg-white p-4 shadow-2xl sm:p-6">
            <div class="mb-6">
                <h2 class="text-xl font-semibold text-gray-900">Add Employee</h2>
                <p class="mt-1 text-sm text-gray-500">
                    Create a new employee record and system account.
                </p>
            </div>

            <p id="employee-error"
                class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 empty:hidden"></p>

            <form id="add-employee-form" method="POST" action="{{ route('admin.employees.store') }}"
                class="space-y-4">
                @csrf

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">
                            First Name
                        </label>

                        <input type="text" name="first_name" placeholder="Enter first name"
                            class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">
                            Last Name
                        </label>

                        <input type="text" name="last_name" placeholder="Enter last name"
                            class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">
                        Email
                    </label>

                    <input type="email" name="email" placeholder="name@company.com"
                        class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">
                        Password
                    </label>

                    <input type="password" name="password" minlength="8" placeholder="••••••••"
                        class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/30">

                    <p class="mt-1.5 text-xs text-gray-500">
                        Minimum 8 characters.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">
                            Role
                        </label>

                        <select name="role"
                            class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2.5 text-sm text-gray-900 transition focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                            <option value="employee">Employee</option>
                            <option value="manager">Manager</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">
                            Department
                        </label>

                        <select name="department_id"
                            class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2.5 text-sm text-gray-900 transition focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                            <option value="">Select department</option>

                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}">
                                    {{ $department->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">
                            Manager
                        </label>

                        <select name="manager_id"
                            class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2.5 text-sm text-gray-900 transition focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                            <option value="">No Manager</option>

                            @foreach ($managers as $manager)
                                <option value="{{ $manager->id }}">
                                    {{ $manager->user->first_name }} {{ $manager->user->last_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">
                            Position
                        </label>

                        <input type="text" name="position" placeholder="Enter position"
                            class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">
                        Hire Date
                    </label>

                    <input type="date" name="hire_date"
                        class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2.5 text-sm text-gray-900 transition focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                </div>

                <div
                    class="flex flex-col-reverse gap-3 border-t border-gray-200 pt-5 sm:flex-row sm:items-center sm:justify-end">
                    <button type="button" id="cancel-add-employee"
                        class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
                        Cancel
                    </button>

                    <button type="submit"
                        class="rounded-lg bg-[#11458c] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-800">
                        Add Employee
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="edit-employee-modal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/75"></div>

        <div
            class="absolute left-1/2 top-1/2 max-h-[90vh] w-[calc(100%-2rem)] max-w-2xl -translate-x-1/2 -translate-y-1/2 overflow-y-auto rounded-xl bg-white p-4 shadow-2xl sm:p-6">
            <div class="mb-6">
                <h2 class="text-xl font-semibold text-gray-900">
                    Edit Employee
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Leave the password field blank to keep the current password.
                </p>
            </div>

            <form id="edit-employee-form" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <p id="edit-employee-error" class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 empty:hidden"></p>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">
                            First Name
                        </label>

                        <input type="text" name="first_name"
                            class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2.5 text-sm text-gray-900 transition focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">
                            Last Name
                        </label>

                        <input type="text" name="last_name"
                            class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2.5 text-sm text-gray-900 transition focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">
                        Email
                    </label>

                    <input type="email" name="email"
                        class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2.5 text-sm text-gray-900 transition focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">
                        Password
                    </label>

                    <input type="password" name="password" minlength="8" placeholder="Leave blank to keep current password"
                        class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/30">

                    <p class="mt-1.5 text-xs text-gray-500">
                        Only required when changing the password.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">
                            Role
                        </label>

                        <select name="role"
                            class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2.5 text-sm text-gray-900 transition focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                            <option value="employee">Employee</option>
                            <option value="manager">Manager</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">
                            Department
                        </label>

                        <select name="department_id"
                            class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2.5 text-sm text-gray-900 transition focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}">
                                    {{ $department->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">
                            Manager
                        </label>

                        <select name="manager_id"
                            class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2.5 text-sm text-gray-900 transition focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                            <option value="">No Manager</option>

                            @foreach ($managers as $manager)
                                <option value="{{ $manager->id }}">
                                    {{ $manager->user->first_name }} {{ $manager->user->last_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">
                            Position
                        </label>

                        <input type="text" name="position"
                            class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2.5 text-sm text-gray-900 transition focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">
                        Hire Date
                    </label>

                    <input type="date" name="hire_date"
                        class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2.5 text-sm text-gray-900 transition focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                </div>

                <div
                    class="flex flex-col-reverse gap-3 border-t border-gray-200 pt-5 sm:flex-row sm:items-center sm:justify-end">
                    <button type="button" id="cancel-edit-employee"
                        class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
                        Cancel
                    </button>

                    <button type="submit"
                        class="rounded-lg bg-[#11458c] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-800">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="view-employee-modal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/60"></div>

        <div
            class="absolute left-1/2 top-1/2 w-[calc(100%-2rem)] max-w-xl -translate-x-1/2 -translate-y-1/2 overflow-hidden rounded-2xl bg-white shadow-2xl">
            <div class="flex items-start justify-between gap-4 px-6 pb-4 pt-6">
                <div>
                    <h2 id="view-employee-name" class="text-2xl font-semibold tracking-tight text-gray-900">
                        Employee Name
                    </h2>

                    <p id="view-employee-account" class="mt-1 text-sm text-gray-400">
                        Employee record EMP-0000
                    </p>
                </div>

                <button type="button" id="close-view-employee"
                    class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full border border-teal-300 text-teal-500 transition hover:bg-teal-50 focus:outline-none focus:ring-2 focus:ring-teal-400/30"
                    aria-label="Close">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="grid grid-cols-1 gap-x-8 gap-y-6 px-6 pb-8 sm:grid-cols-2">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">
                        Email
                    </p>

                    <p id="view-employee-email" class="mt-1.5 break-all text-sm text-gray-900">
                        employee@company.com
                    </p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">
                        Role
                    </p>

                    <span id="view-employee-role"
                        class="mt-1.5 inline-flex items-center rounded-full border border-gray-200 bg-gray-50 px-3 py-1 text-sm font-medium text-gray-700">
                        Employee
                    </span>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">
                        Position
                    </p>

                    <p id="view-employee-position" class="mt-1.5 text-sm text-gray-900">
                        —
                    </p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">
                        Department
                    </p>

                    <p id="view-employee-department" class="mt-1.5 text-sm text-gray-900">
                        —
                    </p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">
                        Manager
                    </p>

                    <p id="view-employee-manager" class="mt-1.5 text-sm text-gray-900">
                        —
                    </p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">
                        Hire Date
                    </p>

                    <p id="view-employee-hire-date" class="mt-1.5 text-sm text-gray-900">
                        —
                    </p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">
                        Status
                    </p>

                    <p id="view-employee-status" class="mt-1.5 text-sm font-medium text-gray-900">
                        Active
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div id="delete-employee-modal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/75"></div>

        <div
            class="absolute left-1/2 top-1/2 w-[calc(100%-2rem)] max-w-md -translate-x-1/2 -translate-y-1/2 rounded-xl bg-white p-4 shadow-2xl sm:p-6">
            <h2 class="text-lg font-semibold text-gray-900">
                Delete <span id="delete-employee-name"></span>?
            </h2>

            <p class="mt-2 text-sm text-gray-500">
                This action cannot be undone.
            </p>

            <form id="delete-employee-form" method="POST"
                class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-end">
                @csrf
                @method('DELETE')

                <button type="button" id="cancel-delete-employee"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
                    Cancel
                </button>

                <button type="submit"
                    class="rounded-lg bg-red-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-red-700">
                    Delete Employee
                </button>
            </form>
        </div>
    </div>

    @vite('resources/js/admin/employees.js')

@endsection
