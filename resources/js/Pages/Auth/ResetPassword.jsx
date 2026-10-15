import React from 'react';
import { Link, useForm } from '@inertiajs/react';
import GuestLayout from '../../Layouts/GuestLayout';

export default function ResetPassword({ email, token }) {
    const { data, setData, post, processing, errors } = useForm({
        email: email ?? '',
        token: token ?? '',
        password: '',
        password_confirmation: '',
    });

    function submit(event) {
        event.preventDefault();
        post('/reset-password');
    }

    return (
        <GuestLayout title="Choose a new password">
            <div className="rounded-2xl bg-white p-6 shadow-[0_16px_48px_-16px_rgba(24,49,83,0.22)] sm:p-8">
                <h1 className="text-2xl font-semibold tracking-tight text-brand-navy">Choose a new password</h1>
                <p className="mt-2 text-sm text-gray-600">Set a new password for your account.</p>

                <form onSubmit={submit} className="mt-6 space-y-5" aria-busy={processing}>
                    <div>
                        <label htmlFor="email" className="block text-sm font-medium text-gray-700">Email</label>
                        <input
                            id="email"
                            name="email"
                            aria-invalid={Boolean(errors.email)}
                            aria-describedby={errors.email ? 'email-error' : undefined}
                            type="email"
                            autoComplete="email"
                            value={data.email}
                            onChange={(event) => setData('email', event.target.value)}
                            className="mt-2 min-h-12 w-full rounded-lg border border-gray-300 bg-white px-3 py-3 text-base focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                            required
                        />
                        {errors.email && <p id="email-error" role="alert" className="mt-2 text-sm text-red-700">{errors.email}</p>}
                    </div>
                    <div>
                        <label htmlFor="password" className="block text-sm font-medium text-gray-700">New password</label>
                        <input
                            id="password"
                            name="password"
                            aria-invalid={Boolean(errors.password)}
                            aria-describedby={errors.password ? 'password-error' : undefined}
                            type="password"
                            autoComplete="new-password"
                            value={data.password}
                            onChange={(event) => setData('password', event.target.value)}
                            className="mt-2 min-h-12 w-full rounded-lg border border-gray-300 bg-white px-3 py-3 text-base focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                            required
                        />
                        {errors.password && <p id="password-error" role="alert" className="mt-2 text-sm text-red-700">{errors.password}</p>}
                    </div>
                    <div>
                        <label htmlFor="password_confirmation" className="block text-sm font-medium text-gray-700">Confirm new password</label>
                        <input
                            id="password_confirmation"
                            name="password_confirmation"
                            aria-invalid={Boolean(errors.password_confirmation)}
                            aria-describedby={errors.password_confirmation ? 'password_confirmation-error' : undefined}
                            type="password"
                            autoComplete="new-password"
                            value={data.password_confirmation}
                            onChange={(event) => setData('password_confirmation', event.target.value)}
                            className="mt-2 min-h-12 w-full rounded-lg border border-gray-300 bg-white px-3 py-3 text-base focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                            required
                        />
                    </div>
                    {errors.password_confirmation && <p id="password_confirmation-error" role="alert" className="text-sm text-red-700">{errors.password_confirmation}</p>}
                    {errors.token && <p role="alert" className="rounded-lg bg-red-50 p-3 text-sm text-red-700">{errors.token} <Link href="/forgot-password" className="font-semibold underline underline-offset-4">Request a new reset link</Link></p>}
                    <button type="submit" disabled={processing} className="min-h-12 w-full rounded-lg bg-brand-action px-4 py-3 text-sm font-semibold text-white hover:bg-brand-hover disabled:opacity-60">
                        {processing ? 'Updating password…' : 'Update password'}
                    </button>
                </form>

                <p className="mt-5 text-center text-sm">
                    <Link href="/login" className="inline-flex min-h-11 items-center font-semibold text-blue-800 underline-offset-4 hover:underline">Back to sign in</Link>
                </p>
            </div>
        </GuestLayout>
    );
}
