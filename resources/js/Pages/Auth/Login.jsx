import React from 'react';
import { useForm } from '@inertiajs/react';
import GuestLayout from '../../Layouts/GuestLayout';
export default function Login({ loginUrl, logoUrl }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        email: '',
        password: '',
    });
    function submit(event) {
        event.preventDefault();
        post(loginUrl, {
            onFinish: () => reset('password'),
        });
    }
    return (
        <GuestLayout title="Login">
        <div className="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-lg">
            <div className="bg-gradient-to-br from-blue-800 to-teal-800 px-6 py-10 text-center">
                <div className="mx-auto flex h-14 w-14 items-center justify-center ">
                    <img src={logoUrl} alt="ALOT Logo" className="h-14 w-14 object-contain rounded-2xl" />
                </div>
                <h1 className="mt-5 text-1xl font-bold tracking-tight text-white">
                    Attendance, Leave &amp; Overtime Tracker
                </h1>
                <p className="mt-1 text-sm font-medium text-blue-100">
                    Management System
                </p>
            </div>
            <div className="px-6 py-8 sm:px-8">
                <div className="mb-7">
                    <h2 className="text-2xl font-bold tracking-tight text-gray-900">
                        Welcome back
                    </h2>
                    <p className="mt-1.5 text-sm text-gray-500">
                        Sign in with your company account.
                    </p>
                </div>
                {Object.keys(errors).length > 0 && (
                    <div role="alert" className="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3">
                        <div className="flex items-start gap-2.5">
                            <svg className="mt-0.5 h-4 w-4 shrink-0 text-red-500" fill="none" stroke="currentColor"
                                strokeWidth="2" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round"
                                    d="M12 8v4m0 4h.01M10.3 3.8 2.9 17a2 2 0 0 0 1.75 3h14.7a2 2 0 0 0 1.75-3l-7.4-13.2a2 2 0 0 0-3.4 0Z" />
                            </svg>
                            <div className="space-y-1 text-sm text-red-700">
                                {Object.entries(errors).map(([field, message]) => <p key={field}>{message}</p>)}
                            </div>
                        </div>
                    </div>
                )}
                <form onSubmit={submit} className="space-y-5">
                    <div>
                        <label htmlFor="email" className="block text-xs font-semibold uppercase tracking-wide text-gray-600">
                            Email
                        </label>
                        <div className="relative mt-2">
                            <svg className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400"
                                fill="none" stroke="currentColor" strokeWidth="1.8" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round"
                                    d="M4 6h16a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1Z" />
                                <path strokeLinecap="round" strokeLinejoin="round" d="m4 7 8 6 8-6" />
                            </svg>
                            <input type="email" id="email" name="email" value={data.email} onChange={(event) => setData('email', event.target.value)} aria-invalid={Boolean(errors.email)} required
                                autoFocus autoComplete="email" placeholder="name@company.com"
                                className="w-full rounded-lg border border-gray-300 bg-white py-3 pl-10 pr-3 text-sm text-gray-900 placeholder-gray-400 shadow-sm outline-none transition focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20" />
                        </div>
                    </div>
                    <div>
                        <label htmlFor="password" className="block text-xs font-semibold uppercase tracking-wide text-gray-600">
                            Password
                        </label>
                        <div className="relative mt-2">
                            <svg className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400"
                                fill="none" stroke="currentColor" strokeWidth="1.8" viewBox="0 0 24 24">
                                <rect x="5" y="10" width="14" height="10" rx="2" />
                                <path strokeLinecap="round" strokeLinejoin="round" d="M8 10V7a4 4 0 0 1 8 0v3" />
                            </svg>
                            <input type="password" id="password" name="password" value={data.password} onChange={(event) => setData('password', event.target.value)} aria-invalid={Boolean(errors.password)} required autoComplete="current-password"
                                placeholder="Enter your password"
                                className="w-full rounded-lg border border-gray-300 bg-white py-3 pl-10 pr-3 text-sm text-gray-900 placeholder-gray-400 shadow-sm outline-none transition focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20" />
                        </div>
                    </div>
                    <button type="submit" disabled={processing}
                        className="flex w-full items-center justify-center gap-2 rounded-lg bg-[#11458c] px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-800 disabled:cursor-wait disabled:opacity-60 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        {processing ? 'Signing in...' : 'Sign in'}
                        <svg className="h-4 w-4" fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" d="M5 12h14M13 6l6 6-6 6" />
                        </svg>
                    </button>
                </form>
                <div className="mt-7 border-t border-gray-200 pt-5">
                    <div className="flex items-start gap-2.5">
                        <svg className="mt-0.5 h-4 w-4 shrink-0 text-teal-500" fill="none" stroke="currentColor"
                            strokeWidth="1.8" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round"
                                d="M12 3 5 6v5c0 4.4 3 8.4 7 9.5 4-1.1 7-5.1 7-9.5V6l-7-3Z" />
                            <path strokeLinecap="round" strokeLinejoin="round" d="m9.5 12 1.5 1.5 3.5-3.5" />
                        </svg>
                        <p className="text-xs leading-5 text-gray-500">
                            Your account is managed by your organization.
                            Use the credentials provided to you to sign in.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        </GuestLayout>
    );
}
