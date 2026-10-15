import React, { useEffect, useState } from 'react';
import axios from 'axios';
import AppLayout from '../../../Layouts/AppLayout';
import { PageHeader, Badge, Button, DataTable, DateFilter, Filters, formatDate, Panel, Search, SelectFilter } from '../../../Components/Admin/Ui';

function formatTime(value, timezone) {
    if (!value) return 'N/A';
    const date = new Date(value);
    return Number.isNaN(date.getTime()) ? 'N/A' : date.toLocaleTimeString('en-US', { timeZone: timezone, hour: 'numeric', minute: '2-digit' });
}

function totalHours(row) {
    if (!row.time_in || !row.time_out) return 'N/A';
    const minutes = Math.max(0, Math.floor((new Date(row.time_out) - new Date(row.time_in)) / 60000));
    return Number.isFinite(minutes) ? `${Math.floor(minutes / 60)}h ${String(minutes % 60).padStart(2, '0')}m` : 'N/A';
}

export default function Index({ filters: initialFilters, dataUrl, timezone }) {
    const [filters, setFilters] = useState({ search: '', date: '', status: '', ...initialFilters });
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
                    params: { ...filters, page }, signal: controller.signal,
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
    }, [dataUrl, filters, page, retry]);

    function filter(name, value) {
        setFilters((previous) => ({ ...previous, [name]: value }));
        setPage(1);
    }

    return <AppLayout title="Attendance" breadcrumbParent="Employee" breadcrumbCurrent="Attendance">
        <PageHeader title="Attendance" description="Review your personal attendance history." />
        <Panel title="Attendance History" description="Your daily time in, time out, and total hours.">
            <Filters>
                <Search value={filters.search} onChange={(value) => filter('search', value)} placeholder="Search date (YYYY-MM-DD or Oct 15, 2026)" />
                <DateFilter value={filters.date} onChange={(value) => filter('date', value)} />
                <SelectFilter label="All statuses" value={filters.status} onChange={(value) => filter('status', value)} options={[
                    ['working', 'Working'], ['completed', 'Completed'], ['not_started', 'Not started'],
                ]} />
            </Filters>
            {loading ? <p role="status" className="p-5 text-sm text-gray-500">Loading attendance...</p>
                : error ? <div role="alert" className="space-y-3 p-5 text-sm text-red-700"><p>{error}</p>
                    <Button variant="secondary" onClick={() => setRetry((value) => value + 1)}>Try again</Button></div>
                    : <DataTable rows={result.attendances} empty="No attendance records match your filters."
                        pagination={result.pagination} onPageChange={setPage} paginationLabel="Attendance history pagination" columns={[
                            { key: 'date', label: 'Date', render: (row) => formatDate(row.date) },
                            { key: 'time_in', label: 'Time in', render: (row) => formatTime(row.time_in, timezone) },
                            { key: 'time_out', label: 'Time out', render: (row) => formatTime(row.time_out, timezone) },
                            { key: 'hours', label: 'Total hours', render: totalHours },
                            { key: 'status', label: 'Status', render: (row) => <Badge value={row.time_in ? (row.time_out ? 'completed' : 'working') : 'not started'} /> },
                        ]} />}
        </Panel>
    </AppLayout>;
}
