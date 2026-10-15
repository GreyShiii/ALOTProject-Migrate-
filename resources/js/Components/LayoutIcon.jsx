import React from 'react';

const paths = {
    dashboard: 'M4 4h6v6H4zM14 4h6v6h-6zM4 14h6v6H4zM14 14h6v6h-6z',
    attendance: 'M12 7v5l3 2M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
    leave: 'M3 9h18M8 3v4M16 3v4M5 5h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z',
    overtime: 'M12 10v3M9 2h6M19 13a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z',
    people: 'M16 20v-1a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v1M13 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0ZM22 20v-1a4 4 0 0 0-3-3.87M16 3.13A4 4 0 0 1 16 11',
    users: 'M12 3 5 6v5c0 4.4 3 8.4 7 9.5 4-1.1 7-5.1 7-9.5V6l-7-3Z',
    departments: 'M4 21V6a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v15M16 21h4V10a1 1 0 0 0-1-1h-3M8 8h.01M12 8h.01M8 12h.01M12 12h.01M8 16h.01M12 16h.01',
    profile: 'M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0ZM4 20c0-3.3 3.6-6 8-6s8 2.7 8 6',
    logout: 'M15 12H3m0 0 4-4m-4 4 4 4M11 4h6a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-6',
    menu: 'M4 6h16M4 12h16M4 18h16',
    close: 'M6 18 18 6M6 6l12 12',
    chevron: 'm9 6 6 6-6 6',
};

export default function LayoutIcon({ name, className = 'h-5 w-5 shrink-0' }) {
    return (
        <svg aria-hidden="true" className={className} fill="none" stroke="currentColor" strokeWidth="1.8" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" d={paths[name] || paths.dashboard} />
        </svg>
    );
}
