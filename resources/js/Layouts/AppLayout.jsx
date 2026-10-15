import React, { useEffect, useRef, useState } from 'react';
import { Head, usePage } from '@inertiajs/react';
import Sidebar, { isNavigationActive } from '../Components/Sidebar';
import Topbar from '../Components/Topbar';

export default function AppLayout({ children, title, breadcrumbParent, breadcrumbCurrent }) {
    const { props: { auth, layout }, url } = usePage();
    const [menuOpen, setMenuOpen] = useState(false);
    const menuButton = useRef(null);
    const user = auth?.user;
    const navigation = layout?.navigation || [];
    const activeItem = navigation.find((item) => isNavigationActive(url, item.href));
    const current = breadcrumbCurrent || title || activeItem?.label || 'Dashboard';
    const role = user?.role ? user.role.charAt(0).toUpperCase() + user.role.slice(1) : '';

    useEffect(() => setMenuOpen(false), [url]);

    function closeMenu() {
        setMenuOpen(false);
        if (menuOpen) menuButton.current?.focus();
    }

    return (
        <div className="flex h-screen overflow-hidden bg-gray-50 text-gray-900">
            {title && <Head title={title} />}
            {menuOpen && <button type="button" aria-label="Close navigation menu" tabIndex={-1}
                className="fixed inset-0 z-30 bg-black/40 lg:hidden" onClick={closeMenu} />}
            <Sidebar open={menuOpen} onClose={closeMenu} role={role} navigation={navigation} url={url}
                logoUrl={layout?.logoUrl} logoutUrl={layout?.logoutUrl} />
            <div className="flex min-w-0 flex-1 flex-col overflow-hidden">
                <Topbar user={user} breadcrumbParent={breadcrumbParent || role} breadcrumbCurrent={current}
                    menuOpen={menuOpen} onOpenMenu={() => setMenuOpen(true)} menuButtonRef={menuButton} />
                <main className="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">{children}</main>
            </div>
        </div>
    );
}
