import React, { useState } from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import LayoutIcon from '../../Components/LayoutIcon';
import useApiForm from '../../Components/Admin/useApiForm';
import { Badge, Button, DataTable, FormErrors, Panel } from '../../Components/Admin/Ui';

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
        <div className="mb-6"><h1 className="text-xl font-bold tracking-tight sm:text-2xl">Employee Dashboard</h1>
            <p className="mt-1 text-sm text-gray-500">Record your attendance and track your requests.</p></div>
        {notice && <div role="status" className="mb-5 rounded-lg border border-green-200 bg-green-50 p-3 text-sm text-green-800">{notice}</div>}
        <FormErrors form={form} />
        <section aria-label="Today's attendance" className="mb-6 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <div className="bg-gradient-to-r from-[#11458c] to-teal-600 px-5 py-6 text-white">
                <p className="text-sm font-semibold uppercase tracking-wide">Today's Attendance</p><h2 className="mt-2 text-2xl font-bold sm:text-3xl">{todayLabel}</h2>
            </div>
            <div className="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2 xl:grid-cols-4">
                {[
                    ['Status', <Badge value={status.replace('_', ' ')} />], ['Time in', attendance.time_in || 'N/A'],
                    ['Time out', attendance.time_out || 'N/A'], ['Total hours', attendance.total_hours || 'N/A'],
                ].map(([label, value]) => <div key={label} className="rounded-xl border border-gray-200 bg-gray-50 p-4">
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
        <div className="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            {cards.map((card) => <section key={card.label} className="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <div className="flex items-start justify-between gap-3">
                    <div><h2 className="text-xs font-semibold uppercase tracking-wide text-gray-500">{card.label}</h2>
                        <p className="mt-2 text-3xl font-bold">{card.value}</p></div>
                    <div className={`flex h-10 w-10 shrink-0 items-center justify-center rounded-lg ${card.color}`}><LayoutIcon name={card.icon} /></div>
                </div><p className="mt-4 text-xs text-gray-500">{card.description}</p>
            </section>)}
        </div>
        <div className="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
            {[
                ['Request Leave', links.leave, 'Submit and manage your leave requests.'],
                ['Request Overtime', links.overtime, 'Submit and manage your overtime requests.'],
            ].map(([label, href, description]) => <DashboardLink key={label} href={href}
                className="rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition hover:border-blue-300 hover:shadow-md">
                <h2 className="font-semibold">{label} &rarr;</h2><p className="mt-1 text-sm text-gray-500">{description}</p>
            </DashboardLink>)}
        </div>
        <Panel title="Recent Requests" description="Your five most recent leave and overtime requests.">
            <DataTable rows={recentRequests} empty="No recent requests." columns={[
                { key: 'type', label: 'Request type' }, { key: 'date', label: 'Date' }, { key: 'reason', label: 'Reason' },
                { key: 'submitted', label: 'Submitted' }, { key: 'status', label: 'Status', render: (row) => <Badge value={row.status} /> },
                { key: 'actions', label: 'Action', render: (row) => <DashboardLink href={row.route}
                    className="inline-flex rounded-md border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">View</DashboardLink> },
            ]} />
        </Panel>
    </AppLayout>;
}
