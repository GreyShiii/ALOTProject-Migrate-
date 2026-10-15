import React, { useEffect, useId, useRef, useState } from 'react';
import { router } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import useApiForm from './useApiForm';

export const inputClass = 'w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20';
export const nameOf = (user) => user ? `${user.first_name || ''} ${user.last_name || ''}`.trim() : 'N/A';
export const matchesSearch = (search, ...values) => values.filter(Boolean).join(' ').toLowerCase().includes(search.trim().toLowerCase());

export function formatDate(value) {
    if (!value) return 'N/A';
    const date = new Date(/^\d{4}-\d{2}-\d{2}$/.test(value) ? `${value}T00:00:00` : value);
    return Number.isNaN(date.getTime()) ? 'N/A' : date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

export function AdminPage({ title, description, children, action, notice }) {
    return <AppLayout title={title} breadcrumbParent="Management" breadcrumbCurrent={title}>
        <div className="mb-6 flex flex-wrap items-center justify-between gap-4">
            <div><h1 className="text-xl font-bold tracking-tight sm:text-2xl">{title}</h1>
                {description && <p className="mt-1 text-sm text-gray-500">{description}</p>}</div>
            {action}
        </div>
        {notice && <div role="status" className="mb-5 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{notice}</div>}
        <div className="space-y-6">{children}</div>
    </AppLayout>;
}

export function Panel({ title, description, children }) {
    return <section className="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        {title && <div className="border-b border-gray-200 px-5 py-4">
            <h2 className="text-lg font-semibold">{title}</h2>
            {description && <p className="mt-1 text-sm text-gray-500">{description}</p>}
        </div>}
        {children}
    </section>;
}

export function Button({ children, variant = 'primary', className = '', type = 'button', ...props }) {
    const colors = { primary: 'bg-[#11458c] text-white hover:bg-blue-800', secondary: 'border border-gray-300 bg-white text-gray-700 hover:bg-gray-50', danger: 'bg-red-600 text-white hover:bg-red-700' };
    return <button type={type} {...props} className={`inline-flex items-center justify-center rounded-lg px-3 py-2 text-sm font-semibold transition focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:cursor-wait disabled:opacity-50 ${colors[variant]} ${className}`}>{children}</button>;
}

export function Badge({ value }) {
    const status = String(value || 'N/A').toLowerCase();
    const color = ['active', 'approved', 'completed'].includes(status) ? 'bg-green-50 text-green-700'
        : ['rejected', 'inactive'].includes(status) ? 'bg-red-50 text-red-700'
            : ['pending', 'working'].includes(status) ? 'bg-amber-50 text-amber-700' : 'bg-gray-100 text-gray-600';
    return <span className={`inline-flex rounded-full px-2.5 py-1 text-xs font-semibold capitalize ${color}`}>{value || 'N/A'}</span>;
}

export function Filters({ children }) {
    return <div className="flex flex-wrap gap-3 border-b border-gray-200 p-4">{children}</div>;
}

export function Search({ value, onChange, placeholder = 'Search...' }) {
    return <input type="search" aria-label={placeholder} placeholder={placeholder} value={value} onChange={(event) => onChange(event.target.value)} className={`${inputClass} min-w-48 flex-1 sm:w-auto`} />;
}

export function SelectFilter({ label, value, onChange, options }) {
    return <select aria-label={label} value={value} onChange={(event) => onChange(event.target.value)} className={`${inputClass} sm:w-48`}>
        <option value="">{label}</option>
        {options.map(([id, text]) => <option key={id} value={id}>{text}</option>)}
    </select>;
}

export function DateFilter({ value, onChange }) {
    return <input type="date" aria-label="Filter by date" value={value} onChange={(event) => onChange(event.target.value)} className={`${inputClass} sm:w-48`} />;
}

export function DataTable({ rows, columns, empty = 'No records found.', pageSize = 10, pagination, onPageChange, paginationLabel = 'Table pagination' }) {
    const [page, setPage] = useState(1);
    const perPage = pagination?.per_page || pageSize;
    const total = pagination?.total ?? rows.length;
    const lastPage = pagination?.last_page || Math.max(1, Math.ceil(total / perPage));
    const current = pagination?.current_page || Math.min(page, lastPage);
    const visible = pagination ? rows : rows.slice((current - 1) * perPage, current * perPage);
    const changePage = onPageChange || setPage;
    const cell = (row, column) => column.render ? column.render(row) : row[column.key] || 'N/A';

    return <>
        <div className="hidden overflow-x-auto md:block">
            <table className="w-full divide-y divide-gray-200">
                <thead className="bg-gray-50"><tr>{columns.map((column) => <th key={column.key} scope="col" className="whitespace-nowrap px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">{column.label}</th>)}</tr></thead>
                <tbody className="divide-y divide-gray-200">
                    {visible.map((row, index) => <tr key={row.id ?? index} className="hover:bg-gray-50">{columns.map((column) => <td key={column.key} className="max-w-xs px-4 py-4 text-sm text-gray-700"><div className="break-words">{cell(row, column)}</div></td>)}</tr>)}
                </tbody>
            </table>
        </div>
        <div className="divide-y divide-gray-200 md:hidden">
            {visible.map((row, index) => <dl key={row.id ?? index} className="grid grid-cols-2 gap-3 p-4">{columns.map((column) => <div key={column.key} className={column.key === 'actions' ? 'col-span-2' : 'min-w-0'}>
                <dt className="text-xs font-semibold uppercase tracking-wide text-gray-400">{column.label}</dt>
                <dd className="mt-1 break-words text-sm text-gray-700">{cell(row, column)}</dd>
            </div>)}</dl>)}
        </div>
        {rows.length === 0 && <p className="px-5 py-10 text-center text-sm text-gray-500">{empty}</p>}
        {total > 0 && <nav aria-label={paginationLabel} className="flex flex-wrap items-center justify-between gap-3 border-t border-gray-200 px-5 py-4">
            <p aria-live="polite" className="text-sm text-gray-500">Showing {(current - 1) * perPage + 1}–{Math.min(current * perPage, total)} of {total}</p>
            <div className="flex items-center gap-3">
                <Button variant="secondary" disabled={current === 1} onClick={() => changePage(current - 1)}>Previous</Button>
                <span className="text-sm text-gray-500">{current} / {lastPage}</span>
                <Button variant="secondary" disabled={current === lastPage} onClick={() => changePage(current + 1)}>Next</Button>
            </div>
        </nav>}
    </>;
}

export function Dialog({ title, children, onClose, busy = false }) {
    const element = useRef(null);
    const titleId = useId();
    useEffect(() => {
        const dialog = element.current;
        dialog.showModal();
        return () => dialog.close();
    }, []);
    return <dialog ref={element} aria-labelledby={titleId} onCancel={(event) => { event.preventDefault(); if (!busy) onClose(); }}
        className="fixed inset-0 m-auto max-h-[90dvh] w-[calc(100%-2rem)] max-w-xl overflow-y-auto rounded-xl border-0 bg-white p-0 text-gray-900 shadow-xl backdrop:bg-black/40">
        <div className="flex items-center justify-between border-b border-gray-200 px-6 py-4">
            <h2 id={titleId} className="text-lg font-semibold">{title}</h2>
            <button type="button" aria-label="Close dialog" disabled={busy} onClick={onClose} className="rounded px-2 py-1 text-xl text-gray-500 hover:bg-gray-100 disabled:opacity-50">×</button>
        </div>
        <div className="p-6">{children}</div>
    </dialog>;
}

export function Field({ form, name, label, type = 'text', options, ...props }) {
    const id = useId();
    const error = form.errors[name];
    const errorText = Array.isArray(error) ? error.join(' ') : error;
    const control = { id, name, value: form.data[name] ?? '', onChange: (event) => form.setField(name, event.target.value), className: inputClass, 'aria-invalid': Boolean(error), 'aria-describedby': error ? `${id}-error` : undefined, ...props };
    return <div>
        <label htmlFor={id} className="mb-2 block text-sm font-semibold text-gray-600">{label}</label>
        {options ? <select {...control}>{options.map(([value, text]) => <option key={value} value={value}>{text}</option>)}</select>
            : type === 'textarea' ? <textarea {...control} /> : <input type={type} {...control} />}
        {error && <p id={`${id}-error`} className="mt-1 text-sm text-red-600">{errorText}</p>}
    </div>;
}

export function FormErrors({ form }) {
    return form.message && <div role="alert" className="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">{form.message}</div>;
}

export function FormActions({ busy, onCancel, label = 'Save changes' }) {
    return <div className="mt-6 flex justify-end gap-3">
        {onCancel && <Button variant="secondary" disabled={busy} onClick={onCancel}>Cancel</Button>}
        <Button type="submit" disabled={busy}>{busy ? 'Saving...' : label}</Button>
    </div>;
}

export function Details({ items }) {
    return <dl className="grid grid-cols-1 gap-4 sm:grid-cols-2">{items.map(([label, value]) => <div key={label} className="min-w-0">
        <dt className="text-xs font-semibold uppercase tracking-wide text-gray-400">{label}</dt>
        <dd className="mt-1 whitespace-pre-wrap break-words text-sm text-gray-700">{value ?? 'N/A'}</dd>
    </div>)}</dl>;
}

export function ConfirmAction({ title, description, url, method = 'delete', onClose, onSaved, label = 'Delete' }) {
    const form = useApiForm();
    return <Dialog title={title} onClose={onClose} busy={form.processing}>
        <form onSubmit={async (event) => { event.preventDefault(); if (await form.submit(method, url)) onSaved(); }}>
            <FormErrors form={form} />
            <p className="text-sm text-gray-600">{description}</p>
            <div className="mt-6 flex justify-end gap-3">
                <Button variant="secondary" disabled={form.processing} onClick={onClose}>Cancel</Button>
                <Button type="submit" variant="danger" disabled={form.processing}>{form.processing ? 'Please wait...' : label}</Button>
            </div>
        </form>
    </Dialog>;
}

export function refreshPage() {
    router.reload({ preserveScroll: true });
}
