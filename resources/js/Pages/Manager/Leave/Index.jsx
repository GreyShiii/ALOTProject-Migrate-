import React, { useEffect, useState } from 'react';
import { router, usePage } from '@inertiajs/react';
import ManagerLayout from '../../../Layouts/ManagerLayout';
import useApiForm from '../../../Components/Admin/useApiForm';
import { Badge, Button, DataTable, DateFilter, Details, Dialog, Field, Filters, FormErrors, formatDate, matchesSearch, nameOf, Panel, Search, SelectFilter } from '../../../Components/Admin/Ui';

const leaveDays = (request) => Math.round((Date.parse(request.end_date) - Date.parse(request.start_date)) / 86400000) + 1;

function ReviewDialog({ request, action, onClose, onSaved }) {
    const rejecting = action === 'reject';
    const form = useApiForm(rejecting ? { rejection_reason: '' } : {});

    return <Dialog title={rejecting ? 'Reject Leave Request' : 'Approve Leave Request'} onClose={onClose} busy={form.processing}>
        <form onSubmit={async (event) => {
            event.preventDefault();
            if (await form.submit('post', rejecting ? request.rejectUrl : request.approveUrl)) onSaved();
        }}>
            <FormErrors form={form} />
            <Details items={[
                ['Employee', nameOf(request.employee?.user)], ['Leave type', request.leave_type],
                ['Department', request.employee?.department?.name], ['Reason', request.reason || 'No reason provided.'],
                ['Dates', `${formatDate(request.start_date)} – ${formatDate(request.end_date)}`], ['Calendar days', leaveDays(request)],
            ]} />
            {rejecting ? <fieldset disabled={form.processing} className="mt-5">
                <Field form={form} name="rejection_reason" label="Rejection reason" type="textarea" rows={4} maxLength={500} required />
            </fieldset> : <p className="mt-5 text-sm text-gray-600">Approve this leave request?</p>}
            <div className="mt-6 flex justify-end gap-3">
                <Button variant="secondary" disabled={form.processing} onClick={onClose}>Cancel</Button>
                <Button type="submit" variant={rejecting ? 'danger' : 'primary'} disabled={form.processing}>
                    {form.processing ? 'Submitting...' : rejecting ? 'Reject request' : 'Approve request'}
                </Button>
            </div>
        </form>
    </Dialog>;
}

export default function Index({ leaveRequests }) {
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
        const id = new URL(url, 'http://localhost').searchParams.get('request');
        const selected = leaveRequests.find((row) => String(row.id) === id);
        if (selected) setDialog({ type: 'view', request: selected });
    }, [url]);
    const rows = leaveRequests.filter((row) =>
        matchesSearch(search, nameOf(row.employee?.user), row.employee?.user?.email, row.employee?.department?.name, row.leave_type, row.reason)
        && (!status || row.status?.toLowerCase() === status)
        && (!date || (date >= row.start_date && date <= row.end_date)));

    function saved() {
        setNotice(dialog.type === 'approve' ? 'Leave request approved successfully.' : 'Leave request rejected successfully.');
        close();
        setRefreshing(true);
        router.reload({ only: ['leaveRequests'], preserveScroll: true, onFinish: () => setRefreshing(false) });
    }

    return <ManagerLayout title="Leave Requests" breadcrumbParent="Manager" breadcrumbCurrent="Leave Requests">
        <div className="mb-6"><h1 className="text-2xl font-semibold tracking-tight sm:text-3xl">Leave Requests</h1>
            <p className="mt-1 text-sm text-gray-500">Review leave requests from your assigned employees.</p></div>
        {notice && <div role="status" className="mb-5 rounded-lg border border-green-200 bg-green-50 p-3 text-sm text-green-800">{notice}</div>}
        <Panel title="Team Leave Requests" description="Approve or reject pending requests from your team.">
            <Filters>
                <Search value={search} onChange={setSearch} placeholder="Search employee, leave type or reason" />
                <SelectFilter label="All statuses" value={status} onChange={setStatus} options={[
                    ['pending', 'Pending'], ['approved', 'Approved'], ['rejected', 'Rejected'],
                ]} />
                <DateFilter label="Absent on" value={date} onChange={setDate} />
                {(search || status || date) && <Button variant="secondary" onClick={() => { setSearch(''); setStatus(''); setDate(''); }}>Clear filters</Button>}
            </Filters>
            <DataTable key={`${search}:${status}:${date}`} rows={rows} empty={leaveRequests.length ? 'No matching requests. Change or clear your filters to see more.' : 'No leave requests have been submitted by your team yet.'} columns={[
                { key: 'employee', label: 'Employee', render: (row) => <div><p className="font-semibold">{nameOf(row.employee?.user)}</p><p className="mt-1 text-xs text-gray-500">{row.employee?.department?.name || 'N/A'}</p></div> },
                { key: 'leave_type', label: 'Type' },
                { key: 'dates', label: 'Dates', render: (row) => `${formatDate(row.start_date)} – ${formatDate(row.end_date)}` },
                { key: 'days', label: 'Calendar days', render: leaveDays },
                { key: 'submitted', label: 'Submitted', render: (row) => formatDate(row.created_at) },
                { key: 'status', label: 'Status', render: (row) => <Badge value={row.status} /> },
                { key: 'actions', label: 'Actions', render: (row) => <div className="flex flex-wrap gap-2">
                    <Button variant={row.status === 'Pending' ? 'primary' : 'secondary'} disabled={refreshing} onClick={() => setDialog({ type: 'view', request: row })}>{row.status === 'Pending' ? 'Review request' : 'View details'}</Button>
                </div> },
            ]} />
        </Panel>
        {dialog?.type === 'view' && <Dialog title="Leave Request Details" onClose={close}>
            <Details items={[
                ['Employee', nameOf(request.employee?.user)], ['Email', request.employee?.user?.email],
                ['Department', request.employee?.department?.name], ['Position', request.employee?.position],
                ['Leave type', request.leave_type], ['Start date', formatDate(request.start_date)],
                ['End date', formatDate(request.end_date)], ['Calendar days', leaveDays(request)],
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
