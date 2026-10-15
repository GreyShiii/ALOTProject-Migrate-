import React, { useState } from 'react';
import { Link, useForm } from '@inertiajs/react';
import GuestLayout from '../../Layouts/GuestLayout';
export default function Login({ loginUrl, logoUrl }) {
    const [showPassword, setShowPassword] = useState(false);
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
        <GuestLayout title="Login" wide>
        <div className="login-surface grid overflow-hidden rounded-2xl bg-white shadow-[0_24px_70px_-24px_rgba(20,46,86,0.35)] lg:grid-cols-[0.95fr_1.05fr]">
            <aside aria-label="About ALOT" className="flex flex-col justify-between bg-brand-navy px-7 py-8 text-white sm:px-10 lg:p-12">
                <div className="flex items-center gap-3">
                    <img src={logoUrl} alt="ALOT Logo" className="h-12 w-12 rounded-xl object-contain" />
                    <span className="text-xl font-bold tracking-tight">ALOT</span>
                </div>
                <div className="pt-5 lg:py-16">
                    <h1 className="text-2xl font-semibold leading-[1.15] tracking-tight sm:text-3xl lg:text-5xl">
                        Attendance.<br className="hidden lg:block" />{' '}Leave.<br className="hidden lg:block" />{' '}<span className="text-brand-highlight">Overtime.</span>
                    </h1>
                    <p className="mt-3 text-sm leading-6 text-blue-100 lg:mt-5 lg:max-w-xs">Attendance, Leave &amp; Overtime Tracker</p>
                </div>
                <div className="hidden items-center justify-between gap-4 border-t border-brand-line pt-5 text-xs text-brand-muted lg:flex">
                    <span>Management System</span>
                    <svg aria-hidden="true" className="h-5 w-5 text-brand-highlight" fill="none" stroke="currentColor" strokeWidth="1.5" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" d="M5 12h14m-6-6 6 6-6 6" /></svg>
                </div>
            </aside>
            <div className="flex flex-col justify-center px-7 py-9 sm:px-12 sm:py-12 lg:px-14">
                <div className="mb-7">
                    <h2 className="text-3xl font-semibold tracking-tight text-gray-900">
                        Welcome back
                    </h2>
                    <p className="mt-1.5 text-sm text-gray-500">
                        Sign in with your company account.
                    </p>
                </div>
                {Object.keys(errors).length > 0 && (
                    <div role="alert" className="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3">
                        <div className="flex items-start gap-2.5">
                            <svg aria-hidden="true" className="mt-0.5 h-4 w-4 shrink-0 text-red-500" fill="none" stroke="currentColor"
                                strokeWidth="2" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round"
                                    d="M12 8v4m0 4h.01M10.3 3.8 2.9 17a2 2 0 0 0 1.75 3h14.7a2 2 0 0 0 1.75-3l-7.4-13.2a2 2 0 0 0-3.4 0Z" />
                            </svg>
                            <div className="space-y-1 text-sm text-red-700">
                                {Object.entries(errors).map(([field, message]) => <p key={field} id={`${field}-error`}>{message}</p>)}
                            </div>
                        </div>
                    </div>
                )}
                <form onSubmit={submit} className="space-y-5" aria-busy={processing}>
                    <div>
                        <label htmlFor="email" className="block text-sm font-semibold text-gray-700">
                            Email
                        </label>
                        <div className="relative mt-2">
                            <svg aria-hidden="true" className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400"
                                fill="none" stroke="currentColor" strokeWidth="1.8" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round"
                                    d="M4 6h16a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1Z" />
                                <path strokeLinecap="round" strokeLinejoin="round" d="m4 7 8 6 8-6" />
                            </svg>
                            <input type="email" id="email" name="email" value={data.email} onChange={(event) => setData('email', event.target.value)} aria-invalid={Boolean(errors.email)} aria-describedby={errors.email ? 'email-error' : undefined} required
                                autoFocus autoComplete="email" placeholder="name@company.com"
                                className="w-full rounded-lg border border-gray-300 bg-white py-3.5 pl-10 pr-3 text-base text-gray-900 placeholder-gray-500 outline-none transition focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20" />
                        </div>
                    </div>
                    <div>
                        <label htmlFor="password" className="block text-sm font-semibold text-gray-700">
                            Password
                        </label>
                        <div className="relative mt-2">
                            <svg aria-hidden="true" className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400"
                                fill="none" stroke="currentColor" strokeWidth="1.8" viewBox="0 0 24 24">
                                <rect x="5" y="10" width="14" height="10" rx="2" />
                                <path strokeLinecap="round" strokeLinejoin="round" d="M8 10V7a4 4 0 0 1 8 0v3" />
                            </svg>
                            <input type={showPassword ? 'text' : 'password'} id="password" name="password" value={data.password} onChange={(event) => setData('password', event.target.value)} aria-invalid={Boolean(errors.password)} aria-describedby={errors.password ? 'password-error' : undefined} required autoComplete="current-password"
                                placeholder="Enter your password"
                                className="w-full rounded-lg border border-gray-300 bg-white py-3.5 pl-10 pr-20 text-base text-gray-900 placeholder-gray-500 outline-none transition focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20" />
                            <button type="button" aria-controls="password" aria-label={showPassword ? 'Hide password' : 'Show password'} aria-pressed={showPassword} onClick={() => setShowPassword((visible) => !visible)} className="absolute inset-y-0 right-1 min-w-16 rounded-lg px-3 text-sm font-semibold text-blue-800 hover:bg-blue-50">{showPassword ? 'Hide' : 'Show'}</button>
                        </div>
                    </div>
                    <button type="submit" disabled={processing}
                        className="flex w-full items-center justify-center gap-2 min-h-12 rounded-lg bg-brand-action px-4 py-3.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-800 disabled:cursor-wait disabled:opacity-60 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        {processing ? 'Signing in...' : 'Sign in'}
                        <svg aria-hidden="true" className="h-4 w-4" fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" d="M5 12h14M13 6l6 6-6 6" />
                        </svg>
                    </button>
                </form>
                <p className="mt-4 text-center text-sm">
                    <Link href="/forgot-password" className="inline-flex min-h-11 items-center font-semibold text-blue-800 underline-offset-4 hover:underline">
                        Forgot your password?
                    </Link>
                </p>
                <div className="mt-7 border-t border-gray-200 pt-5">
                    <div className="flex items-start gap-2.5">
                        <svg aria-hidden="true" className="mt-0.5 h-4 w-4 shrink-0 text-blue-600" fill="none" stroke="currentColor"
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
