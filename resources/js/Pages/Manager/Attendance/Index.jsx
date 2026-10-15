import React, { useEffect, useState } from 'react';
import { router } from '@inertiajs/react';
import axios from 'axios';
import ManagerLayout from '../../../Layouts/ManagerLayout';
import useApiForm from '../../../Components/Admin/useApiForm';
import { Badge, Button, DataTable, DateFilter, Filters, FormErrors, formatDate, nameOf, Panel, Search, SelectFilter } from '../../../Components/Admin/Ui';

const statusOf = (row) => row?.time_in ? (row.time_out ? 'completed' : 'working') : 'not_started';
const statusLabel = (row) => statusOf(row).replace('_', ' ');

function formatTime(value, timezone) {
    if (!value) return 'N/A';
    const date = new Date(value);
    return Number.isNaN(date.getTime()) ? 'N/A' : date.toLocaleTimeString('en-US', { timeZone: timezone, hour: 'numeric', minute: '2-digit' });
}

function totalHours(row) {
    if (!row?.time_in || !row.time_out) return 'N/A';
    const minutes = Math.max(0, Math.floor((new Date(row.time_out) - new Date(row.time_in)) / 60000));
    return Number.isFinite(minutes) ? `${Math.floor(minutes / 60)}h ${String(minutes % 60).padStart(2, '0')}m` : 'N/A';
}

function AttendanceHistory({ scope, dataUrl, timezone, refreshKey }) {
    const mine = scope === 'mine';
    const title = mine ? 'My Attendance History' : 'Team Attendance';
    const [filters, setFilters] = useState({ search: '', date: '', status: '' });
    const [page, setPage] = useState(1);
    const [result, setResult] = useState({ attendances: [], pagination: null });
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');
    const [retry, setRetry] = useState(0);

    useEffect(() => {
        const controller = new AbortController();
        setLoading(true);
        setError('');
        const timer = setTimeout(async () => {
            try {
                const response = await axios.get(dataUrl, {
                    params: { scope, ...filters, page },
                    signal: controller.signal,
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (!controller.signal.aborted) setResult(response.data);
            } catch (requestError) {
                if (!controller.signal.aborted) setError(requestError.response?.data?.message || 'Unable to load attendance. Please try again.');
            } finally {
                if (!controller.signal.aborted) setLoading(false);
            }
        }, 250);
        return () => { clearTimeout(timer); controller.abort(); };
    }, [dataUrl, scope, filters, page, refreshKey, retry]);

    function filter(name, value) {
        setFilters((previous) => ({ ...previous, [name]: value }));
        setPage(1);
    }

    const columns = [
        ...(!mine ? [
            { key: 'employee', label: 'Employee', render: (row) => <div><p className="font-semibold">{nameOf(row.employee?.user)}</p><p className="text-xs text-gray-500">{row.employee?.user?.email}</p></div> },
            { key: 'department', label: 'Department', render: (row) => row.employee?.department?.name || 'N/A' },
        ] : []),
        { key: 'date', label: 'Date', render: (row) => formatDate(row.date) },
        { key: 'time_in', label: 'Time in', render: (row) => formatTime(row.time_in, timezone) },
        { key: 'time_out', label: 'Time out', render: (row) => formatTime(row.time_out, timezone) },
        ...(mine ? [{ key: 'hours', label: 'Total hours', render: totalHours }] : []),
        { key: 'status', label: 'Status', render: (row) => <Badge value={statusLabel(row)} /> },
    ];

    return <Panel title={title} description={mine ? 'View your previous attendance records.' : 'Attendance records for employees directly assigned to you.'}>
        <Filters>
            <Search value={filters.search} onChange={(value) => filter('search', value)} placeholder={mine ? 'Search date (YYYY-MM-DD)' : 'Search employee or email'} />
            <DateFilter value={filters.date} onChange={(value) => filter('date', value)} />
            <SelectFilter label="All statuses" value={filters.status} onChange={(value) => filter('status', value)} options={[
                ['working', 'Working'], ['completed', 'Completed'], ['not_started', 'Not started'],
            ]} />
        </Filters>
        {loading ? <p role="status" className="p-5 text-sm text-gray-500">Loading attendance...</p>
            : error ? <div role="alert" className="space-y-3 p-5 text-sm text-red-700"><p>{error}</p><Button variant="secondary" onClick={() => setRetry(retry + 1)}>Try again</Button></div>
                : <DataTable rows={result.attendances} columns={columns} empty="No attendance records match your filters."
                    pagination={result.pagination} onPageChange={setPage} paginationLabel={`${title} pagination`} />}
    </Panel>;
}

export default function Index({ todayAttendance, todayLabel, timezone, links }) {
    const form = useApiForm();
    const [notice, setNotice] = useState('');
    const [refreshing, setRefreshing] = useState(false);
    const [refreshKey, setRefreshKey] = useState(0);
    const busy = form.processing || refreshing;
    const status = statusOf(todayAttendance);

    async function recordAttendance() {
        const timeIn = status === 'not_started';
        if (await form.submit('post', timeIn ? links.timeIn : links.timeOut)) {
            setNotice(timeIn ? 'Time in recorded successfully.' : 'Time out recorded successfully.');
            setRefreshing(true);
            router.reload({
                only: ['todayAttendance', 'todayLabel'],
                preserveScroll: true,
                onSuccess: () => setRefreshKey((value) => value + 1),
                onFinish: () => setRefreshing(false),
            });
        }
    }

    return <ManagerLayout title="Attendance" breadcrumbParent="Manager" breadcrumbCurrent="Attendance">
        <div className="mb-6"><h1 className="text-xl font-bold tracking-tight sm:text-2xl">Attendance</h1>
            <p className="mt-1 text-sm text-gray-500">Record your attendance and monitor your team's attendance.</p></div>
        {notice && <div role="status" className="mb-5 rounded-lg border border-green-200 bg-green-50 p-3 text-sm text-green-800">{notice}</div>}
        <FormErrors form={form} />
        <div className="space-y-6">
            <section aria-label="Today's attendance" className="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <div className="bg-gradient-to-r from-[#11458c] to-teal-600 px-5 py-6 text-white">
                    <p className="text-sm font-semibold uppercase tracking-wide">Today's Attendance</p><h2 className="mt-2 text-2xl font-bold sm:text-3xl">{todayLabel}</h2>
                </div>
                <div className="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2 xl:grid-cols-4">
                    {[
                        ['Status', <Badge value={statusLabel(todayAttendance)} />], ['Time in', formatTime(todayAttendance?.time_in, timezone)],
                        ['Time out', formatTime(todayAttendance?.time_out, timezone)], ['Total hours', totalHours(todayAttendance)],
                    ].map(([label, value]) => <div key={label} className="rounded-xl border border-gray-200 bg-gray-50 p-4">
                        <p className="text-xs font-semibold uppercase tracking-wide text-gray-500">{label}</p><div className="mt-2 text-lg font-semibold">{value}</div>
                    </div>)}
                </div>
                <div className="flex justify-end border-t border-gray-200 px-5 py-4">
                    {status === 'completed' ? <p className="text-sm text-gray-500">Your attendance for today is complete.</p>
                        : <Button disabled={busy} variant={status === 'working' ? 'danger' : 'primary'} onClick={recordAttendance}>
                            {busy ? 'Recording...' : status === 'working' ? 'Time out' : 'Time in'}
                        </Button>}
                </div>
            </section>
            <AttendanceHistory scope="mine" dataUrl={links.data} timezone={timezone} refreshKey={refreshKey} />
            <AttendanceHistory scope="team" dataUrl={links.data} timezone={timezone} refreshKey={refreshKey} />
        </div>
    </ManagerLayout>;
}
