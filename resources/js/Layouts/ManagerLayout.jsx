import React from 'react';
import AppLayout from './AppLayout';

export default function ManagerLayout({ children, ...props }) {
    return <AppLayout {...props}>{children}</AppLayout>;
}
