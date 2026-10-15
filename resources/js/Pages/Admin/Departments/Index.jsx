import React, { useEffect, useState } from 'react';
import useApiForm from '../../../Components/Admin/useApiForm';
import { AdminPage, Button, ConfirmAction, DataTable, Dialog, Field, Filters, FormActions, FormErrors, formatDate, matchesSearch, Panel, refreshPage, Search } from '../../../Components/Admin/Ui';

function DepartmentEditor({ department, actionUrl, onClose, onSaved }) {
    const form = useApiForm({ name: department?.name || '' });
    return <Dialog title={department ? 'Edit Department' : 'Add Department'} onClose={onClose} busy={form.processing}>
        <form onSubmit={async (event) => {
            event.preventDefault();
            if (await form.submit(department ? 'put' : 'post', department ? `${actionUrl}/${department.id}` : actionUrl)) onSaved();
        }}>
            <FormErrors form={form} />
            <fieldset disabled={form.processing}><Field form={form} name="name" label="Department name" required maxLength={255} /></fieldset>
            <FormActions busy={form.processing} onCancel={onClose} label={department ? 'Save changes' : 'Add department'} />
        </form>
    </Dialog>;
}

export default function Index({ departments, actionUrl, openDialog = null }) {
    const [search, setSearch] = useState('');
    const [dialog, setDialog] = useState(openDialog);
    const [notice, setNotice] = useState('');
    const close = () => setDialog(null);
    useEffect(() => setDialog(openDialog), [openDialog]);
    const saved = (message) => { close(); setNotice(message); refreshPage(); };
    const department = dialog?.department;

    return <AdminPage title="Department Management" description="Departments used across employee records and reporting." notice={notice}
        action={<Button onClick={() => setDialog({ type: 'edit' })}>Add department</Button>}>
        <Panel title="Departments">
            <Filters><Search value={search} onChange={setSearch} placeholder="Search department" /></Filters>
            <DataTable key={search} rows={departments.filter((item) => matchesSearch(search, item.name))} columns={[
                { key: 'id', label: 'Department ID', render: (row) => `DEP-${String(row.id).padStart(2, '0')}` },
                { key: 'name', label: 'Department' },
                { key: 'employees_count', label: 'Employees', render: (row) => row.employees_count },
                { key: 'created_at', label: 'Created date', render: (row) => formatDate(row.created_at) },
                { key: 'actions', label: 'Actions', render: (row) => <div className="flex gap-2">
                    <Button variant="secondary" onClick={() => setDialog({ type: 'edit', department: row })}>Edit</Button>
                    <Button variant="danger" onClick={() => setDialog({ type: 'delete', department: row })}>Delete</Button>
                </div> },
            ]} />
        </Panel>
        {dialog?.type === 'edit' && <DepartmentEditor department={department} actionUrl={actionUrl} onClose={close} onSaved={() => saved(department ? 'Department updated successfully.' : 'Department created successfully.')} />}
        {dialog?.type === 'delete' && <ConfirmAction title={`Delete ${department.name}?`} description="Departments with assigned employees cannot be deleted." url={`${actionUrl}/${department.id}`} onClose={close} onSaved={() => saved('Department deleted successfully.')} />}
    </AdminPage>;
}
