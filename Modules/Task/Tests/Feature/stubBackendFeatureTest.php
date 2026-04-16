<?php

namespace Modules\Task\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Builders\UserBuilder;
use Tests\TestCase;

class TaskBackendTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = UserBuilder::make()->asAdmin()->create();
        $this->actingAs($this->admin);
    }

    public function test_admin_can_view_task_index(): void
    {
        $response = $this->get('/admin/tasks');

        $response->assertStatus(200);
        $response->assertSee('Tasks');
    }

    public function test_admin_can_create_task(): void
    {
        $data = [
            'name' => 'Test Task',
            'description' => 'Test description',
            'status' => 1,
        ];

        $response = $this->post('/admin/tasks', $data);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
        $this->assertDatabaseHas('tasks', ['name' => 'Test Task']);
    }

    public function test_admin_can_view_task(): void
    {
        $task = \Modules\Task\Models\Task::factory()->create();

        $response = $this->get("/admin/tasks/{$task->id}");

        $response->assertStatus(200);
        $response->assertSee($task->name);
    }

    public function test_admin_can_edit_task(): void
    {
        $task = \Modules\Task\Models\Task::factory()->create();

        $response = $this->get("/admin/tasks/{$task->id}/edit");

        $response->assertStatus(200);
        $response->assertSee($task->name);
    }

    public function test_admin_can_update_task(): void
    {
        $task = \Modules\Task\Models\Task::factory()->create();

        $data = [
            'name' => 'Updated Task',
            'description' => 'Updated description',
            'status' => 1,
        ];

        $response = $this->put("/admin/tasks/{$task->id}", $data);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
        $this->assertDatabaseHas('tasks', ['name' => 'Updated Task']);
    }

    public function test_admin_can_delete_task(): void
    {
        $task = \Modules\Task\Models\Task::factory()->create();

        $response = $this->delete("/admin/tasks/{$task->id}");

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
        $this->assertSoftDeleted('tasks', ['id' => $task->id]);
    }

    public function test_admin_can_restore_task(): void
    {
        $task = \Modules\Task\Models\Task::factory()->create();
        $task->delete();

        $response = $this->patch("/admin/tasks/{$task->id}/restore");

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'deleted_at' => null,
        ]);
    }
}
