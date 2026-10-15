import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/app.jsx',

                // Admin
                'resources/js/admin/attendance.js',
                'resources/js/admin/departments.js',
                'resources/js/admin/employees.js',
                'resources/js/admin/leave.js',
                'resources/js/admin/overtime.js',
                'resources/js/admin/profile.js',
                'resources/js/admin/users.js',

                // Employee
                'resources/js/employee/attendance.js',
                'resources/js/employee/dashboard.js',
                'resources/js/employee/employees.js',
                'resources/js/employee/leave.js',
                'resources/js/employee/overtime.js',
                'resources/js/employee/profile.js',

                // Manager
                'resources/js/manager/attendance.js',
                'resources/js/manager/leave.js',
                'resources/js/manager/overtime.js',
                'resources/js/manager/profile.js',
                'resources/js/manager/team.js',
            ],
            refresh: true,
        }),

        tailwindcss(),
    ],

    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
