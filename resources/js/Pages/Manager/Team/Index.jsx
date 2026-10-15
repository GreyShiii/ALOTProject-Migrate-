import React, { useState } from 'react';
import ManagerLayout from '../../../Layouts/ManagerLayout';
import { Badge, DataTable, Filters, formatDate, matchesSearch, nameOf, Panel, Search, SelectFilter } from '../../../Components/Admin/Ui';

export default function TeamIndex({ employees = [] }) {
    const [search, setSearch] = useState('');
    const [status, setStatus] = useState('');
    const rows = employees.filter((employee) => matchesSearch(search, nameOf(employee.user), employee.user?.email, employee.position, employee.department?.name)
        && (!status || employee.user?.status === status));

    return <ManagerLayout title="My Team" breadcrumbParent="Manager" breadcrumbCurrent="My Team">
        <div className="mb-8 flex flex-wrap items-end justify-between gap-3">
            <div><h1 className="text-2xl font-semibold tracking-tight sm:text-3xl">My Team</h1>
                <p className="mt-2 text-sm text-gray-500">Find contact details and employment information for your assigned employees.</p></div>
            <p className="text-sm text-gray-600"><span className="font-semibold tabular-nums text-gray-900">{employees.length}</span> team members</p>
        </div>
        <Panel title="Team members" description="Search by name, email, position or department.">
            <Filters>
                <Search value={search} onChange={setSearch} placeholder="Search your team" />
                <SelectFilter label="All statuses" value={status} onChange={setStatus} options={[
                    ['active', 'Active'], ['inactive', 'Inactive'],
                ]} />
            </Filters>
            <DataTable key={search + ':' + status} rows={rows} paginationLabel="Team pagination"
                empty={employees.length ? 'No team members match. Change or clear your filters.' : 'You have no assigned employees yet.'}
                columns={[
                    { key: 'name', label: 'Employee', render: (row) => <div><p className="font-semibold text-gray-900">{nameOf(row.user)}</p><p className="text-xs text-gray-500">{row.user?.email || 'No email provided'}</p></div> },
                    { key: 'position', label: 'Position' },
                    { key: 'department', label: 'Department', render: (row) => row.department?.name || 'N/A' },
                    { key: 'hire_date', label: 'Hire date', render: (row) => formatDate(row.hire_date) },
                    { key: 'status', label: 'Status', render: (row) => <Badge value={row.user?.status} /> },
                ]} />
        </Panel>
    </ManagerLayout>;
}
