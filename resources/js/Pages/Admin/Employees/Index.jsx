import React, { useState } from 'react';
import EmployeeEditor from '../../../Components/Admin/EmployeeEditor';
import { AdminPage, Badge, Button, ConfirmAction, DataTable, Details, Dialog, Filters, formatDate, matchesSearch, nameOf, Panel, refreshPage, Search, SelectFilter } from '../../../Components/Admin/Ui';

export default function Index({ employees, departments, managers, actionUrl }) {
    const [search, setSearch] = useState('');
    const [department, setDepartment] = useState('');
    const [manager, setManager] = useState('');
    const [dialog, setDialog] = useState(null);
    const [notice, setNotice] = useState('');
    const close = () => setDialog(null);
    const saved = (message) => { close(); setNotice(message); refreshPage(); };
    const rows = employees.filter((employee) =>
        matchesSearch(search, `EMP-${String(employee.id).padStart(2, '0')}`, nameOf(employee.user), employee.user?.email, employee.position, employee.department?.name)
        && (!department || String(employee.department_id) === department)
        && (!manager || String(employee.manager_id) === manager));
    const person = dialog?.person;

    return <AdminPage title="Employee Management" description="All employee records across the organization." notice={notice}
        action={<Button onClick={() => setDialog({ type: 'edit' })}>Add employee</Button>}>
        <Panel title="Employee Records">
            <Filters>
                <Search value={search} onChange={setSearch} placeholder="Search name, email or employee ID" />
                <SelectFilter label="All departments" value={department} onChange={setDepartment} options={departments.map((item) => [item.id, item.name])} />
                <SelectFilter label="All managers" value={manager} onChange={setManager} options={managers.map((item) => [item.id, nameOf(item.user)])} />
            </Filters>
            <DataTable key={`${search}:${department}:${manager}`} rows={rows} empty="No employees match your filters." columns={[
                { key: 'id', label: 'Employee ID', render: (row) => `EMP-${String(row.id).padStart(2, '0')}` },
                { key: 'name', label: 'Employee', render: (row) => <div><p className="font-semibold">{nameOf(row.user)}</p><p className="mt-1 text-xs text-gray-500">{row.user?.email}</p></div> },
                { key: 'position', label: 'Position' },
                { key: 'department', label: 'Department', render: (row) => row.department?.name || 'N/A' },
                { key: 'manager', label: 'Manager', render: (row) => nameOf(row.manager?.user) },
                { key: 'hire_date', label: 'Hire date', render: (row) => formatDate(row.hire_date) },
                { key: 'status', label: 'Status', render: (row) => <Badge value={row.user?.status} /> },
                { key: 'actions', label: 'Actions', render: (row) => <div className="flex flex-wrap gap-2">
                    <Button variant="secondary" onClick={() => setDialog({ type: 'view', person: row })}>View</Button>
                    <Button variant="secondary" onClick={() => setDialog({ type: 'edit', person: row })}>Edit</Button>
                    <Button variant="danger" onClick={() => setDialog({ type: 'delete', person: row })}>Delete</Button>
                </div> },
            ]} />
        </Panel>
        {dialog?.type === 'edit' && <EmployeeEditor key={person?.id || 'new'} employee={person} departments={departments} managers={managers} actionUrl={actionUrl} onClose={close} onSaved={() => saved(person ? 'Employee updated successfully.' : 'Employee created successfully.')} />}
        {dialog?.type === 'view' && <Dialog title="Employee Details" onClose={close}>
            <Details items={[
                ['Employee ID', `EMP-${String(person.id).padStart(2, '0')}`],
                ['Name', nameOf(person.user)], ['Email', person.user?.email], ['Role', person.user?.role],
                ['Status', <Badge value={person.user?.status} />], ['Department', person.department?.name],
                ['Manager', nameOf(person.manager?.user)], ['Position', person.position], ['Hire date', formatDate(person.hire_date)],
            ]} />
        </Dialog>}
        {dialog?.type === 'delete' && <ConfirmAction title={`Delete ${nameOf(person.user)}?`} description="This will permanently delete the employee and their user account." url={`${actionUrl}/${person.id}`} onClose={close} onSaved={() => saved('Employee deleted successfully.')} />}
    </AdminPage>;
}
