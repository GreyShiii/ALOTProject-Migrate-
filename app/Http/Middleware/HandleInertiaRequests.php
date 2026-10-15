<?php

namespace App\Http\Middleware;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /** The root template loaded on the first page visit. */
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /** @return array<string, mixed> */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => trim($user->first_name.' '.$user->last_name),
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'position' => $user->employee?->position,
                    'role' => $user->primaryRoleName(),
                    'permissions' => $user->getAllPermissions()->pluck('name')->values(),
                ] : null,
            ],
            'layout' => $user ? [
                'logoUrl' => asset('images/alot-logo.png'),
                'logoutUrl' => route('logout'),
                'navigation' => $this->navigation($user),
            ] : null,
        ];
    }

    /** @return array<int, array<string, mixed>> */
    private function navigation(User $user): array
    {
        $role = $user->primaryRoleName();

        $items = match ($role) {
            'admin' => [
                ['Dashboard', 'admin.dashboard', 'dashboard'],
                ['Employees', 'admin.employees.index', 'people'],
                ['Users', 'admin.users.index', 'users'],
                ['Departments', 'admin.departments.index', 'departments'],
                ['Attendance', 'admin.attendance.index', 'attendance'],
                ['Leave Requests', 'admin.leave.index', 'leave'],
                ['Overtime Requests', 'admin.overtime.index', 'overtime'],
                ['Profile', 'admin.profile.index', 'profile'],
            ],
            'manager' => [
                ['Dashboard', 'manager.dashboard', 'dashboard'],
                ['Attendance', 'manager.attendance.index', 'attendance'],
                ['Leave Requests', 'manager.leave.index', 'leave'],
                ['Overtime Requests', 'manager.overtime.index', 'overtime'],
                ['My Team', 'manager.team.index', 'people'],
                ['Profile', 'manager.profile.index', 'profile'],
            ],
            'employee' => [
                ['Dashboard', 'employee.dashboard', 'dashboard'],
                ['Attendance', 'employee.attendance.index', 'attendance'],
                ['My Leave', 'employee.leave.index', 'leave'],
                ['My Overtime', 'employee.overtime.index', 'overtime'],
                ['Profile', 'employee.profile.index', 'profile'],
            ],
            default => [],
        };

        return array_map(fn (array $item) => [
            'label' => $item[0],
            'href' => route($item[1]),
            'icon' => $item[2],
            // Use Inertia only for pages that have already been migrated.
            'inertia' => in_array($item[1], ['admin.dashboard', 'admin.users.index', 'admin.departments.index', 'admin.employees.index', 'admin.attendance.index', 'admin.leave.index', 'admin.overtime.index', 'admin.profile.index', 'manager.team.index'], true),
        ], $items);
    }
}
