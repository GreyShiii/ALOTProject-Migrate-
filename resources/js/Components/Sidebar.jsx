import React, { useEffect, useRef, useState } from 'react';
import { Link, router } from '@inertiajs/react';
import LayoutIcon from './LayoutIcon';

export function isNavigationActive(url, href) {
    const pathname = (value) => new URL(value, 'http://localhost').pathname.replace(/\/$/, '');
    const current = pathname(url);
    const target = pathname(href);
    return current === target || current.startsWith(`${target}/`);
}

export default function Sidebar({ open, onClose, role, navigation = [], url, logoUrl, logoutUrl }) {
    const closeButton = useRef(null);
    const [loggingOut, setLoggingOut] = useState(false);

    useEffect(() => {
        if (open) closeButton.current?.focus();
    }, [open]);

    function handleKeyDown(event) {
        if (!open || window.matchMedia('(min-width: 1024px)').matches) return;
        if (event.key === 'Escape') {
            event.preventDefault();
            onClose();
        }
        if (event.key === 'Tab') {
            const controls = [...event.currentTarget.querySelectorAll('a[href], button:not([disabled])')];
            const first = controls[0];
            const last = controls[controls.length - 1];
            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last?.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first?.focus();
            }
        }
    }

    return (
        <aside id="app-sidebar" aria-label={`${role || 'Account'} sidebar`} onKeyDown={handleKeyDown}
            className={`fixed inset-y-0 left-0 z-40 flex w-72 shrink-0 flex-col bg-brand-sidebar text-white transition-transform duration-200 lg:visible lg:static lg:translate-x-0 ${open ? 'visible translate-x-0' : 'invisible -translate-x-full'}`}>
            <div className="flex items-center justify-between gap-2 px-6 py-6">
                <div className="flex items-center gap-3">
                    <img src={logoUrl} alt="ALOT Logo" className="h-10 w-10 shrink-0 rounded-full object-cover" />
                    <div>
                        <p className="text-sm font-semibold leading-tight">Attendance, Leave &amp; Overtime Tracker</p>
                        <p className="text-xs text-brand-muted">Management System</p>
                    </div>
                </div>
                <button ref={closeButton} type="button" aria-label="Close navigation menu" onClick={onClose}
                    className="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-lg text-blue-100 hover:bg-brand-line hover:text-white lg:hidden">
                    <LayoutIcon name="close" />
                </button>
            </div>
            <nav aria-label={`${role || 'Account'} navigation`} className="flex-1 overflow-y-auto px-4 py-2">
                <p className="mb-3 px-3 text-xs font-semibold tracking-wide text-blue-100">{role} Menu</p>
                <ul className="space-y-1">
                    {navigation.map((item) => {
                        const active = isNavigationActive(url, item.href);
                        const props = {
                            href: item.href,
                            onClick: onClose,
                            'aria-current': active ? 'page' : undefined,
                            className: `relative flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition ${active ? 'bg-brand-selected font-semibold text-white' : 'text-blue-100 hover:bg-brand-line hover:text-white'}`,
                        };
                        const content = <>
                            <LayoutIcon name={item.icon} />
                            {item.label}
                            {active && <span className="absolute right-2 top-1/2 h-5 w-1 -translate-y-1/2 rounded-full bg-brand-highlight" />}
                        </>;

                        return <li key={item.href}>
                            {item.inertia ? <Link {...props}>{content}</Link> : <a {...props}>{content}</a>}
                        </li>;
                    })}
                </ul>
            </nav>
            <div className="border-t border-brand-line px-4 py-4">
                <button type="button" disabled={loggingOut} onClick={() => router.post(logoutUrl, {}, {
                    onStart: () => setLoggingOut(true),
                    onFinish: () => setLoggingOut(false),
                })} className="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-blue-100 transition hover:bg-brand-line hover:text-white disabled:opacity-50">
                    <LayoutIcon name="logout" />{loggingOut ? 'Signing out...' : 'Sign out'}
                </button>
            </div>
        </aside>
    );
}
