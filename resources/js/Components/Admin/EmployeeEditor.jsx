import React from 'react';
import useApiForm from './useApiForm';
import { roleOptions } from './UserEditor';
import { Dialog, Field, FormActions, FormErrors, nameOf } from './Ui';

export default function EmployeeEditor({ employee, departments, managers, actionUrl, onClose, onSaved }) {
    const user = employee?.user;
    const editing = Boolean(employee);
    const form = useApiForm({
        first_name: user?.first_name || '',
        last_name: user?.last_name || '',
        email: user?.email || '',
        password: '',
        role: user?.role || 'employee',
        department_id: employee?.department_id || '',
        manager_id: employee?.manager_id || '',
        position: employee?.position || '',
        hire_date: employee?.hire_date || '',
    });

    return <Dialog title={editing ? 'Edit Employee' : 'Add Employee'} onClose={onClose} busy={form.processing}>
        <form onSubmit={async (event) => {
            event.preventDefault();
            const url = editing ? `${actionUrl}/${employee.id}` : actionUrl;
            if (await form.submit(editing ? 'put' : 'post', url)) onSaved();
        }}>
            <FormErrors form={form} />
            <fieldset disabled={form.processing} className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <Field form={form} name="first_name" label="First name" required maxLength={100} autoComplete="given-name" />
                <Field form={form} name="last_name" label="Last name" required maxLength={100} autoComplete="family-name" />
                <div className="sm:col-span-2"><Field form={form} name="email" label="Email" type="email" required autoComplete="email" /></div>
                <div className="sm:col-span-2"><Field form={form} name="password" label={editing ? 'New password (optional)' : 'Password'} type="password" minLength={8} required={!editing} autoComplete="new-password" placeholder={editing ? 'Leave blank to keep current password' : 'At least 8 characters'} /></div>
                <Field form={form} name="role" label="Role" options={roleOptions} required />
                <Field form={form} name="department_id" label="Department" required options={[
                    ['', 'Select department'], ...departments.map((department) => [department.id, department.name]),
                ]} />
                <Field form={form} name="manager_id" label="Manager" options={[
                    ['', 'No manager'], ...managers.filter((manager) => manager.id !== employee?.id).map((manager) => [manager.id, nameOf(manager.user)]),
                ]} />
                <Field form={form} name="position" label="Position" required maxLength={editing ? 100 : 255} />
                <Field form={form} name="hire_date" label="Hire date" type="date" />
            </fieldset>
            <FormActions busy={form.processing} onCancel={onClose} label={editing ? 'Save changes' : 'Add employee'} />
        </form>
    </Dialog>;
}
