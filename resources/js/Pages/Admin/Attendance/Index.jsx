import React, { useState } from 'react';
import { AdminPage, Badge, DataTable, DateFilter, Filters, formatDate, matchesSearch, nameOf, Panel, Search, SelectFilter } from '../../../Components/Admin/Ui';

const statusOf = (row) => row.time_in ? (row.time_out ? 'completed' : 'working') : 'not_started';

function formatTime(value, timezone) {
    if (!value) return 'N/A';
    const date = new Date(value);
    return Number.isNaN(date.getTime()) ? 'N/A' : date.toLocaleTimeString('en-US', { timeZone: timezone, hour: 'numeric', minute: '2-digit' });
}

function totalHours(row) {
    if (!row.time_in || !row.time_out) return 'N/A';
    const minutes = Math.max(0, Math.floor((new Date(row.time_out) - new Date(row.time_in)) / 60000));
    if (!Number.isFinite(minutes)) return 'N/A';
    return `${Math.floor(minutes / 60)}h ${String(minutes % 60).padStart(2, '0')}m`;
}

export default function Index({ attendances, departments, timezone }) {
    const [search, setSearch] = useState('');
    const [department, setDepartment] = useState('');
    const [date, setDate] = useState('');
    const [status, setStatus] = useState('');
    const rows = attendances.filter((row) =>
        matchesSearch(search, nameOf(row.employee?.user), row.employee?.user?.email, row.employee?.department?.name)
        && (!department || String(row.employee?.department_id) === department)
        && (!date || row.date === date)
        && (!status || statusOf(row) === status));

    return <AdminPage title="Attendance Management" description="Attendance records for all employees in the organization.">
        <Panel title="Attendance Records" description="Search and filter employee attendance records.">
            <Filters>
                <Search value={search} onChange={setSearch} placeholder="Search employee or email" />
                <SelectFilter label="All departments" value={department} onChange={setDepartment} options={departments.map((item) => [item.id, item.name])} />
                <DateFilter value={date} onChange={setDate} />
                <SelectFilter label="All statuses" value={status} onChange={setStatus} options={[
                    ['working', 'Working'], ['completed', 'Completed'], ['not_started', 'Not started'],
                ]} />
            </Filters>
            <DataTable key={`${search}:${department}:${date}:${status}`} rows={rows} empty="No attendance records match your filters." columns={[
                { key: 'employee', label: 'Employee', render: (row) => <div><p className="font-semibold">{nameOf(row.employee?.user)}</p><p className="mt-1 text-xs text-gray-500">{row.employee?.user?.email}</p></div> },
                { key: 'department', label: 'Department', render: (row) => row.employee?.department?.name || 'N/A' },
                { key: 'date', label: 'Date', render: (row) => formatDate(row.date) },
                { key: 'time_in', label: 'Time in', render: (row) => formatTime(row.time_in, timezone) },
                { key: 'time_out', label: 'Time out', render: (row) => formatTime(row.time_out, timezone) },
                { key: 'hours', label: 'Total hours', render: totalHours },
                { key: 'status', label: 'Status', render: (row) => <Badge value={statusOf(row).replace('_', ' ')} /> },
            ]} />
        </Panel>
    </AdminPage>;
}
