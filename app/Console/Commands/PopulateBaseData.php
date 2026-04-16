<?php

namespace App\Console\Commands;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

#[Signature('app:populate-base-data')]
#[Description('Populate base/essential data (roles, etc.) — safe to re-run')]
class PopulateBaseData extends Command
{
    /**
     * Execute the console command.
     *
     * php artisan app:populate-base-data
     */
    public function handle(): int
    {
        $this->info('Populating base data…');

        $this->seedRoles();
        $this->seedRolePermissions();
        $this->seedUsers();

        $this->info('Done.');

        return self::SUCCESS;
    }

    /**
     * Seed the application roles.
     */
    protected function seedRoles(): void
    {
        $roles = ['student', 'teacher', 'management'];

        foreach ($roles as $name) {
            $role = Role::updateOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
            );

            if ($role->wasRecentlyCreated) {
                $this->line("  <fg=green>Created</fg=green> role: {$name}");
            } else {
                $this->line("  <fg=cyan>Updated</fg=cyan>  role: {$name}");
            }
        }
    }

    /**
     * Sync permissions to each role.
     *
     * @var array<string, string[]>
     */
    protected function seedRolePermissions(): void
    {
        $map = [
            'management' => [
                'view_backend',
            ],
            'teacher' => [
                'view_backend',
            ],
            'student' => [
                'view_backend',
            ],
        ];

        foreach ($map as $roleName => $permissions) {
            $role = Role::where('name', $roleName)->where('guard_name', 'web')->first();

            if (! $role) {
                $this->warn("  Role not found, skipping permissions: {$roleName}");

                continue;
            }

            $permissionIds = Permission::whereIn('name', $permissions)->pluck('id');
            $role->syncPermissions($permissionIds);

            $this->line("  <fg=cyan>Synced</fg=cyan>  permissions → {$roleName} (".count($permissionIds).')');
        }
    }

    /**
     * Seed one user per role.
     */
    protected function seedUsers(): void
    {
        $users = [
            ['name' => 'Management One', 'email' => 'm1@management.com', 'role' => 'management'],
            ['name' => 'Teacher One',    'email' => 't1@teacher.com',    'role' => 'teacher'],
            ['name' => 'Student One',    'email' => 's1@student.com',    'role' => 'student'],
        ];

        foreach ($users as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'email_verified_at' => now(),
                ],
            );

            if ($user->wasRecentlyCreated) {
                $user->password = Hash::make('password');
                $user->save();
                $this->line("  <fg=green>Created</fg=green> user: {$data['email']}");
            } else {
                $this->line("  <fg=cyan>Updated</fg=cyan>  user: {$data['email']}");
            }

            if (! $user->hasRole($data['role'])) {
                $user->assignRole($data['role']);
                $this->line("           → assigned role: {$data['role']}");
            }
        }
    }
}
