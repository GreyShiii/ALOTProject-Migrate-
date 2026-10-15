import React from 'react';
import { AdminPage, Badge, DataTable, formatDate, nameOf, Panel } from '../../Components/Admin/Ui';

export default function Dashboard({ totalEmployees, totalManagers, totalDepartments, workingEmployees, completedEmployees, employeesOnLeave, recentEmployees, recentRequests }) {
    return <AdminPage title="Admin Dashboard" description="Overview of employees, departments, attendance, and requests.">
        <div className="grid grid-cols-1 gap-4 sm:grid-cols-3">
            {[['Total Employees', totalEmployees], ['Total Managers', totalManagers], ['Total Departments', totalDepartments]].map(([label, value]) =>
                <div key={label} className="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <p className="text-sm font-medium text-gray-500">{label}</p><p className="mt-2 text-3xl font-bold">{value}</p>
                </div>)}
        </div>
        <Panel title="Attendance Overview" description="Today's attendance across the organization.">
            <div className="grid grid-cols-1 gap-4 p-5 sm:grid-cols-3">
                {[['Working', workingEmployees, 'bg-blue-50 text-blue-700'], ['Completed', completedEmployees, 'bg-green-50 text-green-700'], ['On Leave', employeesOnLeave, 'bg-amber-50 text-amber-700']].map(([label, value, color]) =>
                    <div key={label} className={`rounded-lg p-4 ${color}`}><p className="text-sm font-medium">{label}</p><p className="mt-2 text-3xl font-bold">{value}</p></div>)}
            </div>
        </Panel>
        <Panel title="Recent Requests" description="Latest leave and overtime requests.">
            <DataTable rows={recentRequests} empty="No recent requests." columns={[
                { key: 'employee', label: 'Employee', render: (row) => <div>
                    <p className="font-semibold">{nameOf(row.employee?.user)}</p>
                    <p className="mt-1 text-xs text-gray-500">{row.employee?.department?.name || 'N/A'}</p>
                </div> },
                { key: 'type', label: 'Type', render: (row) => <span className={`inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ${row.type === 'Leave' ? 'bg-blue-50 text-blue-700' : 'bg-purple-50 text-purple-700'}`}>{row.type}</span> },
                { key: 'date', label: 'Date', render: (row) => formatDate(row.date) },
                { key: 'status', label: 'Status', render: (row) => <Badge value={row.status} /> },
            ]} />
        </Panel>
        <Panel title="Recent Employees" description="Recently added employees.">
            <DataTable rows={recentEmployees} empty="No employees yet." columns={[
                { key: 'employee', label: 'Employee', render: (row) => nameOf(row.user) },
                { key: 'position', label: 'Position' },
                { key: 'department', label: 'Department', render: (row) => row.department?.name || 'N/A' },
                { key: 'hire_date', label: 'Hire date', render: (row) => formatDate(row.hire_date) },
            ]} />
        </Panel>
    </AdminPage>;
}
