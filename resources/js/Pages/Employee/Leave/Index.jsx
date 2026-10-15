import React, { useState } from 'react';
import { router } from '@inertiajs/react';
import AppLayout from '../../../Layouts/AppLayout';
import LayoutIcon from '../../../Components/LayoutIcon';
import useApiForm from '../../../Components/Admin/useApiForm';
import { Badge, Button, DataTable, Details, Dialog, Field, Filters, FormActions, FormErrors, formatDate, matchesSearch, nameOf, Panel, Search, SelectFilter } from '../../../Components/Admin/Ui';

const leaveDays = (request) => Math.round((Date.parse(request.end_date) - Date.parse(request.start_date)) / 86400000) + 1;

function LeaveEditor({ storeUrl, onClose, onSaved }) {
    const form = useApiForm({ leave_type: '', start_date: '', end_date: '', reason: '' });

    return <Dialog title="Request Leave" onClose={onClose} busy={form.processing}>
        <form onSubmit={async (event) => {
            event.preventDefault();
            if (await form.submit('post', storeUrl)) onSaved();
        }}>
            <FormErrors form={form} />
            <fieldset disabled={form.processing} className="space-y-4">
                <Field form={form} name="leave_type" label="Leave type" required options={[
                    ['', 'Select leave type'], ['Sick', 'Sick Leave'], ['Vacation', 'Vacation Leave'],
                    ['Emergency', 'Emergency Leave'], ['Bereavement', 'Bereavement Leave'],
                ]} />
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <Field form={form} name="start_date" label="Start date" type="date" required />
                    <Field form={form} name="end_date" label="End date" type="date" min={form.data.start_date || undefined} required />
                </div>
                <Field form={form} name="reason" label="Reason (optional)" type="textarea" rows={4} maxLength={500} />
            </fieldset>
            <FormActions busy={form.processing} onCancel={onClose} label="Submit leave request" />
        </form>
    </Dialog>;
}

export default function Index({ leaves, pendingCount, approvedCount, rejectedCount, totalCount, storeUrl, canCreate }) {
    const [search, setSearch] = useState('');
    const [status, setStatus] = useState('');
    const [selected, setSelected] = useState(null);
    const [creating, setCreating] = useState(false);
    const [notice, setNotice] = useState('');
    const [refreshing, setRefreshing] = useState(false);
    const rows = leaves.filter((row) => matchesSearch(search, row.leave_type, row.reason)
        && (!status || row.status?.toLowerCase() === status));
    const cards = [
        { label: 'Pending Leave', value: pendingCount, description: 'Awaiting review', color: 'bg-amber-50 text-amber-600' },
        { label: 'Approved Leave', value: approvedCount, description: 'Approved requests', color: 'bg-green-50 text-green-600' },
        { label: 'Rejected Leave', value: rejectedCount, description: 'Rejected requests', color: 'bg-red-50 text-red-600' },
        { label: 'Total Requests', value: totalCount, description: 'All leave requests', color: 'bg-blue-50 text-blue-600' },
    ];

    function saved() {
        setCreating(false);
        setNotice('Leave request submitted successfully.');
        setRefreshing(true);
        router.reload({ only: ['leaves', 'pendingCount', 'approvedCount', 'rejectedCount', 'totalCount'],
            preserveScroll: true, onFinish: () => setRefreshing(false) });
    }

    return <AppLayout title="My Leave" breadcrumbParent="Employee" breadcrumbCurrent="My Leave">
        <div className="mb-6 flex flex-wrap items-center justify-between gap-4">
            <div><h1 className="text-2xl font-semibold tracking-tight sm:text-3xl">My Leave</h1>
                <p className="mt-1 text-sm text-gray-500">Submit and track your leave requests.</p></div>
            {canCreate && <Button disabled={refreshing} onClick={() => setCreating(true)}>Request leave</Button>}
        </div>
        {notice && <div role="status" className="mb-5 rounded-lg border border-green-200 bg-green-50 p-3 text-sm text-green-800">{notice}</div>}
        <div className="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            {cards.map((card) => <section key={card.label} className="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <div className="flex items-start justify-between gap-3">
                    <div><h2 className="text-xs font-semibold uppercase tracking-wide text-gray-500">{card.label}</h2>
                        <p className="mt-2 text-3xl font-bold">{card.value}</p></div>
                    <div className={`flex h-10 w-10 shrink-0 items-center justify-center rounded-lg ${card.color}`}><LayoutIcon name="leave" /></div>
                </div><p className="mt-4 text-xs text-gray-500">{card.description}</p>
            </section>)}
        </div>
        <Panel title="My Leave Requests" description="View your submitted leave requests and their current status.">
            <Filters>
                <Search value={search} onChange={setSearch} placeholder="Search leave type or reason" />
                <SelectFilter label="All statuses" value={status} onChange={setStatus} options={[
                    ['pending', 'Pending'], ['approved', 'Approved'], ['rejected', 'Rejected'],
                ]} />
            </Filters>
            <DataTable key={`${search}:${status}`} rows={rows} empty="No leave requests match your filters." columns={[
                { key: 'leave_type', label: 'Leave type' },
                { key: 'dates', label: 'Dates', render: (row) => `${formatDate(row.start_date)} – ${formatDate(row.end_date)}` },
                { key: 'days', label: 'Days', render: leaveDays }, { key: 'reason', label: 'Reason' },
                { key: 'submitted', label: 'Submitted', render: (row) => formatDate(row.created_at) },
                { key: 'status', label: 'Status', render: (row) => <Badge value={row.status} /> },
                { key: 'actions', label: 'Actions', render: (row) => <Button variant="secondary" disabled={refreshing} onClick={() => setSelected(row)}>View details</Button> },
            ]} />
        </Panel>
        {selected && <Dialog title="Leave Request Details" onClose={() => setSelected(null)}>
            <Details items={[
                ['Leave type', selected.leave_type], ['Status', <Badge value={selected.status} />],
                ['Start date', formatDate(selected.start_date)], ['End date', formatDate(selected.end_date)], ['Days', leaveDays(selected)],
                ['Submitted', formatDate(selected.created_at)], ['Reason', selected.reason || 'No reason provided.'],
                ['Reviewed by', nameOf(selected.approver)], ['Review date', formatDate(selected.approval_date)],
                ['Rejection reason', selected.rejection_reason || 'N/A'],
            ]} />
        </Dialog>}
        {creating && <LeaveEditor storeUrl={storeUrl} onClose={() => setCreating(false)} onSaved={saved} />}
    </AppLayout>;
}
