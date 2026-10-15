import React, { useEffect, useState } from 'react';
import { router, usePage } from '@inertiajs/react';
import ManagerLayout from '../../../Layouts/ManagerLayout';
import useApiForm from '../../../Components/Admin/useApiForm';
import { PageHeader, Badge, Button, DataTable, DateFilter, Details, Dialog, Field, Filters, FormErrors, formatDate, matchesSearch, nameOf, Panel, Search, SelectFilter } from '../../../Components/Admin/Ui';

function ReviewDialog({ request, action, onClose, onSaved }) {
    const rejecting = action === 'reject';
    const form = useApiForm(rejecting ? { rejection_reason: '' } : {});

    return <Dialog title={rejecting ? 'Reject Overtime Request' : 'Approve Overtime Request'} onClose={onClose} busy={form.processing}>
        <form onSubmit={async (event) => {
            event.preventDefault();
            if (await form.submit('post', rejecting ? request.rejectUrl : request.approveUrl)) onSaved();
        }}>
            <FormErrors form={form} />
            <Details items={[
                ['Employee', nameOf(request.employee?.user)], ['Department', request.employee?.department?.name],
                ['Date', formatDate(request.date)], ['Hours', request.hours], ['Reason', request.reason || 'No reason provided.'],
            ]} />
            {rejecting ? <fieldset disabled={form.processing} className="mt-5">
                <Field form={form} name="rejection_reason" label="Rejection reason" type="textarea" rows={4} maxLength={500} required />
            </fieldset> : <p className="mt-5 text-sm text-gray-600">Approve this overtime request?</p>}
            <div className="mt-6 flex justify-end gap-3">
                <Button variant="secondary" disabled={form.processing} onClick={onClose}>Cancel</Button>
                <Button type="submit" variant={rejecting ? 'danger' : 'primary'} disabled={form.processing}>
                    {form.processing ? 'Submitting...' : rejecting ? 'Reject request' : 'Approve request'}
                </Button>
            </div>
        </form>
    </Dialog>;
}

export default function Index({ overtimeRequests }) {
    const { url } = usePage();
    const query = new URL(url, 'http://localhost').searchParams;
    const [search, setSearch] = useState('');
    const [status, setStatus] = useState(() => query.get('status') || 'pending');
    const [date, setDate] = useState('');
    const [dialog, setDialog] = useState(null);
    const [notice, setNotice] = useState('');
    const [refreshing, setRefreshing] = useState(false);
    const close = () => setDialog(null);
    const request = dialog?.request;
    useEffect(() => {
        const params = new URL(url, 'http://localhost').searchParams;
        if (params.has('status')) setStatus(params.get('status'));
        const id = params.get('request');
        const selected = overtimeRequests.find((row) => String(row.id) === id);
        if (selected) setDialog({ type: 'view', request: selected });
    }, [url]);
    const rows = overtimeRequests.filter((row) =>
        matchesSearch(search, nameOf(row.employee?.user), row.employee?.user?.email, row.employee?.department?.name, row.reason)
        && (!status || row.status?.toLowerCase() === status)
        && (!date || row.date === date));

    function saved() {
        setNotice(dialog.type === 'approve' ? 'Overtime request approved successfully.' : 'Overtime request rejected successfully.');
        close();
        setRefreshing(true);
        router.reload({ only: ['overtimeRequests'], preserveScroll: true, onFinish: () => setRefreshing(false) });
    }

    return <ManagerLayout title="Overtime Requests" breadcrumbParent="Manager" breadcrumbCurrent="Overtime Requests">
        <PageHeader title="Overtime Requests" description="Review overtime requests from your assigned employees." />
        {notice && <div role="status" className="mb-5 rounded-lg border border-green-200 bg-green-50 p-3 text-sm text-green-800">{notice}</div>}
        <Panel title="Team Overtime Requests" description="Approve or reject pending overtime requests.">
            <Filters>
                <Search value={search} onChange={setSearch} placeholder="Search employee or reason" />
                <SelectFilter label="All statuses" value={status} onChange={setStatus} options={[
                    ['pending', 'Pending'], ['approved', 'Approved'], ['rejected', 'Rejected'],
                ]} />
                <DateFilter label="Overtime date" value={date} onChange={setDate} />
                {(search || status || date) && <Button variant="secondary" onClick={() => { setSearch(''); setStatus(''); setDate(''); }}>Clear filters</Button>}
            </Filters>
            <DataTable key={`${search}:${status}:${date}`} rows={rows} empty={overtimeRequests.length ? 'No matching requests. Change or clear your filters to see more.' : 'No overtime requests have been submitted by your team yet.'} columns={[
                { key: 'employee', label: 'Employee', render: (row) => <div><p className="font-semibold">{nameOf(row.employee?.user)}</p><p className="mt-1 text-xs text-gray-500">{row.employee?.department?.name || 'N/A'}</p></div> },
                { key: 'date', label: 'Date', render: (row) => formatDate(row.date) },
                { key: 'hours', label: 'Hours' },
                { key: 'reason', label: 'Reason' },
                { key: 'status', label: 'Status', render: (row) => <Badge value={row.status} /> },
                { key: 'actions', label: 'Actions', render: (row) => <div className="flex flex-wrap gap-2">
                    <Button variant={row.status === 'Pending' ? 'primary' : 'secondary'} disabled={refreshing} onClick={() => setDialog({ type: 'view', request: row })}>{row.status === 'Pending' ? 'Review request' : 'View details'}</Button>
                </div> },
            ]} />
        </Panel>
        {dialog?.type === 'view' && <Dialog title="Overtime Request Details" onClose={close}>
            <Details items={[
                ['Employee', nameOf(request.employee?.user)], ['Email', request.employee?.user?.email],
                ['Department', request.employee?.department?.name], ['Position', request.employee?.position],
                ['Date', formatDate(request.date)], ['Hours', request.hours],
                ['Reason', request.reason || 'No reason provided.'], ['Status', <Badge value={request.status} />],
                ['Submitted', formatDate(request.created_at)],
                ...(request.status !== 'Pending' ? [['Reviewed by', nameOf(request.approver)], ['Review date', formatDate(request.approval_date)]] : []),
                ...(request.status === 'Rejected' ? [['Rejection reason', request.rejection_reason || 'No reason recorded.']] : []),
            ]} />
            {request.status === 'Pending' && <div className="mt-6 flex flex-wrap justify-end gap-3 border-t border-gray-200 pt-5">
                <Button variant="secondary" disabled={refreshing} onClick={close}>Close</Button>
                <Button variant="danger" disabled={refreshing} onClick={() => setDialog({ type: 'reject', request })}>Reject</Button>
                <Button disabled={refreshing} onClick={() => setDialog({ type: 'approve', request })}>Approve</Button>
            </div>}
        </Dialog>}
        {['approve', 'reject'].includes(dialog?.type) && <ReviewDialog key={`${dialog.type}:${request.id}`} request={request} action={dialog.type} onClose={close} onSaved={saved} />}
    </ManagerLayout>;
}
