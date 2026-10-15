<aside id="sidebar"
    class="fixed inset-y-0 left-0 z-40 flex w-72 flex-shrink-0 transform flex-col bg-[#0f2138] text-white transition-transform duration-200 ease-in-out -translate-x-full lg:static lg:translate-x-0">

    {{-- =====================================================
        LOGO / BRAND
    ====================================================== --}}
    <div class="flex items-center justify-between px-6 py-6">

        <div class="flex items-center gap-3">

            <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center">
                <img src="{{ asset('images/alot-logo.png') }}" alt="ALOT Logo" class="h-10 w-10 rounded-full object-cover">
            </div>

            <div>
                <p class="text-sm font-semibold leading-tight">
                    Attendance, Leave &amp; Overtime Tracker
                </p>

                <p class="text-xs text-slate-400">
                    Management System
                </p>
            </div>

        </div>

        {{-- Mobile close button --}}
        <button id="sidebar-close" type="button" class="text-slate-400 hover:text-white lg:hidden">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
        </button>

    </div>


    {{-- =====================================================
        MENU
    ====================================================== --}}
    <nav class="flex-1 overflow-y-auto px-4 py-2">

        @auth

            {{-- =================================================
                EMPLOYEE SIDEBAR
            ================================================== --}}
            @if (auth()->user()->isEmployee())
                <p class="mb-3 px-3 text-[11px] font-semibold uppercase tracking-widest text-slate-500">
                    Employee Menu
                </p>

                <ul class="space-y-1">

                    {{-- Dashboard --}}
                    <li>
                        <a href="{{ route('employee.dashboard') }}"
                            class="relative flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition
                            {{ request()->routeIs('employee.dashboard')
                                ? 'bg-slate-800/70 font-semibold text-white'
                                : 'text-slate-300 hover:bg-slate-800/50 hover:text-white' }}">

                            <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M4 5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v4a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V5Zm10 0a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v4a1 1 0 0 1-1 1h-4a1 1 0 0 1-1-1V5ZM4 15a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v4a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-4Zm10 0a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v4a1 1 0 0 1-1 1h-4a1 1 0 0 1-1-1v-4Z" />
                            </svg>

                            Dashboard

                            @if (request()->routeIs('employee.dashboard'))
                                <span
                                    class="absolute right-2 top-1/2 h-5 w-1 -translate-y-1/2 rounded-full bg-teal-400"></span>
                            @endif

                        </a>
                    </li>


                    {{-- Attendance --}}
                    <li>
                        <a href="{{ route('employee.attendance.index') }}"
                            class="relative flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition
                            {{ request()->routeIs('employee.attendance.*')
                                ? 'bg-slate-800/70 font-semibold text-white'
                                : 'text-slate-300 hover:bg-slate-800/50 hover:text-white' }}">

                            <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8"
                                viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="9" />
                                <path stroke-linecap="round" d="M12 7v5l3 2" />
                            </svg>

                            Attendance

                            @if (request()->routeIs('employee.attendance.*'))
                                <span
                                    class="absolute right-2 top-1/2 h-5 w-1 -translate-y-1/2 rounded-full bg-teal-400"></span>
                            @endif

                        </a>
                    </li>


                    {{-- My Leave --}}
                    <li>
                        <a href="{{ route('employee.leave.index') }}"
                            class="relative flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition
                            {{ request()->routeIs('employee.leave.*')
                                ? 'bg-slate-800/70 font-semibold text-white'
                                : 'text-slate-300 hover:bg-slate-800/50 hover:text-white' }}">

                            <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8"
                                viewBox="0 0 24 24">
                                <rect x="3" y="5" width="18" height="16" rx="2" />

                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 9h18M8 3v4M16 3v4" />
                            </svg>

                            My Leave

                            @if (request()->routeIs('employee.leave.*'))
                                <span
                                    class="absolute right-2 top-1/2 h-5 w-1 -translate-y-1/2 rounded-full bg-teal-400"></span>
                            @endif

                        </a>
                    </li>


                    {{-- My Overtime --}}
                    <li>
                        <a href="{{ route('employee.overtime.index') }}"
                            class="relative flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition
                            {{ request()->routeIs('employee.overtime.*')
                                ? 'bg-slate-800/70 font-semibold text-white'
                                : 'text-slate-300 hover:bg-slate-800/50 hover:text-white' }}">

                            <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8"
                                viewBox="0 0 24 24">
                                <circle cx="12" cy="13" r="7" />

                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v3M9 2h6" />
                            </svg>

                            My Overtime

                            @if (request()->routeIs('employee.overtime.*'))
                                <span
                                    class="absolute right-2 top-1/2 h-5 w-1 -translate-y-1/2 rounded-full bg-teal-400"></span>
                            @endif

                        </a>
                    </li>


                    {{-- Profile --}}
                    <li>
                        <a href="{{ route('employee.profile.index') }}"
                            class="relative flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition
                            {{ request()->routeIs('employee.profile.*')
                                ? 'bg-slate-800/70 font-semibold text-white'
                                : 'text-slate-300 hover:bg-slate-800/50 hover:text-white' }}">

                            <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8"
                                viewBox="0 0 24 24">
                                <circle cx="12" cy="8" r="4" />

                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 20c0-3.3 3.6-6 8-6s8 2.7 8 6" />
                            </svg>

                            Profile

                            @if (request()->routeIs('employee.profile.*'))
                                <span
                                    class="absolute right-2 top-1/2 h-5 w-1 -translate-y-1/2 rounded-full bg-teal-400"></span>
                            @endif

                        </a>
                    </li>

                </ul>


                {{-- =================================================
                MANAGER SIDEBAR
            ================================================== --}}
            @elseif (auth()->user()->isManager())
                <p class="mb-3 px-3 text-[11px] font-semibold uppercase tracking-widest text-slate-500">
                    Manager Menu
                </p>

                <ul class="space-y-1">

                    {{-- Dashboard --}}
                    <li>
                        <a href="{{ route('manager.dashboard') }}"
                            class="relative flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition
                            {{ request()->routeIs('manager.dashboard')
                                ? 'bg-slate-800/70 font-semibold text-white'
                                : 'text-slate-300 hover:bg-slate-800/50 hover:text-white' }}">

                            <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M4 5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v4a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V5Zm10 0a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v4a1 1 0 0 1-1 1h-4a1 1 0 0 1-1-1V5ZM4 15a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v4a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-4Zm10 0a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v4a1 1 0 0 1-1 1h-4a1 1 0 0 1-1-1v-4Z" />
                            </svg>

                            Dashboard

                            @if (request()->routeIs('manager.dashboard'))
                                <span
                                    class="absolute right-2 top-1/2 h-5 w-1 -translate-y-1/2 rounded-full bg-teal-400"></span>
                            @endif

                        </a>
                    </li>

                    <li>
                        <a href="{{ route('manager.attendance.index') }}"
                            class="relative flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition
        {{ request()->routeIs('manager.attendance.*')
            ? 'bg-slate-800/70 font-semibold text-white'
            : 'text-slate-300 hover:bg-slate-800/50 hover:text-white' }}">

                            <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8"
                                viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="9" />

                                <path stroke-linecap="round" d="M12 7v5l3 2" />
                            </svg>

                            Attendance

                            @if (request()->routeIs('manager.attendance.*'))
                                <span
                                    class="absolute right-2 top-1/2 h-5 w-1 -translate-y-1/2 rounded-full bg-teal-400"></span>
                            @endif

                        </a>
                    </li>


                    {{-- Leave Requests --}}
                    <li>
                        <a href="{{ route('manager.leave.index') }}"
                            class="relative flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition
                            {{ request()->routeIs('manager.leave.*')
                                ? 'bg-slate-800/70 font-semibold text-white'
                                : 'text-slate-300 hover:bg-slate-800/50 hover:text-white' }}">

                            <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8"
                                viewBox="0 0 24 24">
                                <rect x="3" y="5" width="18" height="16" rx="2" />

                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 9h18M8 3v4M16 3v4" />
                            </svg>

                            Leave Requests

                            @if (request()->routeIs('manager.leave.*'))
                                <span
                                    class="absolute right-2 top-1/2 h-5 w-1 -translate-y-1/2 rounded-full bg-teal-400"></span>
                            @endif

                        </a>
                    </li>


                    {{-- Overtime Requests --}}
                    <li>
                        <a href="{{ route('manager.overtime.index') }}"
                            class="relative flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition
                            {{ request()->routeIs('manager.overtime.*')
                                ? 'bg-slate-800/70 font-semibold text-white'
                                : 'text-slate-300 hover:bg-slate-800/50 hover:text-white' }}">

                            <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8"
                                viewBox="0 0 24 24">
                                <circle cx="12" cy="13" r="7" />

                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v3M9 2h6" />
                            </svg>

                            Overtime Requests

                            @if (request()->routeIs('manager.overtime.*'))
                                <span
                                    class="absolute right-2 top-1/2 h-5 w-1 -translate-y-1/2 rounded-full bg-teal-400"></span>
                            @endif

                        </a>
                    </li>


                    {{-- My Team --}}
                    <li>
                        <a href="{{ route('manager.team.index') }}"
                            class="relative flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition
                            {{ request()->routeIs('manager.team.*')
                                ? 'bg-slate-800/70 font-semibold text-white'
                                : 'text-slate-300 hover:bg-slate-800/50 hover:text-white' }}">

                            <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M16 20v-1a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v1M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM22 20v-1a4 4 0 0 0-3-3.87M16 3.13A4 4 0 0 1 16 11" />
                            </svg>

                            My Team

                            @if (request()->routeIs('manager.team.*'))
                                <span
                                    class="absolute right-2 top-1/2 h-5 w-1 -translate-y-1/2 rounded-full bg-teal-400"></span>
                            @endif

                        </a>
                    </li>


                    {{-- Profile --}}
                    <li>
                        <a href="{{ route('manager.profile.index') }}"
                            class="relative flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition
                            {{ request()->routeIs('manager.profile.index')
                                ? 'bg-slate-800/70 font-semibold text-white'
                                : 'text-slate-300 hover:bg-slate-800/50 hover:text-white' }}">

                            <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8"
                                viewBox="0 0 24 24">
                                <circle cx="12" cy="8" r="4" />

                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M4 20c0-3.3 3.6-6 8-6s8 2.7 8 6" />
                            </svg>

                            Profile

                            @if (request()->routeIs('manager.profile'))
                                <span
                                    class="absolute right-2 top-1/2 h-5 w-1 -translate-y-1/2 rounded-full bg-teal-400"></span>
                            @endif

                        </a>
                    </li>

                </ul>


                {{-- =================================================
                ADMIN SIDEBAR
            ================================================== --}}
            @elseif (auth()->user()->isAdmin())
                <p class="mb-3 px-3 text-[11px] font-semibold uppercase tracking-widest text-slate-500">
                    Admin Menu
                </p>

                <ul class="space-y-1">

                    {{-- Dashboard --}}
                    <li>
                        <a href="{{ route('admin.dashboard') }}"
                            class="relative flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition
                            {{ request()->routeIs('admin.dashboard')
                                ? 'bg-slate-800/70 font-semibold text-white'
                                : 'text-slate-300 hover:bg-slate-800/50 hover:text-white' }}">

                            <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M4 5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v4a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V5Zm10 0a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v4a1 1 0 0 1-1 1h-4a1 1 0 0 1-1-1V5ZM4 15a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v4a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-4Zm10 0a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v4a1 1 0 0 1-1 1h-4a1 1 0 0 1-1-1v-4Z" />
                            </svg>

                            Dashboard

                            @if (request()->routeIs('admin.dashboard'))
                                <span
                                    class="absolute right-2 top-1/2 h-5 w-1 -translate-y-1/2 rounded-full bg-teal-400"></span>
                            @endif

                        </a>
                    </li>


                    {{-- Employees --}}
                    <li>
                        <a href="{{ route('admin.employees.index') }}"
                            class="relative flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition
                            {{ request()->routeIs('admin.employees.*')
                                ? 'bg-slate-800/70 font-semibold text-white'
                                : 'text-slate-300 hover:bg-slate-800/50 hover:text-white' }}">

                            <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M16 20v-1a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v1M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM22 20v-1a4 4 0 0 0-3-3.87M16 3.13A4 4 0 0 1 16 11" />
                            </svg>

                            Employees

                            @if (request()->routeIs('admin.employees.*'))
                                <span
                                    class="absolute right-2 top-1/2 h-5 w-1 -translate-y-1/2 rounded-full bg-teal-400"></span>
                            @endif

                        </a>
                    </li>


                    {{-- Users --}}
                    <li>
                        <a href="{{ route('admin.users.index') }}"
                            class="relative flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition
                            {{ request()->routeIs('admin.users.*')
                                ? 'bg-slate-800/70 font-semibold text-white'
                                : 'text-slate-300 hover:bg-slate-800/50 hover:text-white' }}">

                            <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 3 5 6v5c0 4.4 3 8.4 7 9.5 4-1.1 7-5.1 7-9.5V6l-7-3Z" />
                            </svg>

                            Users

                            @if (request()->routeIs('admin.users.*'))
                                <span
                                    class="absolute right-2 top-1/2 h-5 w-1 -translate-y-1/2 rounded-full bg-teal-400"></span>
                            @endif

                        </a>
                    </li>


                    {{-- Departments --}}
                    <li>
                        <a href="{{ route('admin.departments.index') }}"
                            class="relative flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition
                            {{ request()->routeIs('admin.departments.*')
                                ? 'bg-slate-800/70 font-semibold text-white'
                                : 'text-slate-300 hover:bg-slate-800/50 hover:text-white' }}">

                            <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M4 21V6a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v15M16 21h4V10a1 1 0 0 0-1-1h-3M8 8h.01M12 8h.01M8 12h.01M12 12h.01M8 16h.01M12 16h.01" />
                            </svg>

                            Departments

                            @if (request()->routeIs('admin.departments.*'))
                                <span
                                    class="absolute right-2 top-1/2 h-5 w-1 -translate-y-1/2 rounded-full bg-teal-400"></span>
                            @endif

                        </a>
                    </li>


                    {{-- Attendance --}}
                    <li>
                        <a href="{{ route('admin.attendance.index') }}"
                            class="relative flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition
                            {{ request()->routeIs('admin.attendance.*')
                                ? 'bg-slate-800/70 font-semibold text-white'
                                : 'text-slate-300 hover:bg-slate-800/50 hover:text-white' }}">

                            <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8"
                                viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="9" />

                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 2" />
                            </svg>

                            Attendance

                            @if (request()->routeIs('admin.attendance.*'))
                                <span
                                    class="absolute right-2 top-1/2 h-5 w-1 -translate-y-1/2 rounded-full bg-teal-400"></span>
                            @endif

                        </a>
                    </li>


                    {{-- Leave Requests --}}
                    <li>
                        <a href="{{ route('admin.leave.index') }}"
                            class="relative flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition
                            {{ request()->routeIs('admin.leave.*')
                                ? 'bg-slate-800/70 font-semibold text-white'
                                : 'text-slate-300 hover:bg-slate-800/50 hover:text-white' }}">

                            <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8"
                                viewBox="0 0 24 24">
                                <rect x="3" y="5" width="18" height="16" rx="2" />

                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 9h18M8 3v4M16 3v4" />
                            </svg>

                            Leave Requests

                            @if (request()->routeIs('admin.leave.*'))
                                <span
                                    class="absolute right-2 top-1/2 h-5 w-1 -translate-y-1/2 rounded-full bg-teal-400"></span>
                            @endif

                        </a>
                    </li>


                    {{-- Overtime Requests --}}
                    <li>
                        <a href="{{ route('admin.overtime.index') }}"
                            class="relative flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition
                            {{ request()->routeIs('admin.overtime.*')
                                ? 'bg-slate-800/70 font-semibold text-white'
                                : 'text-slate-300 hover:bg-slate-800/50 hover:text-white' }}">

                            <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8"
                                viewBox="0 0 24 24">
                                <circle cx="12" cy="13" r="7" />

                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v3M9 2h6" />
                            </svg>

                            Overtime Requests

                        </a>
                    </li>


                    {{-- Profile --}}
                    <li>
                        <a href="{{ route('admin.profile.index') }}"
                            class="relative flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition
                        {{ request()->routeIs('admin.profile.*')
                            ? 'bg-slate-800/70 font-semibold text-white'
                            : 'text-slate-300 hover:bg-slate-800/50 hover:text-white' }}">

                            <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8"
                                viewBox="0 0 24 24">
                                <circle cx="12" cy="8" r="4" />

                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M4 20c0-3.3 3.6-6 8-6s8 2.7 8 6" />
                            </svg>

                            Profile

                        </a>
                    </li>

                </ul>
            @endif

        @endauth

    </nav>


    {{-- =====================================================
        LOGOUT
    ====================================================== --}}

    <div class="border-t border-slate-700/60 px-4 py-4">

        <form method="POST" action="{{ route('logout') }}">

            @csrf

            <button type="submit"
                class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-slate-300 transition hover:bg-slate-800/50 hover:text-white">

                <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M15 12H3m0 0 4-4m-4 4 4 4M11 4h6a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-6" />
                </svg>

                Sign out

            </button>

        </form>

    </div>

</aside>
