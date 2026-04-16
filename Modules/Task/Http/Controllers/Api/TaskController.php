<?php

namespace Modules\Task\Http\Controllers\Api;

use App\ApiAuthorizable;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Modules\Task\Http\Requests\StoreTaskRequest;
use Modules\Task\Http\Requests\UpdateTaskRequest;
use Modules\Task\Http\Resources\TaskResource;
use Modules\Task\Models\Task;

class TaskController extends Controller
{
    use ApiAuthorizable;

    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = min(max($request->integer('per_page', 50), 1), 100);

        $tasks = Task::query()
            ->with(['creator', 'primaryAssignee', 'assignedRole', 'coAssignees', 'completer'])
            ->visibleTo($request->user()->loadMissing('roles'))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()))
            ->orderByRaw("case when status = 'pending' then 0 else 1 end")
            ->orderBy('due_at')
            ->paginate($perPage);

        return TaskResource::collection($tasks);
    }

    public function options(Request $request): JsonResponse
    {
        abort_unless($request->user()?->can('add_tasks') || $request->user()?->can('edit_tasks'), 403);

        return response()->json([
            'data' => [
                'users' => User::query()
                    ->where('status', 1)
                    ->orderBy('name')
                    ->get(['id', 'name', 'email'])
                    ->map(fn ($user) => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                    ])
                    ->values(),
                'roles' => Role::query()
                    ->orderBy('name')
                    ->get(['id', 'name'])
                    ->map(fn ($role) => [
                        'id' => $role->id,
                        'name' => ucfirst($role->name),
                    ])
                    ->values(),
            ],
        ]);
    }

    public function store(StoreTaskRequest $request): JsonResponse
    {
        $task = new Task(collect($request->validated())->except('co_assignee_ids')->all());
        $task->status = $request->input('status', 'pending');
        $task->save();
        $task->coAssignees()->sync($request->input('co_assignee_ids', []));

        return TaskResource::make($task->load(['creator', 'primaryAssignee', 'assignedRole', 'coAssignees', 'completer']))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, Task $task): TaskResource
    {
        abort_unless(Task::query()->visibleTo($request->user()->loadMissing('roles'))->whereKey($task->id)->exists(), 403);

        return TaskResource::make($task->load(['creator', 'primaryAssignee', 'assignedRole', 'coAssignees', 'completer']));
    }

    public function update(UpdateTaskRequest $request, Task $task): TaskResource
    {
        abort_unless(Task::query()->visibleTo($request->user()->loadMissing('roles'))->whereKey($task->id)->exists(), 403);

        $task->fill(collect($request->validated())->except('co_assignee_ids')->all());

        if ($request->input('status') === 'completed' || $request->filled('completed_at')) {
            $task->completed_by = $request->user()->id;
            $task->completed_at = $request->date('completed_at') ?? now();
            $task->status = 'completed';
        } elseif ($request->input('status') === 'pending') {
            $task->completed_by = null;
            $task->completed_at = null;
            $task->status = 'pending';
        }

        $task->save();
        $task->coAssignees()->sync($request->input('co_assignee_ids', []));

        return TaskResource::make($task->load(['creator', 'primaryAssignee', 'assignedRole', 'coAssignees', 'completer']));
    }

    public function destroy(Request $request, Task $task): JsonResponse
    {
        abort_unless(Task::query()->visibleTo($request->user()->loadMissing('roles'))->whereKey($task->id)->exists(), 403);

        $task->delete();

        return response()->json(['message' => 'Task deleted successfully.']);
    }

    public function complete(Request $request, Task $task): TaskResource
    {
        abort_unless($this->canMarkComplete($request->user()->loadMissing('roles'), $task->loadMissing('coAssignees')), 403);

        $task->markCompleted($request->user());

        return TaskResource::make($task->fresh()->load(['creator', 'primaryAssignee', 'assignedRole', 'coAssignees', 'completer']));
    }

    public function reopen(Request $request, Task $task): TaskResource
    {
        abort_unless($this->canMarkComplete($request->user()->loadMissing('roles'), $task->loadMissing('coAssignees')), 403);

        $task->reopen();

        return TaskResource::make($task->fresh()->load(['creator', 'primaryAssignee', 'assignedRole', 'coAssignees', 'completer']));
    }

    protected function canMarkComplete($user, Task $task): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->can('edit_tasks')) {
            return true;
        }

        return (int) $task->created_by === (int) $user->id
            || (int) $task->primary_assignee_id === (int) $user->id
            || $task->coAssignees->contains('id', $user->id)
            || $user->roles->pluck('id')->contains($task->assigned_role_id);
    }
}
