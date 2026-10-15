import React from 'react';
import LayoutIcon from './LayoutIcon';

export default function Topbar({ user, breadcrumbParent, breadcrumbCurrent, menuOpen, onOpenMenu, menuButtonRef }) {
    const initials = `${user?.first_name?.slice(0, 1) || ''}${user?.last_name?.slice(0, 1) || ''}`.toUpperCase();

    return (
        <header className="flex shrink-0 items-center justify-between gap-3 border-b border-gray-200 bg-white px-4 py-4 sm:px-8">
            <div className="flex min-w-0 items-center gap-2 sm:gap-4">
                <button ref={menuButtonRef} type="button" aria-label="Open navigation menu" aria-controls="app-sidebar"
                    aria-expanded={menuOpen} onClick={onOpenMenu} className="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-lg text-gray-600 hover:bg-gray-100 hover:text-gray-900 lg:hidden">
                    <LayoutIcon name="menu" className="h-6 w-6" />
                </button>
                <nav aria-label="Breadcrumb" className="flex min-w-0 items-center gap-2 text-sm">
                    <span className="hidden text-gray-500 sm:inline">{breadcrumbParent}</span>
                    <LayoutIcon name="chevron" className="hidden h-4 w-4 text-gray-500 sm:inline" />
                    <span aria-current="page" className="truncate font-semibold text-gray-900">{breadcrumbCurrent}</span>
                </nav>
            </div>
            {user && <div className="flex shrink-0 items-center gap-3 rounded-full border border-blue-100 bg-blue-50 py-1.5 pl-1.5 pr-4">
                <div aria-hidden="true" className="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-blue-100 text-xs font-semibold text-blue-700">{initials}</div>
                <div className="text-sm sm:hidden"><span className="sr-only">{user.name}</span></div>
                <div className="hidden max-w-40 text-sm sm:block xl:max-w-56">
                    <p className="truncate font-semibold leading-tight text-gray-900">{user.name}</p>
                    <p className="truncate text-xs capitalize text-gray-500">{user.position || user.role}</p>
                </div>
            </div>}
        </header>
    );
}
