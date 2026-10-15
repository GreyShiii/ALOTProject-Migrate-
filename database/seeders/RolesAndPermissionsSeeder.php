<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'users.manage',
            'employees.manage',
            'departments.manage',
            'dashboard.view.admin',
            'dashboard.view.manager',
            'dashboard.view.employee',
            'attendance.view.all',
            'attendance.manage.team',
            'attendance.manage.own',
            'leave.view.all',
            'leave.view.own',
            'leave.review.team',
            'leave.create.own',
            'overtime.view.all',
            'overtime.view.own',
            'overtime.review.team',
            'overtime.create.own',
            'team.view',
            'profile.update',
        ];

        foreach ($permissions as $name) {
            Permission::firstOrCreate([
                'name' => $name,
                'guard_name' => 'web',
            ]);
        }

        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $manager = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        $employee = Role::firstOrCreate(['name' => 'employee', 'guard_name' => 'web']);

        $admin->syncPermissions($permissions);
        $manager->syncPermissions([
            'dashboard.view.manager',
            'attendance.manage.team',
            'leave.review.team',
            'overtime.review.team',
            'team.view',
            'profile.update',
        ]);
        $employee->syncPermissions([
            'dashboard.view.employee',
            'attendance.manage.own',
            'leave.view.own',
            'leave.create.own',
            'overtime.view.own',
            'overtime.create.own',
            'profile.update',
        ]);
    }
}
