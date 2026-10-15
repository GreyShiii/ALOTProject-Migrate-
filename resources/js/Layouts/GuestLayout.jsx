import React from 'react';
import { Head } from '@inertiajs/react';

export default function GuestLayout({ children, title }) {
    return (
        <main className="flex min-h-screen items-center justify-center bg-gray-50 px-4 py-8">
            {title && <Head title={title} />}
            <div className="w-full max-w-md">{children}</div>
        </main>
    );
}
