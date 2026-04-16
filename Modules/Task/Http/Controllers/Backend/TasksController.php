<?php

namespace Modules\Task\Http\Controllers\Backend;

use App\Authorizable;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Task\Http\Requests\StoreTaskRequest;
use Modules\Task\Http\Requests\UpdateTaskRequest;
use Modules\Task\Models\Task;
use Yajra\DataTables\Facades\DataTables;

class TasksController extends Controller
{
    use Authorizable;

    public $module_title;

    public $module_name;

    public $module_path;

    public $module_icon;

    public $module_model;

    public function __construct()
    {
        $this->module_title = 'Tasks';
        $this->module_name = 'tasks';
        $this->module_path = 'task::backend';
        $this->module_icon = 'fa-regular fa-square-check';
        $this->module_model = Task::class;
    }

    protected function moduleContext(): array
    {
        return [
            'module_title' => $this->module_title,
            'module_name' => $this->module_name,
            'module_path' => $this->module_path,
            'module_icon' => $this->module_icon,
            'module_model' => $this->module_model,
            'module_name_singular' => Str::singular($this->module_name),
        ];
    }

    public function index(): View
    {
        return view('task::backend.tasks.index_datatable', array_merge($this->moduleContext(), [
            'module_action' => 'List',
        ]));
    }

    public function index_data(Request $request): JsonResponse
    {
        $query = Task::query()
            ->with(['creator', 'primaryAssignee', 'assignedRole', 'coAssignees'])
            ->visibleTo($request->user()->loadMissing('roles'))
            ->select('tasks.*');

        return DataTables::eloquent($query)
            ->addColumn('assignees', function (Task $task) {
                $names = collect([
                    $task->primaryAssignee?->name,
                    ...$task->coAssignees->pluck('name')->all(),
                    $task->assignedRole?->name ? 'Role: '.ucfirst($task->assignedRole->name) : null,
                ])->filter()->unique()->values();

                return e($names->implode(', ')) ?: '-';
            })
            ->addColumn('due_at_display', fn (Task $task) => $task->due_at?->format('Y-m-d H:i') ?? '-')
            ->addColumn('status_badge', function (Task $task) {
                $class = $task->status === 'completed' ? 'success' : 'warning text-dark';

                return '<span class="badge bg-'.$class.'">'.ucfirst($task->status).'</span>';
            })
            ->addColumn('action', function (Task $task) {
                $module_name = $this->module_name;
                $data = $task;

                return view('backend.includes.action_column', compact('module_name', 'data'));
            })
            ->rawColumns(['status_badge', 'action'])
            ->make(true);
    }

    public function create(): View
    {
        abort_unless(request()->user()?->can('add_tasks'), 403);

        return view('task::backend.tasks.create', array_merge($this->moduleContext(), [
            'module_action' => 'Create',
            'userOptions' => $this->userOptions(),
            'roleOptions' => $this->roleOptions(),
        ]));
    }

    public function store(StoreTaskRequest $request): RedirectResponse
    {
        $task = DB::transaction(function () use ($request) {
            $task = Task::create(collect($request->validated())->except('co_assignee_ids')->all());
            $task->coAssignees()->sync($request->input('co_assignee_ids', []));

            return $task;
        });

        flash("New 'Task' Added")->success()->important();

        return redirect()->route('backend.tasks.show', $task);
    }

    public function show(Request $request, Task $task): View
    {
        abort_unless(Task::query()->visibleTo($request->user()->loadMissing('roles'))->whereKey($task->id)->exists(), 403);

        return view('task::backend.tasks.show', array_merge($this->moduleContext(), [
            'module_action' => 'Show',
            'task' => $task->load(['creator', 'primaryAssignee', 'assignedRole', 'coAssignees', 'completer']),
        ]));
    }

    public function edit(Request $request, Task $task): View
    {
        abort_unless($request->user()?->can('edit_tasks'), 403);

        return view('task::backend.tasks.edit', array_merge($this->moduleContext(), [
            'module_action' => 'Edit',
            'task' => $task->load(['coAssignees']),
            'userOptions' => $this->userOptions(),
            'roleOptions' => $this->roleOptions(),
        ]));
    }

    public function update(UpdateTaskRequest $request, Task $task): RedirectResponse
    {
        DB::transaction(function () use ($request, $task) {
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
        });

        flash("Task Updated Successfully")->success()->important();

        return redirect()->route('backend.tasks.show', $task);
    }

    public function destroy(Request $request, Task $task): RedirectResponse
    {
        abort_unless($request->user()?->can('delete_tasks'), 403);

        $task->delete();

        flash('Task Deleted Successfully!')->success()->important();

        return redirect()->route('backend.tasks.index');
    }

    protected function userOptions(): array
    {
        return User::query()
            ->where('status', 1)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    protected function roleOptions(): array
    {
        return Role::query()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->map(fn ($name) => ucfirst($name))
            ->toArray();
    }
}
