import React, { useState } from 'react';
import ManagerLayout from '../../../Layouts/ManagerLayout';
import useApiForm from '../../../Components/Admin/useApiForm';
import { Badge, Button, Details, Dialog, Field, FormActions, FormErrors, formatDate, nameOf, Panel, refreshPage } from '../../../Components/Admin/Ui';

function PasswordField({ form, name, label, ...props }) {
    const [visible, setVisible] = useState(false);
    return <div>
        <Field form={form} name={name} label={label} type={visible ? 'text' : 'password'} {...props} />
        <button type="button" aria-label={`${visible ? 'Hide' : 'Show'} ${label.toLowerCase()}`} aria-pressed={visible}
            className="mt-1 rounded text-xs font-medium text-blue-700 hover:underline focus:outline-none focus:ring-2 focus:ring-blue-500"
            onClick={() => setVisible(!visible)}>{visible ? 'Hide' : 'Show'} password</button>
    </div>;
}

function PasswordEditor({ passwordUrl, onClose, onSaved }) {
    const form = useApiForm({ current_password: '', new_password: '', new_password_confirmation: '' });
    return <Dialog title="Change Password" onClose={onClose} busy={form.processing}>
        <form onSubmit={async (event) => {
            event.preventDefault();
            if (await form.submit('put', passwordUrl)) onSaved();
        }}>
            <FormErrors form={form} />
            <fieldset disabled={form.processing} className="space-y-4">
                <PasswordField form={form} name="current_password" label="Current password" required autoComplete="current-password" />
                <PasswordField form={form} name="new_password" label="New password" minLength={8} required autoComplete="new-password" />
                <PasswordField form={form} name="new_password_confirmation" label="Confirm new password" minLength={8} required autoComplete="new-password" />
            </fieldset>
            <FormActions busy={form.processing} onCancel={onClose} label="Update password" />
        </form>
    </Dialog>;
}

export default function Index({ user, employee, actionUrl, passwordUrl }) {
    const form = useApiForm({ first_name: user.first_name, last_name: user.last_name, email: user.email });
    const [notice, setNotice] = useState('');
    const [passwordOpen, setPasswordOpen] = useState(false);

    return <ManagerLayout title="Profile" breadcrumbParent="Manager" breadcrumbCurrent="Profile">
        <div className="mb-6"><h1 className="text-2xl font-semibold tracking-tight sm:text-3xl">Profile</h1>
            <p className="mt-1 text-sm text-gray-500">Your personal, employment and account information.</p></div>
        {notice && <div role="status" className="mb-5 rounded-lg border border-green-200 bg-green-50 p-3 text-sm text-green-800">{notice}</div>}
        <div className="grid grid-cols-1 gap-6 xl:grid-cols-2">
            <Panel title="Personal Information">
                <form className="p-5" onSubmit={async (event) => {
                    event.preventDefault();
                    if (await form.submit('put', actionUrl)) { setNotice('Profile updated successfully.'); refreshPage(); }
                }}>
                    <FormErrors form={form} />
                    <fieldset disabled={form.processing} className="space-y-4">
                        <Field form={form} name="first_name" label="First name" required maxLength={255} autoComplete="given-name" />
                        <Field form={form} name="last_name" label="Last name" required maxLength={255} autoComplete="family-name" />
                        <Field form={form} name="email" label="Email" type="email" required maxLength={255} autoComplete="email" />
                    </fieldset>
                    <FormActions busy={form.processing} />
                </form>
            </Panel>
            <div className="space-y-6">
                <Panel title="Employment Information">
                    <div className="p-5">{employee ? <Details items={[
                        ['Employee ID', employee.id], ['Department', employee.department?.name], ['Position', employee.position],
                        ['Manager', nameOf(employee.manager?.user)], ['Hire date', formatDate(employee.hire_date)],
                    ]} /> : <p className="text-sm text-gray-500">No employee record linked to this account.</p>}</div>
                </Panel>
                <Panel title="Account & Security">
                    <div className="space-y-5 p-5">
                        <Details items={[
                            ['Name', nameOf(user)], ['Role', <span className="capitalize">{user.role}</span>],
                            ['Status', <Badge value={user.status} />], ['Account created', formatDate(user.created_at)],
                        ]} />
                        <Button variant="secondary" onClick={() => setPasswordOpen(true)}>Change password</Button>
                    </div>
                </Panel>
            </div>
        </div>
        {passwordOpen && <PasswordEditor passwordUrl={passwordUrl} onClose={() => setPasswordOpen(false)} onSaved={() => {
            setPasswordOpen(false); setNotice('Password updated successfully.');
        }} />}
    </ManagerLayout>;
}
