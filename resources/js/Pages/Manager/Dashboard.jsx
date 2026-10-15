import React from 'react';
import { Link, usePage } from '@inertiajs/react';
import ManagerLayout from '../../Layouts/ManagerLayout';
import { DataTable, formatDate, nameOf, Panel } from '../../Components/Admin/Ui';

function DashboardLink({ href, children, className }) {
    const { layout } = usePage().props;
    const inertia = layout?.navigation?.some((item) => new URL(item.href, 'http://localhost').pathname === new URL(href, 'http://localhost').pathname && item.inertia);
    const props = { href, className };
    return inertia ? <Link {...props}>{children}</Link> : <a {...props}>{children}</a>;
}

export default function Dashboard({ pendingLeaveCount, pendingOvertimeCount, teamMemberCount, approvedRequestCount, pendingRequests, links }) {
    const pendingCount = pendingLeaveCount + pendingOvertimeCount;
    const pendingLink = (href) => `${href}?status=pending`;

    return <ManagerLayout title="Manager Dashboard" breadcrumbParent="Manager" breadcrumbCurrent="Dashboard">
        <div className="mb-6">
            <h1 className="text-2xl font-semibold tracking-tight sm:text-3xl">Manager Dashboard</h1>
            <p className="mt-1 text-sm text-gray-500">Review your team's requests and monitor current activity.</p>
        </div>
        <section aria-labelledby="review-heading" className="mb-8 rounded-xl bg-brand-navy p-5 text-white sm:p-7">
            <div className="flex flex-col justify-between gap-5 sm:flex-row sm:items-center">
                <div className="max-w-xl">
                    <h2 id="review-heading" className="text-xl font-semibold sm:text-2xl">{pendingCount ? `${pendingCount} request${pendingCount === 1 ? '' : 's'} awaiting your review` : "You're all caught up"}</h2>
                    <p className="mt-2 text-sm leading-6 text-blue-100">{pendingCount ? 'Review the details, then approve or explain your decision. Start with a request below.' : 'There are no pending leave or overtime requests from your team.'}</p>
                </div>
                <div className="flex flex-wrap gap-3">
                    <DashboardLink href={pendingLink(links.leave)} className="inline-flex min-h-11 items-center justify-center gap-2 rounded-lg bg-white px-4 py-2 text-sm font-semibold text-brand-navy hover:bg-blue-50">Leave <span className="rounded bg-blue-50 px-2 py-0.5 tabular-nums">{pendingLeaveCount}</span></DashboardLink>
                    <DashboardLink href={pendingLink(links.overtime)} className="inline-flex min-h-11 items-center justify-center gap-2 rounded-lg border border-brand-muted px-4 py-2 text-sm font-semibold text-white hover:bg-brand-selected">Overtime <span className="rounded bg-brand-selected px-2 py-0.5 tabular-nums">{pendingOvertimeCount}</span></DashboardLink>
                </div>
            </div>
        </section>
        <Panel title="Requests to review" description={pendingCount > pendingRequests.length ? `Showing the latest ${pendingRequests.length} of ${pendingCount} pending requests. Use Leave or Overtime above to view the full queue.` : 'Select a request to see its details and make a decision.'}>
            <DataTable rows={pendingRequests} empty="No pending requests. You're all caught up." columns={[
                { key: 'employee', label: 'Employee', render: (row) => <div>
                    <p className="font-semibold">{nameOf(row.employee?.user)}</p>
                    <p className="mt-1 text-xs text-gray-500">{row.employee?.department?.name || 'N/A'}</p>
                </div> },
                { key: 'type', label: 'Request type', render: (row) => <span className={`inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ${row.type === 'Leave' ? 'bg-blue-50 text-blue-700' : 'bg-purple-50 text-purple-700'}`}>{row.type}</span> },
                { key: 'details', label: 'Details' },
                { key: 'date', label: 'Date', render: (row) => formatDate(row.date) },
                { key: 'submitted', label: 'Submitted', render: (row) => formatDate(row.submitted) },
                { key: 'actions', label: 'Action', render: (row) => <DashboardLink href={`${row.route}?request=${encodeURIComponent(String(row.id).replace(/^(leave|overtime)-/, ''))}&status=pending`} className="inline-flex min-h-11 items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-blue-50">Review</DashboardLink> },
            ]} />
        </Panel>
        <section aria-label="Team summary" className="mt-6 flex flex-wrap items-center gap-x-8 gap-y-3 border-t border-gray-200 pt-5 text-sm text-gray-600">
            <DashboardLink href={links.team} className="inline-flex min-h-11 items-center gap-2 font-medium text-blue-800 underline-offset-4 hover:underline"><span className="font-semibold tabular-nums">{teamMemberCount}</span> team members</DashboardLink>
            <p><span className="font-semibold text-gray-900 tabular-nums">{approvedRequestCount}</span> approved requests (all time)</p>
        </section>
    </ManagerLayout>;
}
