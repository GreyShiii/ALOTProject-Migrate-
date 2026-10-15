<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Departments
        $engineering = Department::firstOrCreate(['name' => 'Engineering']);
        $hr = Department::firstOrCreate(['name' => 'Human Resources']);

        // Admin
        $adminUser = User::updateOrCreate(['email' => 'admin@company.com'], [
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@company.com',
            'password' => 'password',
            'status' => 'active',
        ]);
        $adminUser->syncRoles('admin');

        Employee::updateOrCreate(['user_id' => $adminUser->id], [
            'department_id' => $hr->id,
            'position' => 'System Administrator',
            'hire_date' => '2020-01-05',
        ]);

        // Manager
        $managerUser = User::updateOrCreate(['email' => 'daniel.reyes@company.com'], [
            'first_name' => 'Daniel',
            'last_name' => 'Reyes',
            'email' => 'daniel.reyes@company.com',
            'password' => 'password',
            'status' => 'active',
        ]);
        $managerUser->syncRoles('manager');

        $managerEmployee = Employee::updateOrCreate(['user_id' => $managerUser->id], [
            'user_id' => $managerUser->id,
            'department_id' => $engineering->id,
            'position' => 'Engineering Manager',
            'hire_date' => '2021-03-02',
        ]);

        // Employee (reports to Daniel)
        $employeeUser = User::updateOrCreate(['email' => 'bea.oca@company.com'], [
            'first_name' => 'Bea Marie',
            'last_name' => 'Oca',
            'email' => 'bea.oca@company.com',
            'password' => 'password',
            'status' => 'active',
        ]);
        $employeeUser->syncRoles('employee');

        Employee::updateOrCreate(['user_id' => $employeeUser->id], [
            'user_id' => $employeeUser->id,
            'department_id' => $engineering->id,
            'manager_id' => $managerEmployee->id,
            'position' => 'Senior Software Engineer',
            'hire_date' => '2024-02-14',
        ]);

        // Inactive employee (to test the "deactivated" login block)
        $inactiveUser = User::updateOrCreate(['email' => 'inactive@company.com'], [
            'first_name' => 'Inactive',
            'last_name' => 'Tester',
            'email' => 'inactive@company.com',
            'password' => 'password',
            'status' => 'inactive',
        ]);
        $inactiveUser->syncRoles('employee');

        Employee::updateOrCreate(['user_id' => $inactiveUser->id], [
            'user_id' => $inactiveUser->id,
            'department_id' => $engineering->id,
            'manager_id' => $managerEmployee->id,
            'position' => 'Former Employee',
            'hire_date' => '2023-01-10',
        ]);
    }
}
