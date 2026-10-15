import React, { useState } from 'react';
import { usePage } from '@inertiajs/react';
import UserEditor, { roleOptions, statusOptions } from '../../../Components/Admin/UserEditor';
import { AdminPage, Badge, Button, ConfirmAction, DataTable, Details, Dialog, Filters, formatDate, matchesSearch, nameOf, Panel, refreshPage, Search, SelectFilter } from '../../../Components/Admin/Ui';

export default function Index({ users, actionUrl }) {
    const { auth } = usePage().props;
    const [search, setSearch] = useState('');
    const [role, setRole] = useState('');
    const [status, setStatus] = useState('');
    const [dialog, setDialog] = useState(null);
    const [notice, setNotice] = useState('');
    const close = () => setDialog(null);
    const saved = (message) => { close(); setNotice(message); refreshPage(); };
    const rows = users.filter((user) => matchesSearch(search, nameOf(user), user.email)
        && (!role || user.role === role) && (!status || user.status === status));
    const person = dialog?.person;

    return <AdminPage title="Users" description="Manage user accounts, roles, and access status." notice={notice}>
        <Panel title="User Accounts">
            <Filters>
                <Search value={search} onChange={setSearch} placeholder="Search user or email" />
                <SelectFilter label="All roles" value={role} onChange={setRole} options={roleOptions} />
                <SelectFilter label="All statuses" value={status} onChange={setStatus} options={statusOptions} />
            </Filters>
            <DataTable key={`${search}:${role}:${status}`} rows={rows} empty="No users match your filters." columns={[
                { key: 'name', label: 'Name', render: (row) => nameOf(row) },
                { key: 'email', label: 'Email' },
                { key: 'role', label: 'Role', render: (row) => <span className="capitalize">{row.role || 'N/A'}</span> },
                { key: 'status', label: 'Status', render: (row) => <Badge value={row.status} /> },
                { key: 'actions', label: 'Actions', render: (row) => <div className="flex flex-wrap gap-2">
                    <Button variant="secondary" onClick={() => setDialog({ type: 'view', person: row })}>View</Button>
                    <Button variant="secondary" onClick={() => setDialog({ type: 'edit', person: row })}>Edit</Button>
                    <Button variant={row.status === 'active' ? 'danger' : 'secondary'} disabled={row.id === auth.user.id}
                        onClick={() => setDialog({ type: row.status === 'active' ? 'deactivate' : 'activate', person: row })}>
                        {row.status === 'active' ? 'Deactivate' : 'Activate'}
                    </Button>
                </div> },
            ]} />
        </Panel>
        {dialog?.type === 'edit' && <UserEditor user={person} actionUrl={actionUrl} onClose={close} onSaved={() => saved('User updated successfully.')} />}
        {dialog?.type === 'view' && <Dialog title="User Details" onClose={close}>
            <Details items={[
                ['Name', nameOf(person)], ['Email', person.email], ['Role', person.role], ['Status', <Badge value={person.status} />],
                ['Department', person.employee?.department?.name], ['Position', person.employee?.position],
                ['Manager', nameOf(person.employee?.manager?.user)], ['Hire date', formatDate(person.employee?.hire_date)],
                ['Account created', formatDate(person.created_at)],
            ]} />
        </Dialog>}
        {['activate', 'deactivate'].includes(dialog?.type) && <ConfirmAction title={`${dialog.type === 'activate' ? 'Activate' : 'Deactivate'} ${nameOf(person)}?`}
            description={dialog.type === 'activate' ? 'This user will be able to sign in again.' : 'This user will no longer be able to sign in.'}
            url={`${actionUrl}/${person.id}/${dialog.type}`} method="post" label={dialog.type === 'activate' ? 'Activate' : 'Deactivate'}
            onClose={close} onSaved={() => saved('Account status updated successfully.')} />}
    </AdminPage>;
}
