<header class="bg-white border-b border-gray-200 px-4 sm:px-8 py-4 flex items-center justify-between flex-shrink-0">

    <div class="flex items-center gap-4">
        <button id="sidebar-open" class="lg:hidden text-gray-600 hover:text-gray-900">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>

        <div class="flex items-center gap-2 text-sm">
            <span class="text-gray-400 hidden sm:inline">@yield('breadcrumb-parent', ucfirst(auth()->user()->primaryRoleName() ?? ''))</span>
            <svg class="w-4 h-4 text-gray-300 hidden sm:inline" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m9 6 6 6-6 6"/></svg>
            <span class="text-gray-900 font-semibold">@yield('breadcrumb-current', 'Dashboard')</span>
        </div>
    </div>

    <div class="flex items-center gap-3 sm:gap-5">
        @auth
            @php
                $user = auth()->user();
                $initials = strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1));
                $position = $user->employee->position ?? ucfirst($user->primaryRoleName() ?? '');
            @endphp

            <div class="flex items-center gap-3 border border-gray-200 rounded-full py-1.5 pl-1.5 pr-4">
                <div class="w-9 h-9 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-xs font-semibold flex-shrink-0">
                    {{ $initials }}
                </div>
                <div class="text-sm hidden sm:block">
                    <p class="font-semibold text-gray-900 leading-tight">{{ $user->first_name }} {{ $user->last_name }}</p>
                    <p class="text-gray-500 text-xs">{{ $position }}</p>
                </div>
            </div>
        @endauth
    </div>

</header>
