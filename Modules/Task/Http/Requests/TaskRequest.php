<?php

namespace Modules\Task\Http\Requests;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class TaskRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'co_assignee_ids' => array_values(array_filter((array) $this->input('co_assignee_ids', []))),
        ]);
    }

    public function rules(): array
    {
        $activeUserRule = Rule::exists(User::class, 'id')
            ->where(fn ($query) => $query->whereNull('deleted_at')->where('status', 1));

        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'due_at' => ['required', 'date'],
            'status' => ['nullable', Rule::in(['pending', 'completed'])],
            'primary_assignee_id' => ['nullable', 'integer', $activeUserRule],
            'assigned_role_id' => ['nullable', 'integer', Rule::exists(Role::class, 'id')],
            'co_assignee_ids' => ['nullable', 'array'],
            'co_assignee_ids.*' => ['integer', 'distinct', $activeUserRule],
            'completed_at' => ['nullable', 'date'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $primaryAssigneeId = $this->integer('primary_assignee_id');
            $coAssigneeIds = collect($this->input('co_assignee_ids', []))
                ->map(fn ($id) => (int) $id)
                ->filter();

            if (! $primaryAssigneeId && ! $this->filled('assigned_role_id') && $coAssigneeIds->isEmpty()) {
                $validator->errors()->add('primary_assignee_id', 'Assign the task to a user, co-assignee, or role.');
            }

            if ($primaryAssigneeId && $coAssigneeIds->contains($primaryAssigneeId)) {
                $validator->errors()->add('co_assignee_ids', 'The primary assignee cannot also be a co-assignee.');
            }
        });
    }

    protected function canCreateTasks(): bool
    {
        $user = $this->user();

        return $user?->can('add_tasks') ?? false;
    }

    protected function canEditTasks(): bool
    {
        $user = $this->user();

        return $user?->can('edit_tasks') ?? false;
    }
}
