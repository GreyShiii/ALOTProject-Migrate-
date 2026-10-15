<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $roleNames = DB::table('users')->distinct()->pluck('role')->filter()->unique();

        foreach ($roleNames as $roleName) {
            DB::table('roles')->insertOrIgnore([
                'name' => $roleName,
                'guard_name' => 'web',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $roleIds = DB::table('roles')
            ->whereIn('name', $roleNames)
            ->where('guard_name', 'web')
            ->pluck('id', 'name');

        DB::table('users')->select(['id', 'role'])->orderBy('id')->chunk(500, function ($users) use ($roleIds): void {
            $assignments = $users
                ->filter(fn ($user) => isset($roleIds[$user->role]))
                ->map(fn ($user) => [
                    'role_id' => $roleIds[$user->role],
                    'model_type' => App\Models\User::class,
                    'model_id' => $user->id,
                ])
                ->values()
                ->all();

            if ($assignments !== []) {
                DB::table('model_has_roles')->insertOrIgnore($assignments);
            }
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('role')->default('employee');
        });

        DB::table('model_has_roles')
            ->where('model_type', App\Models\User::class)
            ->orderBy('model_id')
            ->get(['model_id', 'role_id'])
            ->each(function ($assignment): void {
                $roleName = DB::table('roles')->where('id', $assignment->role_id)->value('name');

                if ($roleName !== null) {
                    DB::table('users')->where('id', $assignment->model_id)->update(['role' => $roleName]);
                }
            });
    }
};
