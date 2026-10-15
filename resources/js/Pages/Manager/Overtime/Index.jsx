import React, { useState } from 'react';
import { router } from '@inertiajs/react';
import ManagerLayout from '../../../Layouts/ManagerLayout';
import useApiForm from '../../../Components/Admin/useApiForm';
import { Badge, Button, DataTable, DateFilter, Details, Dialog, Field, Filters, FormErrors, formatDate, matchesSearch, nameOf, Panel, Search, SelectFilter } from '../../../Components/Admin/Ui';

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
    const [search, setSearch] = useState('');
    const [status, setStatus] = useState('');
    const [date, setDate] = useState('');
    const [dialog, setDialog] = useState(null);
    const [notice, setNotice] = useState('');
    const [refreshing, setRefreshing] = useState(false);
    const close = () => setDialog(null);
    const request = dialog?.request;
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
        <div className="mb-6"><h1 className="text-xl font-bold tracking-tight sm:text-2xl">Overtime Requests</h1>
            <p className="mt-1 text-sm text-gray-500">Review overtime requests from your assigned employees.</p></div>
        {notice && <div role="status" className="mb-5 rounded-lg border border-green-200 bg-green-50 p-3 text-sm text-green-800">{notice}</div>}
        <Panel title="Team Overtime Requests" description="Approve or reject pending overtime requests.">
            <Filters>
                <Search value={search} onChange={setSearch} placeholder="Search employee or reason" />
                <SelectFilter label="All statuses" value={status} onChange={setStatus} options={[
                    ['pending', 'Pending'], ['approved', 'Approved'], ['rejected', 'Rejected'],
                ]} />
                <DateFilter value={date} onChange={setDate} />
            </Filters>
            <DataTable key={`${search}:${status}:${date}`} rows={rows} empty="No overtime requests match your filters." columns={[
                { key: 'employee', label: 'Employee', render: (row) => <div><p className="font-semibold">{nameOf(row.employee?.user)}</p><p className="mt-1 text-xs text-gray-500">{row.employee?.department?.name || 'N/A'}</p></div> },
                { key: 'date', label: 'Date', render: (row) => formatDate(row.date) },
                { key: 'hours', label: 'Hours' },
                { key: 'reason', label: 'Reason' },
                { key: 'status', label: 'Status', render: (row) => <Badge value={row.status} /> },
                { key: 'actions', label: 'Actions', render: (row) => <div className="flex flex-wrap gap-2">
                    <Button variant="secondary" disabled={refreshing} onClick={() => setDialog({ type: 'view', request: row })}>View details</Button>
                    {row.status === 'Pending' && <>
                        <Button disabled={refreshing} onClick={() => setDialog({ type: 'approve', request: row })}>Approve</Button>
                        <Button variant="danger" disabled={refreshing} onClick={() => setDialog({ type: 'reject', request: row })}>Reject</Button>
                    </>}
                </div> },
            ]} />
        </Panel>
        {dialog?.type === 'view' && <Dialog title="Overtime Request Details" onClose={close}>
            <Details items={[
                ['Employee', nameOf(request.employee?.user)], ['Email', request.employee?.user?.email],
                ['Department', request.employee?.department?.name], ['Position', request.employee?.position],
                ['Date', formatDate(request.date)], ['Hours', request.hours],
                ['Reason', request.reason || 'No reason provided.'], ['Status', <Badge value={request.status} />],
                ['Submitted', formatDate(request.created_at)], ['Reviewed by', nameOf(request.approver)],
                ['Review date', formatDate(request.approval_date)], ['Rejection reason', request.rejection_reason || 'N/A'],
            ]} />
        </Dialog>}
        {['approve', 'reject'].includes(dialog?.type) && <ReviewDialog key={`${dialog.type}:${request.id}`} request={request} action={dialog.type} onClose={close} onSaved={saved} />}
    </ManagerLayout>;
}
