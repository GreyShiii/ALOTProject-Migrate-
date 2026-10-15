import React from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import { useState } from 'react';

const links = [
    { label: 'Dashboard', href: '/manager/dashboard' },
    { label: 'Attendance', href: '/manager/attendance' },
    { label: 'Leave requests', href: '/manager/leave' },
    { label: 'Overtime requests', href: '/manager/overtime' },
    { label: 'My team', href: '/manager/team', inertia: true },
    { label: 'Profile', href: '/manager/profile' },
];

export default function ManagerLayout({ children }) {
    const page = usePage();
    const { auth } = page.props;
    const { url } = page;
    const [menuOpen, setMenuOpen] = useState(false);
    const currentUser = auth?.user;

    return (
        <div className="flex min-h-screen bg-gray-50 text-gray-900">
            {menuOpen && (
                <button
                    type="button"
                    aria-label="Close navigation menu"
                    className="fixed inset-0 z-30 bg-black/40 lg:hidden"
                    onClick={() => setMenuOpen(false)}
                />
            )}

            <aside className={`fixed inset-y-0 left-0 z-40 flex w-72 flex-col bg-[#0f2138] text-white transition-transform lg:static lg:translate-x-0 ${menuOpen ? 'translate-x-0' : '-translate-x-full'}`}>
                <div className="border-b border-white/10 px-6 py-6">
                    <p className="text-sm font-semibold">Attendance, Leave &amp; Overtime Tracker</p>
                    <p className="mt-1 text-xs text-slate-400">Management System</p>
                </div>

                <nav aria-label="Manager navigation" className="flex-1 space-y-1 overflow-y-auto px-4 py-5">
                    <p className="mb-3 px-3 text-[11px] font-semibold uppercase tracking-widest text-slate-500">Manager menu</p>
                    {links.map((link) => {
                        const active = url === link.href || url.startsWith(`${link.href}/`);
                        const className = `block rounded-lg px-3 py-2.5 text-sm transition ${active ? 'bg-slate-800 font-semibold text-white' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white'}`;

                        return link.inertia ? (
                            <Link key={link.href} href={link.href} className={className} onClick={() => setMenuOpen(false)}>
                                {link.label}
                            </Link>
                        ) : (
                            <a key={link.href} href={link.href} className={className}>
                                {link.label}
                            </a>
                        );
                    })}
                </nav>

                <div className="border-t border-white/10 p-4">
                    <button
                        type="button"
                        onClick={() => router.post('/logout')}
                        className="w-full rounded-lg px-3 py-2 text-left text-sm text-slate-300 hover:bg-slate-800/60 hover:text-white"
                    >
                        Log out
                    </button>
                </div>
            </aside>

            <div className="flex min-w-0 flex-1 flex-col">
                <header className="flex min-h-16 items-center justify-between border-b border-gray-200 bg-white px-4 sm:px-8">
                    <div className="flex items-center gap-4">
                        <button type="button" aria-label="Open navigation menu" className="text-gray-600 lg:hidden" onClick={() => setMenuOpen(true)}>
                            <svg className="h-6 w-6" fill="none" stroke="currentColor" strokeWidth="1.8" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" d="M4 6h16M4 12h16M4 18h16" /></svg>
                        </button>
                        <p className="text-sm text-gray-500">Manager <span className="mx-2 text-gray-300">/</span><span className="font-semibold text-gray-900">My Team</span></p>
                    </div>
                    <div className="text-right">
                        <p className="text-sm font-semibold">{currentUser?.name ?? 'Manager'}</p>
                        <p className="text-xs capitalize text-gray-500">{currentUser?.role ?? 'Manager'}</p>
                    </div>
                </header>

                <main className="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
                    {children}
                </main>
            </div>
        </div>
    );
}
