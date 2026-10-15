@extends('layouts.app')

@section('title', 'Profile')

@section(
    'breadcrumb-parent',
    auth()->user()->isAdmin()
        ? 'Admin'
        : (auth()->user()->isManager() ? 'Manager' : 'Employee')
)

@section('breadcrumb-current', 'Profile')

@section('content')

    @php
        if (auth()->user()->isAdmin()) {
            $profileRoutePrefix = 'admin.profile';
        } elseif (auth()->user()->isManager()) {
            $profileRoutePrefix = 'manager.profile';
        } else {
            $profileRoutePrefix = 'employee.profile';
        }
    @endphp

    <div class="space-y-6">

        {{-- =====================================================
            PAGE HEADER
        ===================================================== --}}

        <div>

            <h1 class="text-2xl font-semibold text-gray-900">
                Profile
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                Your personal, employment and account information.
            </p>

        </div>


        {{-- =====================================================
            PROFILE HEADER
        ===================================================== --}}

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">

            <div class="flex items-center gap-4">

                {{-- Avatar --}}
                <div
                    data-profile-avatar
                    class="flex h-16 w-16 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-blue-700 to-cyan-600 text-xl font-semibold text-white"
                >
                    {{ strtoupper(substr($user->first_name, 0, 1)) }}{{ strtoupper(substr($user->last_name, 0, 1)) }}
                </div>


                {{-- Name / Position --}}
                <div class="min-w-0">

                    <h2
                        data-profile-name
                        class="text-xl font-semibold text-gray-900"
                    >
                        {{ $user->first_name }} {{ $user->last_name }}
                    </h2>


                    <p class="mt-1 text-sm text-gray-500">

                        @if ($employee && $employee->position)

                            {{ $employee->position }}

                        @elseif (auth()->user()->isAdmin())

                            Administrator

                        @elseif (auth()->user()->isManager())

                            Manager

                        @else

                            Employee

                        @endif


                        @if ($employee && $employee->department)

                            · {{ $employee->department->name }}

                        @endif

                    </p>


                    <div class="mt-2">

                        <span
                            class="inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700"
                        >
                            {{ ucfirst($user->primaryRoleName() ?? '') }}
                        </span>

                    </div>

                </div>

            </div>

        </div>


        {{-- =====================================================
            PROFILE INFORMATION
        ===================================================== --}}

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

            {{-- =================================================
                PERSONAL INFORMATION
            ================================================== --}}

            <div class="rounded-xl border border-gray-200 bg-white shadow-sm">

                <div class="border-b border-gray-200 px-5 py-4">

                    <h2 class="text-lg font-semibold text-gray-900">
                        Personal Information
                    </h2>

                    <p class="mt-1 text-sm text-gray-500">
                        Basic contact details on file.
                    </p>

                </div>


                <form
                    method="POST"
                    action="{{ route($profileRoutePrefix . '.update') }}"
                    data-profile-form="personal"
                >

                    @csrf
                    @method('PUT')

                    <div class="space-y-5 p-5">

                        {{-- First Name --}}
                        <div>

                            <label
                                for="first_name"
                                class="text-xs font-semibold uppercase tracking-wide text-gray-500"
                            >
                                First Name
                            </label>

                            <input
                                type="text"
                                id="first_name"
                                name="first_name"
                                value="{{ old('first_name', $user->first_name) }}"
                                required
                                class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-700 outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                            >

                        </div>


                        {{-- Last Name --}}
                        <div>

                            <label
                                for="last_name"
                                class="text-xs font-semibold uppercase tracking-wide text-gray-500"
                            >
                                Last Name
                            </label>

                            <input
                                type="text"
                                id="last_name"
                                name="last_name"
                                value="{{ old('last_name', $user->last_name) }}"
                                required
                                class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-700 outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                            >

                        </div>


                        {{-- Email --}}
                        <div>

                            <label
                                for="email"
                                class="text-xs font-semibold uppercase tracking-wide text-gray-500"
                            >
                                Email
                            </label>

                            <input
                                type="email"
                                id="email"
                                name="email"
                                value="{{ old('email', $user->email) }}"
                                required
                                data-profile-email
                                class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-700 outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                            >

                        </div>


                        {{-- Save --}}
                        <div>

                            <button
                                type="submit"
                                class="rounded-lg bg-[#11458c] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                            >
                                Save Changes
                            </button>

                        </div>

                    </div>

                </form>

            </div>


            {{-- =================================================
                EMPLOYMENT INFORMATION
            ================================================== --}}

            <div class="rounded-xl border border-gray-200 bg-white shadow-sm">

                <div class="border-b border-gray-200 px-5 py-4">

                    <h2 class="text-lg font-semibold text-gray-900">
                        Employment Information
                    </h2>

                    <p class="mt-1 text-sm text-gray-500">
                        Managed by your HR administrator.
                    </p>

                </div>


                <div class="px-5">

                    {{-- Employee ID --}}
                    <div class="flex items-center justify-between border-b border-gray-200 py-4">

                        <span class="text-sm text-gray-500">
                            Employee ID
                        </span>

                        <span class="text-sm font-medium text-gray-900">
                            {{ $employee?->id ? 'EMP-' . str_pad($employee->id, 2, '0', STR_PAD_LEFT) : '—' }}
                        </span>

                    </div>


                    {{-- Position --}}
                    <div class="flex items-center justify-between border-b border-gray-200 py-4">

                        <span class="text-sm text-gray-500">
                            Position
                        </span>

                        <span class="text-right text-sm font-medium text-gray-900">
                            {{ $employee?->position ?? '—' }}
                        </span>

                    </div>


                    {{-- Department --}}
                    <div class="flex items-center justify-between border-b border-gray-200 py-4">

                        <span class="text-sm text-gray-500">
                            Department
                        </span>

                        <span class="text-right text-sm font-medium text-gray-900">
                            {{ $employee?->department?->name ?? '—' }}
                        </span>

                    </div>


                    {{-- Hire Date --}}
                    <div class="flex items-center justify-between border-b border-gray-200 py-4">

                        <span class="text-sm text-gray-500">
                            Hire Date
                        </span>

                        <span class="text-sm font-medium text-gray-900">

                            @if ($employee?->hire_date)

                                {{ $employee->hire_date->format('F j, Y') }}

                            @else

                                —

                            @endif

                        </span>

                    </div>


                    {{-- Role --}}
                    <div class="flex items-center justify-between border-b border-gray-200 py-4">

                        <span class="text-sm text-gray-500">
                            Role
                        </span>

                        <span
                            class="rounded-full border border-blue-200 bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700"
                        >
                            {{ ucfirst($user->primaryRoleName() ?? '') }}
                        </span>

                    </div>


                    {{-- Reporting Manager --}}
                    <div class="flex items-center justify-between py-4">

                        <span class="text-sm text-gray-500">
                            Reporting Manager
                        </span>

                        <span class="text-right text-sm font-medium text-gray-900">

                            @if ($employee?->manager)

                                {{ $employee->manager->user->first_name }}
                                {{ $employee->manager->user->last_name }}

                            @else

                                —

                            @endif

                        </span>

                    </div>

                </div>

            </div>

        </div>


        {{-- =====================================================
            CHANGE PASSWORD
        ===================================================== --}}

        <div class="rounded-xl border border-gray-200 bg-white shadow-sm">

            <div class="border-b border-gray-200 px-5 py-4">

                <h2 class="text-lg font-semibold text-gray-900">
                    Change Password
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Update your account password to keep your account secure.
                </p>

            </div>


            <form
                method="POST"
                action="{{ route($profileRoutePrefix . '.password.update') }}"
                data-profile-form="password"
            >

                @csrf
                @method('PUT')


                <div class="space-y-5 p-5">

                    {{-- Current Password --}}
                    <div>

                        <label
                            for="current_password"
                            class="text-xs font-semibold uppercase tracking-wide text-gray-500"
                        >
                            Current Password
                        </label>

                        <div class="relative mt-2">

                            <input
                                type="password"
                                id="current_password"
                                name="current_password"
                                required
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 pr-20 text-sm text-gray-700 outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                            >

                            <button
                                type="button"
                                class="password-toggle absolute right-3 top-1/2 -translate-y-1/2 text-xs font-medium text-gray-500 hover:text-gray-700"
                                data-target="current_password"
                            >
                                Show
                            </button>

                        </div>

                    </div>


                    {{-- New Password --}}
                    <div>

                        <label
                            for="new_password"
                            class="text-xs font-semibold uppercase tracking-wide text-gray-500"
                        >
                            New Password
                        </label>

                        <div class="relative mt-2">

                            <input
                                type="password"
                                id="new_password"
                                name="new_password"
                                minlength="8"
                                required
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 pr-20 text-sm text-gray-700 outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                            >

                            <button
                                type="button"
                                class="password-toggle absolute right-3 top-1/2 -translate-y-1/2 text-xs font-medium text-gray-500 hover:text-gray-700"
                                data-target="new_password"
                            >
                                Show
                            </button>

                        </div>

                        <p class="mt-1 text-xs text-gray-500">
                            Password must contain at least 8 characters.
                        </p>

                    </div>


                    {{-- Confirm Password --}}
                    <div>

                        <label
                            for="new_password_confirmation"
                            class="text-xs font-semibold uppercase tracking-wide text-gray-500"
                        >
                            Confirm Password
                        </label>

                        <div class="relative mt-2">

                            <input
                                type="password"
                                id="new_password_confirmation"
                                name="new_password_confirmation"
                                minlength="8"
                                required
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 pr-20 text-sm text-gray-700 outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                            >

                            <button
                                type="button"
                                class="password-toggle absolute right-3 top-1/2 -translate-y-1/2 text-xs font-medium text-gray-500 hover:text-gray-700"
                                data-target="new_password_confirmation"
                            >
                                Show
                            </button>

                        </div>

                    </div>


                    {{-- Update Password --}}
                    <div>

                        <button
                            type="submit"
                            class="rounded-lg bg-[#11458c] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                        >
                            Update Password
                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>

@endsection


@section('scripts')

    @vite('resources/js/employee/profile.js')

@endsection
