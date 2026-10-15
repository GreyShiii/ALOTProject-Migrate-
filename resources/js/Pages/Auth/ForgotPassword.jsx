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
            <div className="rounded-2xl border border-gray-200 bg-white p-6 shadow-lg sm:p-8">
                <h1 className="text-2xl font-bold text-gray-900">Reset your password</h1>
                <p className="mt-2 text-sm text-gray-600">
                    Enter your account email and we will send you a password reset link.
                </p>

                {flash?.status && (
                    <p role="status" className="mt-5 rounded-lg bg-green-50 px-4 py-3 text-sm text-green-700">
                        {flash.status}
                    </p>
                )}

                <form onSubmit={submit} className="mt-6 space-y-4">
                    <div>
                        <label htmlFor="email" className="block text-sm font-medium text-gray-700">Email</label>
                        <input
                            id="email"
                            type="email"
                            autoComplete="email"
                            value={data.email}
                            onChange={(event) => setData('email', event.target.value)}
                            className="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                            required
                        />
                        {errors.email && <p className="mt-1 text-sm text-red-600">{errors.email}</p>}
                    </div>
                    <button type="submit" disabled={processing} className="w-full rounded-lg bg-blue-800 px-4 py-3 text-sm font-semibold text-white hover:bg-blue-900 disabled:opacity-60">
                        {processing ? 'Sending link…' : 'Send reset link'}
                    </button>
                </form>

                <p className="mt-5 text-center text-sm">
                    <Link href="/login" className="font-medium text-blue-800 hover:underline">Back to sign in</Link>
                </p>
            </div>
        </GuestLayout>
    );
}
