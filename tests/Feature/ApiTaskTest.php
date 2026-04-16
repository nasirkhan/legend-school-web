<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Task\Models\Task;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ApiTaskTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'super admin', 'guard_name' => 'web']);
        $this->admin = User::factory()->create(['status' => 1]);
        $this->admin->assignRole('super admin');
    }

    public function test_unauthenticated_request_to_tasks_api_is_rejected(): void
    {
        $this->getJson('/api/v1/tasks')->assertUnauthorized();
    }

    public function test_authenticated_user_can_list_tasks(): void
    {
        $token = $this->admin->createToken('Test Device')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/tasks')
            ->assertOk()
            ->assertJsonStructure(['data', 'meta']);
    }

    public function test_authenticated_user_can_create_a_task(): void
    {
        $token = $this->admin->createToken('Test Device')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/v1/tasks', [
                'name' => 'Test Task',
                'status' => 'pending',
                'due_at' => now()->addDays(3)->toDateTimeString(),
                'primary_assignee_id' => $this->admin->id,
            ])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Test Task');
    }

    public function test_authenticated_user_can_view_a_task(): void
    {
        $token = $this->admin->createToken('Test Device')->plainTextToken;
        $task = Task::factory()->create(['created_by' => $this->admin->id]);

        $this->withToken($token)
            ->getJson("/api/v1/tasks/{$task->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $task->id);
    }

    public function test_authenticated_user_can_complete_a_task(): void
    {
        $token = $this->admin->createToken('Test Device')->plainTextToken;
        $task = Task::factory()->create(['created_by' => $this->admin->id, 'status' => 'pending']);

        $this->withToken($token)
            ->patchJson("/api/v1/tasks/{$task->id}/complete")
            ->assertOk()
            ->assertJsonPath('data.status', 'completed');
    }

    public function test_authenticated_user_can_reopen_a_completed_task(): void
    {
        $token = $this->admin->createToken('Test Device')->plainTextToken;
        $task = Task::factory()->create(['created_by' => $this->admin->id, 'status' => 'completed']);

        $this->withToken($token)
            ->patchJson("/api/v1/tasks/{$task->id}/reopen")
            ->assertOk()
            ->assertJsonPath('data.status', 'pending');
    }
}
