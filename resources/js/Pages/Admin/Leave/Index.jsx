import React, { useState } from 'react';
import { AdminPage, Badge, Button, DataTable, DateFilter, Details, Dialog, Filters, formatDate, matchesSearch, nameOf, Panel, Search, SelectFilter } from '../../../Components/Admin/Ui';

function leaveDays(row) {
    return Math.round((Date.parse(row.end_date) - Date.parse(row.start_date)) / 86400000) + 1;
}

export default function Index({ leaveRequests, departments }) {
    const [search, setSearch] = useState('');
    const [department, setDepartment] = useState('');
    const [status, setStatus] = useState('');
    const [date, setDate] = useState('');
    const [selected, setSelected] = useState(null);
    const rows = leaveRequests.filter((row) =>
        matchesSearch(search, nameOf(row.employee?.user), row.employee?.user?.email, row.employee?.position, row.employee?.department?.name, row.leave_type, row.reason)
        && (!department || String(row.employee?.department_id) === department)
        && (!status || row.status?.toLowerCase() === status)
        && (!date || (date >= row.start_date && date <= row.end_date)));

    return <AdminPage title="Leave Requests" description="Monitor leave requests across the organization.">
        <Panel title="All Leave Requests" description="View employee leave requests and their current status.">
            <Filters>
                <Search value={search} onChange={setSearch} placeholder="Search employee, type or reason" />
                <SelectFilter label="All statuses" value={status} onChange={setStatus} options={[
                    ['pending', 'Pending'], ['approved', 'Approved'], ['rejected', 'Rejected'],
                ]} />
                <SelectFilter label="All departments" value={department} onChange={setDepartment} options={departments.map((item) => [item.id, item.name])} />
                <DateFilter value={date} onChange={setDate} />
            </Filters>
            <DataTable key={`${search}:${department}:${status}:${date}`} rows={rows} empty="No leave requests match your filters." columns={[
                { key: 'employee', label: 'Employee', render: (row) => nameOf(row.employee?.user) },
                { key: 'department', label: 'Department', render: (row) => row.employee?.department?.name || 'N/A' },
                { key: 'leave_type', label: 'Type' },
                { key: 'dates', label: 'Dates', render: (row) => `${formatDate(row.start_date)} – ${formatDate(row.end_date)}` },
                { key: 'days', label: 'Days', render: leaveDays },
                { key: 'reason', label: 'Reason' },
                { key: 'status', label: 'Status', render: (row) => <Badge value={row.status} /> },
                { key: 'submitted', label: 'Submitted', render: (row) => formatDate(row.created_at) },
                { key: 'actions', label: 'Actions', render: (row) => <Button variant="secondary" onClick={() => setSelected(row)}>View details</Button> },
            ]} />
        </Panel>
        {selected && <Dialog title="Leave Request Details" onClose={() => setSelected(null)}>
            <Details items={[
                ['Employee', nameOf(selected.employee?.user)], ['Email', selected.employee?.user?.email],
                ['Department', selected.employee?.department?.name], ['Position', selected.employee?.position],
                ['Leave type', selected.leave_type], ['Start date', formatDate(selected.start_date)],
                ['End date', formatDate(selected.end_date)], ['Days', leaveDays(selected)],
                ['Reason', selected.reason || 'No reason provided.'], ['Status', <Badge value={selected.status} />],
                ['Submitted', formatDate(selected.created_at)], ['Reviewed by', nameOf(selected.approver)],
                ['Review date', formatDate(selected.approval_date)], ['Rejection reason', selected.rejection_reason || 'N/A'],
            ]} />
        </Dialog>}
    </AdminPage>;
}
