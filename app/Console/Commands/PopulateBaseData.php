<?php

namespace App\Console\Commands;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Nasirkhan\ModuleManager\Modules\Menu\Models\Menu;
use Nasirkhan\ModuleManager\Modules\Menu\Models\MenuItem;

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

        $this->announceMethod(__FUNCTION__, 'seedRoles');
        $this->seedRoles();

        $this->announceMethod(__FUNCTION__, 'seedRolePermissions');
        $this->seedRolePermissions();

        $this->announceMethod(__FUNCTION__, 'seedBackendMenu');
        $this->seedBackendMenu();

        $this->announceMethod(__FUNCTION__, 'seedUsers');
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
        $taskPermissions = [
            'view_tasks',
            'add_tasks',
            'edit_tasks',
            'delete_tasks',
            'restore_tasks',
        ];

        $map = [
            'management' => [
                'view_backend',
                ...$taskPermissions,
            ],
            'teacher' => [
                'view_backend',
                ...$taskPermissions,
            ],
            'student' => [
                'view_backend',
                'view_tasks',
            ],
        ];

        Collection::make(Permission::defaultPermissions())
            ->each(fn ($name) => Permission::findOrCreate($name, 'web'));

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

    /**
     * Ensure the Tasks item exists in the backend sidebar menu.
     */
    protected function seedBackendMenu(): void
    {
        if (! class_exists(Menu::class) || ! class_exists(MenuItem::class)) {
            $this->warn('  Menu module not available, skipping backend Tasks menu.');

            return;
        }

        $menu = Menu::query()->firstOrCreate(
            ['slug' => 'admin-sidebar'],
            [
                'name' => 'Admin Sidebar',
                'description' => 'Administrative backend navigation',
                'location' => 'admin-sidebar',
                'theme' => 'dark',
                'css_classes' => 'sidebar-nav',
                'settings' => [
                    'max_depth' => 2,
                    'cache_duration' => 30,
                ],
                'permissions' => ['view_backend'],
                'is_public' => false,
                'is_active' => true,
                'is_visible' => true,
                'locale' => 'en',
                'status' => 1,
            ],
        );

        MenuItem::query()->updateOrCreate(
            [
                'menu_id' => $menu->id,
                'slug' => 'admin-tasks',
            ],
            [
                'parent_id' => null,
                'name' => 'Tasks',
                'description' => 'Manage tasks',
                'type' => 'link',
                'url' => null,
                'route_name' => 'backend.tasks.index',
                'route_parameters' => null,
                'opens_new_tab' => false,
                'sort_order' => 2,
                'depth' => 0,
                'path' => null,
                'icon' => 'fa-regular fa-square-check',
                'badge_text' => null,
                'badge_color' => null,
                'css_classes' => 'nav-link',
                'html_attributes' => null,
                'permissions' => ['view_tasks'],
                'is_visible' => true,
                'is_active' => true,
                'locale' => 'en',
                'meta_title' => 'Tasks',
                'meta_description' => null,
                'meta_keywords' => null,
                'custom_data' => null,
                'note' => 'Task module backend entry',
                'status' => 1,
            ],
        );

        Menu::clearMenuCache('admin-sidebar');

        $this->line('  <fg=cyan>Synced</fg=cyan>  backend menu → Tasks');
    }

    protected function announceMethod(string $caller, string $method): void
    {
        $this->line("Running {$caller}() -> {$method}()");
    }
}
