<div class="overflow-x-auto">

    <table class="min-w-full divide-y divide-gray-200">

        <thead class="bg-gray-50">
            <tr>

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


        <tbody class="divide-y divide-gray-200 bg-white">

            @forelse ($attendances as $attendance)

                @php

                    if ($attendance->time_in && $attendance->time_out) {
                        $attendanceStatus = 'completed';
                    } elseif ($attendance->time_in && !$attendance->time_out) {
                        $attendanceStatus = 'working';
                    } else {
                        $attendanceStatus = 'not_started';
                    }

                @endphp


                <tr>

                    <td class="whitespace-nowrap px-5 py-3 text-sm font-medium text-gray-900 text-center">
                        {{ $attendance->date->format('M j, Y') }}
                    </td>


                    <td class="whitespace-nowrap px-5 py-3 text-sm text-gray-700 text-center">
                        {{ $attendance->time_in?->format('h:i A') ?? '—' }}
                    </td>


                    <td class="whitespace-nowrap px-5 py-3 text-sm text-gray-700 text-center">
                        {{ $attendance->time_out?->format('h:i A') ?? '—' }}
                    </td>


                    <td class="whitespace-nowrap px-5 py-3 text-sm text-gray-700 text-center">

                        @if ($attendance->time_in && $attendance->time_out)

                            @php
                                $totalMinutes = (int) $attendance->time_in->diffInMinutes($attendance->time_out);
                                $hours = intdiv($totalMinutes, 60);
                                $minutes = $totalMinutes % 60;
                            @endphp

                            {{ $hours }}h {{ str_pad($minutes, 2, '0', STR_PAD_LEFT) }}m

                        @else

                            —

                        @endif

                    </td>


                    <td class="whitespace-nowrap px-5 py-3 text-center">

                        @if ($attendanceStatus === 'completed')

                            <span class="inline-flex items-center gap-1.5 rounded-full bg-green-50 px-2.5 py-1 text-xs font-semibold text-green-700">
                                <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>
                                Completed
                            </span>

                        @elseif ($attendanceStatus === 'working')

                            <span class="inline-flex items-center gap-1.5 rounded-full bg-cyan-50 px-2.5 py-1 text-xs font-semibold text-cyan-700">
                                <span class="h-1.5 w-1.5 rounded-full bg-cyan-500"></span>
                                Working
                            </span>

                        @endif

                    </td>

                </tr>

            @empty

                <tr>

                    <td
                        colspan="5"
                        class="px-5 py-10 text-center text-sm text-gray-500"
                    >
                        No attendance records found.
                    </td>

                </tr>

            @endforelse

        </tbody>

    </table>

</div>


@if ($attendances->hasPages())

    <div class="border-t border-gray-200 px-5 py-4">
        {{ $attendances->withQueryString()->links() }}
    </div>

@endif
