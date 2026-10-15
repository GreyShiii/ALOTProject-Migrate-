import React from 'react';
import { Head } from '@inertiajs/react';
import { useState } from 'react';
import ManagerLayout from '../../../Layouts/ManagerLayout';

const pageSize = 10;

function formatDate(value) {
    if (!value) return 'N/A';

    const date = new Date(`${value}T00:00:00`);
    return Number.isNaN(date.getTime())
        ? 'N/A'
        : new Intl.DateTimeFormat('en-US', { month: 'short', day: 'numeric', year: 'numeric' }).format(date);
}

function StatusBadge({ status }) {
    const active = status === 'active';

    return (
        <span className={`inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ${active ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-600'}`}>
            {active ? 'Active' : 'Inactive'}
        </span>
    );
}

function EmployeeName({ employee }) {
    const user = employee.user;
    return user ? `${user.first_name} ${user.last_name}`.trim() : 'Unknown Employee';
}

function EmployeeDetails({ employee }) {
    const user = employee.user;

    return (
        <>
            <div className="min-w-0">
                <p className="truncate text-sm font-semibold text-gray-900"><EmployeeName employee={employee} /></p>
                <p className="mt-1 truncate text-xs text-gray-500">{employee.position || '—'}</p>
            </div>
            <div className="mt-3 grid grid-cols-2 gap-3 text-sm">
                <div><p className="text-xs font-semibold uppercase tracking-wide text-gray-400">Department</p><p className="truncate text-gray-700">{employee.department?.name || '—'}</p></div>
                <div><p className="text-xs font-semibold uppercase tracking-wide text-gray-400">Hire date</p><p className="text-gray-700">{formatDate(employee.hire_date)}</p></div>
                <div className="col-span-2"><p className="text-xs font-semibold uppercase tracking-wide text-gray-400">Email</p><p className="break-all text-gray-700">{user?.email || '—'}</p></div>
            </div>
        </>
    );
}

export default function Index({ employees }) {
    const [currentPage, setCurrentPage] = useState(1);
    const totalPages = Math.ceil(employees.length / pageSize);
    const safePage = Math.min(currentPage, Math.max(totalPages, 1));
    const pageEmployees = employees.slice((safePage - 1) * pageSize, safePage * pageSize);

    return (
        <ManagerLayout>
            <Head title="My Team" />
            <div className="mb-6">
                <h1 className="text-xl font-bold tracking-tight sm:text-2xl">My Team</h1>
                <p className="mt-1 text-sm text-gray-500">Employees assigned to you.</p>
            </div>

            <section className="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm" aria-labelledby="team-heading">
                <div className="border-b border-gray-200 px-5 py-4">
                    <h2 id="team-heading" className="text-lg font-semibold">Team Members</h2>
                    <p className="mt-1 text-sm text-gray-500">Your currently assigned employees.</p>
                </div>

                {pageEmployees.length > 0 ? (
                    <>
                        <div className="hidden overflow-x-auto md:block">
                            <table className="w-full min-w-[850px] divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>{['Employee', 'Position', 'Department', 'Email', 'Hire date', 'Status'].map((label) => <th key={label} scope="col" className="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">{label}</th>)}</tr>
                                </thead>
                                <tbody className="divide-y divide-gray-200">
                                    {pageEmployees.map((employee) => (
                                        <tr key={employee.id} className="transition hover:bg-gray-50">
                                            <td className="px-4 py-4 text-center text-sm font-semibold"> <EmployeeName employee={employee} /></td>
                                            <td className="px-4 py-4 text-center text-sm text-gray-700">{employee.position || '—'}</td>
                                            <td className="px-4 py-4 text-center text-sm text-gray-700">{employee.department?.name || '—'}</td>
                                            <td className="max-w-56 truncate px-4 py-4 text-center text-sm text-gray-700">{employee.user?.email || '—'}</td>
                                            <td className="whitespace-nowrap px-4 py-4 text-center text-sm text-gray-700">{formatDate(employee.hire_date)}</td>
                                            <td className="px-4 py-4 text-center"><StatusBadge status={employee.user?.status} /></td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>

                        <div className="divide-y divide-gray-200 md:hidden">
                            {pageEmployees.map((employee) => (
                                <article key={employee.id} className="px-4 py-4">
                                    <div className="flex items-start justify-between gap-3"><EmployeeDetails employee={employee} /><StatusBadge status={employee.user?.status} /></div>
                                </article>
                            ))}
                        </div>
                    </>
                ) : (
                    <div className="px-6 py-12 text-center">
                        <p className="text-sm font-semibold text-gray-700">No team members found.</p>
                        <p className="mt-1 text-xs text-gray-500">You currently have no assigned employees.</p>
                    </div>
                )}

                {totalPages > 1 && (
                    <div className="flex min-h-[72px] flex-wrap items-center justify-between gap-3 border-t border-gray-200 px-5 py-3">
                        <p className="text-xs text-gray-500">Showing <span className="font-semibold text-gray-700">{Math.min(safePage * pageSize, employees.length)}</span> of <span className="font-semibold text-gray-700">{employees.length}</span> records</p>
                        <div className="flex items-center gap-1" aria-label="Team pages">
                            <button type="button" disabled={safePage <= 1} onClick={() => setCurrentPage(safePage - 1)} className="h-9 rounded-lg border border-gray-200 px-3 text-xs font-medium text-gray-700 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50">Previous</button>
                            {Array.from({ length: totalPages }, (_, index) => index + 1).map((page) => <button key={page} type="button" aria-current={page === safePage ? 'page' : undefined} onClick={() => setCurrentPage(page)} className={`h-9 min-w-9 rounded-lg px-2.5 text-xs font-medium ${page === safePage ? 'border border-gray-200 bg-gray-100 text-gray-800' : 'text-gray-700 hover:bg-gray-100'}`}>{page}</button>)}
                            <button type="button" disabled={safePage >= totalPages} onClick={() => setCurrentPage(safePage + 1)} className="h-9 rounded-lg border border-gray-200 px-3 text-xs font-medium text-gray-700 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50">Next</button>
                        </div>
                    </div>
                )}
            </section>
        </ManagerLayout>
    );
}
