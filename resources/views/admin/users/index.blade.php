@extends('layouts.app')

@section('title', 'User Management')
@section('breadcrumb-parent', 'Management')
@section('breadcrumb-current', 'User Management')

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

    <div class="mb-6">
        <h1 class="text-xl font-bold tracking-tight text-gray-900 sm:text-2xl">
            User Management
        </h1>

        <p class="mt-1 text-sm text-gray-500">
            Manage system accounts, roles, and account access.
        </p>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

        <div class="border-b border-gray-200 px-4 py-4 sm:px-6 sm:py-5">
            <h2 class="text-lg font-semibold text-gray-900">
                Accounts
            </h2>
        </div>

        <div class="flex flex-col gap-3 border-b border-gray-200 px-4 py-4 sm:flex-row sm:items-center sm:px-6">

            <div class="relative flex-1">
                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="7"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>

                <input type="text" id="user-search" placeholder="Search name or email"
                    class="w-full rounded-lg border border-gray-300 bg-white py-2.5 pl-10 pr-3 text-sm text-gray-900 placeholder-gray-400 transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30">
            </div>

            <select id="filter-role"
                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-700 transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30 sm:w-48">
                <option value="">All roles</option>
                <option value="admin">Admin</option>
                <option value="manager">Manager</option>
                <option value="employee">Employee</option>
            </select>

            <select id="filter-status"
                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-700 transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30 sm:w-48">
                <option value="">All status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>

        </div>

        <div class="hidden overflow-x-auto md:block">

            <div class="hidden overflow-x-auto md:block">

                <table class="w-full table-fixed divide-y divide-gray-200">

                    <colgroup>
                        <col class="w-[18%]">
                        <col class="w-[20%]">
                        <col class="w-[12%]">
                        <col class="w-[15%]">
                        <col class="w-[12%]">
                        <col class="w-[23%]">
                    </colgroup>

                    <thead class="bg-gray-50">

                        <tr>

                            <th
                                class="whitespace-nowrap px-3 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">
                                User
                            </th>

                            <th
                                class="whitespace-nowrap px-3 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">
                                Email
                            </th>

                            <th
                                class="whitespace-nowrap px-3 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">
                                Role
                            </th>

                            <th
                                class="whitespace-nowrap px-3 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">
                                Created At
                            </th>

                            <th
                                class="whitespace-nowrap px-3 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">
                                Status
                            </th>

                            <th
                                class="whitespace-nowrap px-3 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">
                                Actions
                            </th>

                        </tr>

                    </thead>

                    <tbody id="user-table-body" class="divide-y divide-gray-200 bg-white">

                        @forelse ($users as $user)
                            <tr id="user-row-{{ $user->id }}" class="transition hover:bg-gray-50"
                                data-role="{{ $user->primaryRoleName() }}" data-status="{{ $user->status }}">

                                <td class="px-3 py-4 text-center text-sm font-semibold text-gray-900">
                                    <div class="truncate">
                                        {{ $user->first_name }} {{ $user->last_name }}
                                    </div>
                                </td>

                                <td class="px-3 py-4 text-center text-sm text-gray-700">
                                    <div class="truncate">
                                        {{ $user->email }}
                                    </div>
                                </td>

                                <td class="px-3 py-4 text-center">

                                    @if ($user->hasRole('admin'))
                                        <span data-user-role
                                            class="inline-flex rounded-full bg-purple-100 px-2.5 py-1 text-xs font-semibold text-purple-700">
                                            Admin
                                        </span>
                                    @elseif ($user->hasRole('manager'))
                                        <span data-user-role
                                            class="inline-flex rounded-full bg-blue-100 px-2.5 py-1 text-xs font-semibold text-blue-700">
                                            Manager
                                        </span>
                                    @else
                                        <span data-user-role
                                            class="inline-flex rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-700">
                                            Employee
                                        </span>
                                    @endif

                                </td>

                                <td class="whitespace-nowrap px-3 py-4 text-center text-sm text-gray-700">
                                    {{ $user->created_at->format('M j, Y') }}
                                </td>

                                <td class="px-3 py-4 text-center">

                                    @if ($user->status === 'active')
                                        <span data-user-status
                                            class="inline-flex rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-700">
                                            Active
                                        </span>
                                    @else
                                        <span data-user-status
                                            class="inline-flex rounded-full bg-red-100 px-2.5 py-1 text-xs font-semibold text-red-700">
                                            Inactive
                                        </span>
                                    @endif

                                </td>

                                <td class="whitespace-nowrap px-3 py-4 text-center">

                                    <div class="flex items-center justify-center gap-2">

                                        <button type="button" data-id="{{ $user->id }}"
                                            class="view-user-btn rounded-md border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 shadow-sm transition hover:bg-slate-100 hover:text-slate-900">
                                            View
                                        </button>

                                        <button type="button" data-id="{{ $user->id }}"
                                            class="edit-user-btn rounded-md border border-slate-200 bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-700 transition hover:bg-slate-200">
                                            Edit
                                        </button>

                                        @if ($user->status === 'active')
                                            <button type="button" data-id="{{ $user->id }}"
                                                class="deactivate-user-btn w-[84px] rounded-md bg-amber-600 px-3 py-1.5 text-xs font-medium text-white transition hover:bg-amber-700">
                                                Deactivate
                                            </button>
                                        @else
                                            <button type="button" data-id="{{ $user->id }}"
                                                class="activate-user-btn w-[84px] rounded-md bg-green-600 px-3 py-1.5 text-xs font-medium text-white transition hover:bg-green-700">
                                                Activate
                                            </button>
                                        @endif

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr id="no-users">

                                <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500">
                                    No users yet.
                                </td>

                            </tr>
                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

        <div id="user-card-list" class="divide-y divide-gray-200 md:hidden">
            @forelse ($users as $user)
                <div id="user-card-{{ $user->id }}" class="px-4 py-4" data-role="{{ $user->primaryRoleName() }}"
                    data-status="{{ $user->status }}">
                    <div class="flex items-start justify-between gap-3">

                        <div class="min-w-0">
                            <p data-user-name class="truncate text-sm font-semibold text-gray-900">
                                {{ $user->first_name }} {{ $user->last_name }}
                            </p>

                            <p data-user-email class="mt-1 truncate text-xs text-gray-500">
                                {{ $user->email }}
                            </p>
                        </div>

                        @if ($user->status === 'active')
                            <span data-user-status
                                class="shrink-0 rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-700">
                                Active
                            </span>
                        @else
                            <span data-user-status
                                class="shrink-0 rounded-full bg-red-100 px-2.5 py-1 text-xs font-semibold text-red-700">
                                Inactive
                            </span>
                        @endif

                    </div>

                    <dl class="mt-3 grid grid-cols-2 gap-x-4 gap-y-2 text-sm">

                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-gray-400">
                                Role
                            </dt>

                            <dd data-user-role class="mt-1 text-gray-700">
                                {{ ucfirst($user->primaryRoleName() ?? '') }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-gray-400">
                                Created At
                            </dt>

                            <dd class="mt-1 text-gray-700">
                                {{ $user->created_at->format('M j, Y') }}
                            </dd>
                        </div>

                    </dl>

                    <div class="mt-4 flex items-center gap-2">

                        <button type="button" data-id="{{ $user->id }}"
                            class="view-user-btn flex-1 rounded-md border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-700 transition hover:bg-slate-100">
                            View
                        </button>

                        <button type="button" data-id="{{ $user->id }}"
                            class="edit-user-btn flex-1 rounded-md border border-slate-200 bg-slate-100 px-3 py-2 text-xs font-medium text-slate-700 transition hover:bg-slate-200">
                            Edit
                        </button>

                        @if ($user->status === 'active')
                            <button type="button" data-id="{{ $user->id }}"
                                class="deactivate-user-btn flex-1 rounded-md bg-amber-600 px-3 py-2 text-xs font-medium text-white transition hover:bg-amber-700">
                                Deactivate
                            </button>
                        @else
                            <button type="button" data-id="{{ $user->id }}"
                                class="activate-user-btn flex-1 rounded-md bg-green-600 px-3 py-2 text-xs font-medium text-white transition hover:bg-green-700">
                                Activate
                            </button>
                        @endif

                    </div>
                </div>
            @empty
                <div id="no-user-cards" class="px-4 py-12 text-center text-sm text-gray-500">
                    No users yet.
                </div>
            @endforelse
        </div>

        <div id="user-pagination" class="border-t border-gray-200 bg-white"></div>

    </div>

    @foreach ($users as $user)
        <div id="view-user-modal-{{ $user->id }}" class="fixed inset-0 z-50 hidden">
            <div class="absolute inset-0 bg-black/60"></div>

            <div
                class="absolute left-1/2 top-1/2 w-[calc(100%-2rem)] max-w-lg -translate-x-1/2 -translate-y-1/2 overflow-hidden rounded-2xl bg-white shadow-2xl">

                <div class="flex items-start justify-between gap-4 px-6 pb-4 pt-6">

                    <div>
                        <h2 class="text-2xl font-semibold tracking-tight text-gray-900">
                            {{ $user->first_name }} {{ $user->last_name }}
                        </h2>

                        <p class="mt-1 text-sm text-gray-400">
                            Account EMP-{{ str_pad($user->id, 4, '0', STR_PAD_LEFT) }}
                        </p>
                    </div>

                    <button type="button"
                        onclick="document.getElementById('view-user-modal-{{ $user->id }}').classList.add('hidden')"
                        class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full border border-teal-300 text-teal-500 transition hover:bg-teal-50"
                        aria-label="Close">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>

                </div>

                <div class="grid grid-cols-1 gap-x-8 gap-y-6 px-6 pb-8 sm:grid-cols-2">

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">
                            Email
                        </p>

                        <p class="mt-1.5 break-all text-sm text-gray-900">
                            {{ $user->email }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">
                            Role
                        </p>

                        <span
                            class="mt-1.5 inline-flex items-center rounded-full border border-gray-200 bg-gray-50 px-3 py-1 text-sm font-medium text-gray-700">
                            {{ ucfirst($user->primaryRoleName() ?? '') }}
                        </span>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">
                            Created
                        </p>

                        <p class="mt-1.5 text-sm text-gray-900">
                            {{ $user->created_at->format('M j, Y') }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">
                            Status
                        </p>

                        <p class="mt-1.5 text-sm font-medium text-gray-900">
                            {{ ucfirst($user->status) }}
                        </p>
                    </div>

                </div>

            </div>
        </div>
    @endforeach

    @foreach ($users as $user)
        <div id="edit-user-modal-{{ $user->id }}" class="fixed inset-0 z-50 hidden">
            <div class="absolute inset-0 bg-black/75"></div>

            <div
                class="absolute left-1/2 top-1/2 w-[calc(100%-2rem)] max-w-md -translate-x-1/2 -translate-y-1/2 rounded-2xl bg-white p-6 shadow-2xl">

                <div class="mb-1 flex items-start justify-between">

                    <h2 class="text-xl font-semibold text-gray-900">
                        Edit Account
                    </h2>

                    <button type="button"
                        onclick="document.getElementById('edit-user-modal-{{ $user->id }}').classList.add('hidden')"
                        class="text-gray-400 transition hover:text-gray-600">
                        &times;
                    </button>

                </div>

                <p class="mb-6 text-sm text-gray-500">
                    Update the system account information.
                </p>

                <p id="edit-user-error-{{ $user->id }}"
                    class="mb-4 hidden rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"></p>

                <form id="edit-user-form-{{ $user->id }}" class="edit-user-form space-y-4" method="POST"
                    action="{{ route('admin.users.update', $user) }}">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">
                            First Name
                        </label>

                        <input type="text" name="first_name" value="{{ $user->first_name }}"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-500/30">
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">
                            Last Name
                        </label>

                        <input type="text" name="last_name" value="{{ $user->last_name }}"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-500/30">
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">
                            Email
                        </label>

                        <input type="email" name="email" value="{{ $user->email }}"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-500/30">
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">
                            New Password
                        </label>

                        <input type="password" name="password" minlength="8" placeholder="Leave blank to keep current password"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-500/30">
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">
                            Role
                        </label>

                        <select name="role"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-500/30">
                            <option value="employee" @selected($user->hasRole('employee'))>
                                Employee
                            </option>

                            <option value="manager" @selected($user->hasRole('manager'))>
                                Manager
                            </option>

                            <option value="admin" @selected($user->hasRole('admin'))>
                                Admin
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">
                            Status
                        </label>

                        <select name="status"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-500/30">
                            <option value="active" @selected($user->status === 'active')>
                                Active
                            </option>

                            <option value="inactive" @selected($user->status === 'inactive')>
                                Inactive
                            </option>
                        </select>
                    </div>

                    <div class="flex flex-col-reverse gap-2 pt-2 sm:flex-row sm:justify-end">

                        <button type="button"
                            onclick="document.getElementById('edit-user-modal-{{ $user->id }}').classList.add('hidden')"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 sm:w-auto">
                            Cancel
                        </button>

                        <button type="submit"
                            class="w-full rounded-lg bg-[#11458c] px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:w-auto">
                            Save Changes
                        </button>

                    </div>

                </form>

            </div>
        </div>
    @endforeach

    @foreach ($users as $user)
        <div id="deactivate-user-modal-{{ $user->id }}" class="fixed inset-0 z-50 hidden">
            <div class="absolute inset-0 bg-black/75"></div>

            <div
                class="absolute left-1/2 top-1/2 w-[calc(100%-2rem)] max-w-md -translate-x-1/2 -translate-y-1/2 rounded-xl bg-white p-4 shadow-2xl sm:p-6">

                <h2 class="mb-2 text-xl font-semibold text-gray-900">
                    Deactivate {{ $user->first_name }} {{ $user->last_name }}?
                </h2>

                <p class="mb-6 text-sm text-gray-500">
                    This will disable the user's access to the system.
                    Their employee record and historical records will be preserved.
                </p>

                <p id="deactivate-user-error-{{ $user->id }}"
                    class="mb-4 hidden rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"></p>

                <form id="deactivate-user-form-{{ $user->id }}" class="deactivate-user-form" method="POST"
                    action="{{ route('admin.users.deactivate', $user) }}">
                    @csrf

                    <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">

                        <button type="button"
                            onclick="document.getElementById('deactivate-user-modal-{{ $user->id }}').classList.add('hidden')"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 sm:w-auto">
                            Cancel
                        </button>

                        <button type="submit"
                            class="w-full rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-amber-700 sm:w-auto">
                            Deactivate User
                        </button>

                    </div>
                </form>

            </div>
        </div>
    @endforeach

    @foreach ($users as $user)
        <div id="activate-user-modal-{{ $user->id }}" class="fixed inset-0 z-50 hidden">
            <div class="absolute inset-0 bg-black/75"></div>

            <div
                class="absolute left-1/2 top-1/2 w-[calc(100%-2rem)] max-w-md -translate-x-1/2 -translate-y-1/2 rounded-xl bg-white p-4 shadow-2xl sm:p-6">

                <h2 class="mb-2 text-xl font-semibold text-gray-900">
                    Activate {{ $user->first_name }} {{ $user->last_name }}?
                </h2>

                <p class="mb-6 text-sm text-gray-500">
                    This will restore the user's access to the system.
                </p>

                <p id="activate-user-error-{{ $user->id }}"
                    class="mb-4 hidden rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"></p>

                <form id="activate-user-form-{{ $user->id }}" class="activate-user-form" method="POST"
                    action="{{ route('admin.users.activate', $user) }}">
                    @csrf

                    <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">

                        <button type="button"
                            onclick="document.getElementById('activate-user-modal-{{ $user->id }}').classList.add('hidden')"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 sm:w-auto">
                            Cancel
                        </button>

                        <button type="submit"
                            class="w-full rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-green-700 sm:w-auto">
                            Activate User
                        </button>

                    </div>
                </form>

            </div>
        </div>
    @endforeach

    @vite('resources/js/admin/users.js')

@endsection
