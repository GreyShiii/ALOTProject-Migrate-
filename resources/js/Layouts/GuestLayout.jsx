import React from 'react';
import { Head } from '@inertiajs/react';

export default function GuestLayout({ children, title, wide = false }) {
    return (
        <main className="flex min-h-screen items-center justify-center bg-gray-50 px-4 py-8">
            {title && <Head title={title} />}
            <div className={`w-full ${wide ? 'max-w-5xl' : 'max-w-md'}`}>{children}</div>
        </main>
    );
}
