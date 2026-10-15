<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SharedLayoutTest extends TestCase
{
    use RefreshDatabase;

    public static function roles(): array
    {
        return [
            'admin' => ['admin', 8, 'admin.dashboard'],
            'manager' => ['manager', 6, 'manager.dashboard'],
            'employee' => ['employee', 5, 'employee.dashboard'],
        ];
    }

    #[DataProvider('roles')]
    public function test_shared_layout_has_the_correct_role_menu(string $role, int $count, string $dashboard): void
    {
        Role::create(['name' => $role, 'guard_name' => 'web']);
        $user = User::create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'password' => 'test-password',
            'status' => 'active',
        ]);
        $user->assignRole($role);

        
        Route::middleware(['web', 'auth'])->get('/layout-preview-test', fn () => Inertia::render('Manager/Team/Index', [
            'employees' => [],
        ]));

        $this->actingAs($user)->withoutVite()->get('/layout-preview-test')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('auth.user.first_name', 'Test')
                ->where('auth.user.last_name', 'User')
                ->where('auth.user.position', null)
                ->where('auth.user.role', $role)
                ->where('layout.logoUrl', asset('images/alot-logo.png'))
                ->where('layout.logoutUrl', route('logout'))
                ->has('layout.navigation', $count)
                ->where('layout.navigation.0.href', route($dashboard))
                ->where('layout.navigation.0.inertia', $role === 'admin')
                ->where('layout.navigation', function ($items) use ($role) {
                    return collect($items)->every(fn ($item) =>
                        str_starts_with($item['href'], url('/'.$role.'/'))
                        && $item['inertia'] === in_array($item['href'], [route('admin.dashboard'), route('admin.users.index'), route('admin.departments.index'), route('admin.employees.index'), route('admin.attendance.index'), route('admin.leave.index'), route('admin.overtime.index'), route('manager.team.index')], true)
                    );
                }));
    }
}
