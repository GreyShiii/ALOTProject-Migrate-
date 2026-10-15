import React from 'react';
import { Link, usePage } from '@inertiajs/react';
import ManagerLayout from '../../Layouts/ManagerLayout';
import LayoutIcon from '../../Components/LayoutIcon';
import { DataTable, formatDate, nameOf, Panel } from '../../Components/Admin/Ui';

function DashboardLink({ href, children, className }) {
    const { layout } = usePage().props;
    const inertia = layout?.navigation.some((item) => item.href === href && item.inertia);
    const props = { href, className };
    return inertia ? <Link {...props}>{children}</Link> : <a {...props}>{children}</a>;
}

export default function Dashboard({ pendingLeaveCount, pendingOvertimeCount, teamMemberCount, approvedRequestCount, pendingRequests, links }) {
    const cards = [
        { label: 'Pending Leave', value: pendingLeaveCount, icon: 'leave', color: 'bg-amber-50 text-amber-600', href: links.leave, action: 'Review leave requests' },
        { label: 'Pending Overtime', value: pendingOvertimeCount, icon: 'overtime', color: 'bg-blue-50 text-blue-600', href: links.overtime, action: 'Review overtime requests' },
        { label: 'Team Members', value: teamMemberCount, icon: 'people', color: 'bg-teal-50 text-teal-600', href: links.team, action: 'View my team' },
        { label: 'Approved Requests', value: approvedRequestCount, icon: 'users', color: 'bg-green-50 text-green-600' },
    ];

    return <ManagerLayout title="Manager Dashboard" breadcrumbParent="Manager" breadcrumbCurrent="Dashboard">
        <div className="mb-6">
            <h1 className="text-xl font-bold tracking-tight sm:text-2xl">Manager Dashboard</h1>
            <p className="mt-1 text-sm text-gray-500">Review your team's requests and monitor current activity.</p>
        </div>
        <div className="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            {cards.map((card) => <section key={card.label} className="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <div className="flex items-start justify-between gap-3">
                    <div><h2 className="text-xs font-semibold uppercase tracking-wide text-gray-500">{card.label}</h2>
                        <p className="mt-2 text-3xl font-bold">{card.value}</p></div>
                    <div className={`flex h-10 w-10 shrink-0 items-center justify-center rounded-lg ${card.color}`}><LayoutIcon name={card.icon} /></div>
                </div>
                {card.href ? <DashboardLink href={card.href} className="mt-4 inline-block text-xs font-semibold text-blue-600 hover:text-blue-700">{card.action} →</DashboardLink>
                    : <p className="mt-4 text-xs text-gray-500">Approved leave and overtime requests.</p>}
            </section>)}
        </div>
        <Panel title="Pending Requests" description="Latest requests that require your review.">
            <DataTable rows={pendingRequests} empty="No pending requests. You're all caught up." columns={[
                { key: 'employee', label: 'Employee', render: (row) => <div>
                    <p className="font-semibold">{nameOf(row.employee?.user)}</p>
                    <p className="mt-1 text-xs text-gray-500">{row.employee?.department?.name || 'N/A'}</p>
                </div> },
                { key: 'type', label: 'Request type', render: (row) => <span className={`inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ${row.type === 'Leave' ? 'bg-blue-50 text-blue-700' : 'bg-purple-50 text-purple-700'}`}>{row.type}</span> },
                { key: 'details', label: 'Details' },
                { key: 'date', label: 'Date', render: (row) => formatDate(row.date) },
                { key: 'submitted', label: 'Submitted', render: (row) => formatDate(row.submitted) },
                { key: 'actions', label: 'Action', render: (row) => <DashboardLink href={row.route} className="inline-flex rounded-md border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-100">Review</DashboardLink> },
            ]} />
        </Panel>
    </ManagerLayout>;
}
