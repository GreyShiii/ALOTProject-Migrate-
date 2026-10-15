import React, { useState } from 'react';
import { router } from '@inertiajs/react';
import AppLayout from '../../../Layouts/AppLayout';
import LayoutIcon from '../../../Components/LayoutIcon';
import useApiForm from '../../../Components/Admin/useApiForm';
import { PageHeader, Badge, Button, DataTable, Details, Dialog, Field, Filters, FormActions, FormErrors, formatDate, matchesSearch, nameOf, Panel, Search, SelectFilter } from '../../../Components/Admin/Ui';

function OvertimeEditor({ storeUrl, onClose, onSaved }) {
    const form = useApiForm({ date: '', hours: '', reason: '' });

    return <Dialog title="Request Overtime" onClose={onClose} busy={form.processing}>
        <form onSubmit={async (event) => {
            event.preventDefault();
            if (await form.submit('post', storeUrl)) onSaved();
        }}>
            <FormErrors form={form} />
            <fieldset disabled={form.processing} className="space-y-4">
                <Field form={form} name="date" label="Overtime date" type="date" required />
                <Field form={form} name="hours" label="Overtime hours" type="number" min="0.5" max="6" step="0.5" required />
                <Field form={form} name="reason" label="Reason (optional)" type="textarea" rows={4} maxLength={500} />
            </fieldset>
            <FormActions busy={form.processing} onCancel={onClose} label="Submit overtime request" />
        </form>
    </Dialog>;
}

export default function Index({ overtimeRequests, pendingCount, approvedCount, approvedHours, totalCount, storeUrl, canCreate }) {
    const [search, setSearch] = useState('');
    const [status, setStatus] = useState('');
    const [selected, setSelected] = useState(null);
    const [creating, setCreating] = useState(false);
    const [notice, setNotice] = useState('');
    const [refreshing, setRefreshing] = useState(false);
    const rows = overtimeRequests.filter((row) => matchesSearch(search, row.date, formatDate(row.date), row.hours, row.reason)
        && (!status || row.status?.toLowerCase() === status));
    const cards = [
        { label: 'Pending Overtime', value: pendingCount, description: 'Awaiting review', color: 'bg-amber-50 text-amber-600' },
        { label: 'Approved Overtime', value: approvedCount, description: 'Approved requests', color: 'bg-green-50 text-green-600' },
        { label: 'Approved Hours', value: approvedHours, description: 'Total approved hours', color: 'bg-blue-50 text-blue-600' },
        { label: 'Total Requests', value: totalCount, description: 'All overtime requests', color: 'bg-blue-50 text-blue-600' },
    ];

    function saved() {
        setCreating(false);
        setNotice('Overtime request submitted successfully.');
        setRefreshing(true);
        router.reload({ only: ['overtimeRequests', 'pendingCount', 'approvedCount', 'rejectedCount', 'totalCount', 'approvedHours'],
            preserveScroll: true, onFinish: () => setRefreshing(false) });
    }

    return <AppLayout title="My Overtime" breadcrumbParent="Employee" breadcrumbCurrent="My Overtime">
        <PageHeader title="My Overtime" description="Submit and track your overtime requests." action={canCreate && <Button disabled={refreshing} onClick={() => setCreating(true)}>Request overtime</Button>} />
        {notice && <div role="status" className="mb-5 rounded-lg border border-green-200 bg-green-50 p-3 text-sm text-green-800">{notice}</div>}
        <div className="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            {cards.map((card) => <section key={card.label} className="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <div className="flex items-start justify-between gap-3">
                    <div><h2 className="text-xs font-semibold uppercase tracking-wide text-gray-500">{card.label}</h2>
                        <p className="mt-2 text-3xl font-bold">{card.value}</p></div>
                    <div className={`flex h-10 w-10 shrink-0 items-center justify-center rounded-lg ${card.color}`}><LayoutIcon name="overtime" /></div>
                </div><p className="mt-4 text-xs text-gray-500">{card.description}</p>
            </section>)}
        </div>
        <Panel title="My Overtime Requests" description="View your submitted overtime requests and their current status.">
            <Filters>
                <Search value={search} onChange={setSearch} placeholder="Search date, hours or reason" />
                <SelectFilter label="All statuses" value={status} onChange={setStatus} options={[
                    ['pending', 'Pending'], ['approved', 'Approved'], ['rejected', 'Rejected'],
                ]} />
            </Filters>
            <DataTable key={`${search}:${status}`} rows={rows} empty="No overtime requests match your filters." columns={[
                { key: 'date', label: 'Date', render: (row) => formatDate(row.date) },
                { key: 'hours', label: 'Hours' }, { key: 'reason', label: 'Reason' },
                { key: 'submitted', label: 'Submitted', render: (row) => formatDate(row.created_at) },
                { key: 'status', label: 'Status', render: (row) => <Badge value={row.status} /> },
                { key: 'actions', label: 'Actions', render: (row) => <Button variant="secondary" disabled={refreshing} onClick={() => setSelected(row)}>View details</Button> },
            ]} />
        </Panel>
        {selected && <Dialog title="Overtime Request Details" onClose={() => setSelected(null)}>
            <Details items={[
                ['Date', formatDate(selected.date)], ['Hours', selected.hours], ['Status', <Badge value={selected.status} />],
                ['Submitted', formatDate(selected.created_at)], ['Reason', selected.reason || 'No reason provided.'],
                ['Reviewed by', nameOf(selected.approver)], ['Review date', formatDate(selected.approval_date)],
                ['Rejection reason', selected.rejection_reason || 'N/A'],
            ]} />
        </Dialog>}
        {creating && <OvertimeEditor storeUrl={storeUrl} onClose={() => setCreating(false)} onSaved={saved} />}
    </AppLayout>;
}
