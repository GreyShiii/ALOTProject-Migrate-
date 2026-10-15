import React, { useState } from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import LayoutIcon from '../../Components/LayoutIcon';
import useApiForm from '../../Components/Admin/useApiForm';
import { PageHeader, Badge, Button, DataTable, FormErrors, Panel } from '../../Components/Admin/Ui';

function DashboardLink({ href, children, className }) {
    const { layout } = usePage().props;
    const inertia = layout?.navigation.some((item) => item.href === href && item.inertia);
    return inertia ? <Link href={href} className={className}>{children}</Link> : <a href={href} className={className}>{children}</a>;
}

export default function Dashboard({ attendance, todayLabel, pendingLeaveCount, pendingOvertimeCount, approvedLeaveCount, approvedOvertimeCount, recentRequests, links }) {
    const form = useApiForm();
    const [notice, setNotice] = useState('');
    const [refreshing, setRefreshing] = useState(false);
    const busy = form.processing || refreshing;
    const status = attendance.status;
    const cards = [
        { label: 'Pending Leave', value: pendingLeaveCount, icon: 'leave', color: 'bg-amber-50 text-amber-600', description: 'Awaiting manager review' },
        { label: 'Pending Overtime', value: pendingOvertimeCount, icon: 'overtime', color: 'bg-amber-50 text-amber-600', description: 'Awaiting manager review' },
        { label: 'Approved Leave', value: approvedLeaveCount, icon: 'leave', color: 'bg-green-50 text-green-600', description: 'This year' },
        { label: 'Approved Overtime', value: approvedOvertimeCount, icon: 'overtime', color: 'bg-green-50 text-green-600', description: 'This year' },
    ];

    async function recordAttendance() {
        const timeIn = status === 'not_started';
        if (await form.submit('post', timeIn ? links.timeIn : links.timeOut)) {
            setNotice(timeIn ? 'Time in recorded successfully.' : 'Time out recorded successfully.');
            setRefreshing(true);
            router.reload({ only: ['attendance', 'todayLabel'], preserveScroll: true, onFinish: () => setRefreshing(false) });
        }
    }

    return <AppLayout title="Employee Dashboard" breadcrumbParent="Employee" breadcrumbCurrent="Dashboard">
        <PageHeader title="Employee Dashboard" description="Record your attendance and track your requests." />
        {notice && <div role="status" className="mb-5 rounded-lg border border-green-200 bg-green-50 p-3 text-sm text-green-800">{notice}</div>}
        <FormErrors form={form} />
        <section aria-label="Today's attendance" className="mb-6 overflow-hidden rounded-xl border border-gray-200 bg-white">
            <div className="bg-brand-navy px-5 py-6 text-white">
                <p className="text-sm font-semibold uppercase tracking-wide">Today's Attendance</p><h2 className="mt-2 text-2xl font-bold sm:text-3xl">{todayLabel}</h2>
            </div>
            <div className="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2 xl:grid-cols-4">
                {[
                    ['Status', <Badge value={status.replace('_', ' ')} />], ['Time in', attendance.time_in || 'N/A'],
                    ['Time out', attendance.time_out || 'N/A'], ['Total hours', attendance.total_hours || 'N/A'],
                ].map(([label, value]) => <div key={label} className="min-w-0 border-b border-gray-200 pb-4 sm:border-b-0 sm:border-r sm:px-4 sm:last:border-r-0">
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
        <section aria-label="Request summary" className="mb-6 grid grid-cols-2 gap-y-5 border-y border-gray-200 py-5 lg:grid-cols-4">
            {cards.map((card) => <div key={card.label} className="min-w-0 px-4">
                <p className="text-sm font-medium text-gray-600">{card.label}</p>
                <p className="mt-1 text-2xl font-semibold tabular-nums text-gray-900">{card.value}</p>
                <p className="mt-1 text-xs text-gray-500">{card.description}</p>
            </div>)}
        </section>
        <div className="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
            {[
                ['Request Leave', links.leave, 'Submit and manage your leave requests.'],
                ['Request Overtime', links.overtime, 'Submit and manage your overtime requests.'],
            ].map(([label, href, description]) => <DashboardLink key={label} href={href}
                className="group flex items-center gap-4 rounded-xl bg-white p-5 shadow-[0_4px_24px_-12px_rgba(24,49,83,0.22)] transition-colors hover:bg-blue-50 focus-visible:bg-blue-50">
                <span className="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-blue-800"><LayoutIcon name={href === links.leave ? 'leave' : 'overtime'} className="h-6 w-6" /></span>
                <div className="min-w-0 flex-1"><h2 className="font-semibold text-brand-navy">{label}</h2><p className="mt-1 text-sm leading-6 text-gray-600">{description}</p></div>
                <LayoutIcon name="chevron" className="h-5 w-5 shrink-0 text-blue-700 transition-transform group-hover:translate-x-1 motion-reduce:transform-none" />
            </DashboardLink>)}
        </div>
        <Panel title="Recent Requests" description="Your five most recent leave and overtime requests.">
            <DataTable rows={recentRequests} empty="No recent requests." columns={[
                { key: 'type', label: 'Request type' }, { key: 'date', label: 'Date' }, { key: 'reason', label: 'Reason' },
                { key: 'submitted', label: 'Submitted' }, { key: 'status', label: 'Status', render: (row) => <Badge value={row.status} /> },
                { key: 'actions', label: 'Action', render: (row) => <DashboardLink href={row.route}
                    className="inline-flex min-h-11 items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">View</DashboardLink> },
            ]} />
        </Panel>
    </AppLayout>;
}
