<?php

namespace Modules\Task\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Modules\Task\Models\Task;

class TaskModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_task_has_correct_table_name(): void
    {
        $task = new Task();

        $this->assertEquals('tasks', $task->getTable());
    }

    public function test_task_has_correct_casts(): void
    {
        $task = new Task();
        $casts = $task->getCasts();

        $this->assertArrayHasKey('created_at', $casts);
        $this->assertArrayHasKey('updated_at', $casts);
        $this->assertArrayHasKey('deleted_at', $casts);
    }

    public function test_task_uses_soft_deletes(): void
    {
        $task = Task::factory()->create();
        $task->delete();

        $this->assertSoftDeleted('tasks', ['id' => $task->id]);
    }

    public function test_task_factory_creates_valid_data(): void
    {
        $task = Task::factory()->create();

        $this->assertNotEmpty($task->name);
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'name' => $task->name,
        ]);
    }
}
