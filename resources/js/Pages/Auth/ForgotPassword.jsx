import React from 'react';
import { Link, useForm, usePage } from '@inertiajs/react';
import GuestLayout from '../../Layouts/GuestLayout';

export default function ForgotPassword() {
    const { flash } = usePage().props;
    const { data, setData, post, processing, errors } = useForm({ email: '' });

    function submit(event) {
        event.preventDefault();
        post('/forgot-password');
    }

    return (
        <GuestLayout title="Forgot password">
            <div className="rounded-2xl bg-white p-6 shadow-[0_16px_48px_-16px_rgba(24,49,83,0.22)] sm:p-8">
                <h1 className="text-2xl font-semibold tracking-tight text-brand-navy">Reset your password</h1>
                <p className="mt-2 text-sm text-gray-600">
                    Enter your account email and we will send you a password reset link.
                </p>

                {flash?.status && (
                    <p role="status" className="mt-5 rounded-lg bg-green-50 px-4 py-3 text-sm text-green-700">
                        {flash.status}
                    </p>
                )}

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
                    <button type="submit" disabled={processing} className="min-h-12 w-full rounded-lg bg-brand-action px-4 py-3 text-sm font-semibold text-white hover:bg-brand-hover disabled:opacity-60">
                        {processing ? 'Sending link…' : 'Send reset link'}
                    </button>
                </form>

                <p className="mt-5 text-center text-sm">
                    <Link href="/login" className="inline-flex min-h-11 items-center font-semibold text-blue-800 underline-offset-4 hover:underline">Back to sign in</Link>
                </p>
            </div>
        </GuestLayout>
    );
}
