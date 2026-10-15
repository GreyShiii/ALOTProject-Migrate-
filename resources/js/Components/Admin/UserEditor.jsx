import React from 'react';
import useApiForm from './useApiForm';
import { Dialog, Field, FormActions, FormErrors } from './Ui';

export const roleOptions = [['employee', 'Employee'], ['manager', 'Manager'], ['admin', 'Admin']];
export const statusOptions = [['active', 'Active'], ['inactive', 'Inactive']];

export default function UserEditor({ user, actionUrl, onClose, onSaved }) {
    const form = useApiForm({
        first_name: user.first_name,
        last_name: user.last_name,
        email: user.email,
        password: '',
        role: user.role || 'employee',
        status: user.status || 'active',
    });

    return <Dialog title="Edit User" onClose={onClose} busy={form.processing}>
        <form onSubmit={async (event) => {
            event.preventDefault();
            if (await form.submit('put', `${actionUrl}/${user.id}`)) onSaved();
        }}>
            <FormErrors form={form} />
            <fieldset disabled={form.processing} className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <Field form={form} name="first_name" label="First name" required maxLength={255} autoComplete="given-name" />
                <Field form={form} name="last_name" label="Last name" required maxLength={255} autoComplete="family-name" />
                <div className="sm:col-span-2"><Field form={form} name="email" label="Email" type="email" required maxLength={255} autoComplete="email" /></div>
                <div className="sm:col-span-2"><Field form={form} name="password" label="New password (optional)" type="password" minLength={8} autoComplete="new-password" placeholder="Leave blank to keep current password" /></div>
                <Field form={form} name="role" label="Role" options={roleOptions} required />
                <Field form={form} name="status" label="Status" options={statusOptions} required />
            </fieldset>
            <FormActions busy={form.processing} onCancel={onClose} />
        </form>
    </Dialog>;
}
