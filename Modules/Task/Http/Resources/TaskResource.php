<?php

namespace Modules\Task\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $request->user();
        $coAssignees = $this->relationLoaded('coAssignees') ? $this->coAssignees : collect();
        $userRoleIds = $user ? $user->roles->pluck('id') : collect();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
            'due_at' => $this->due_at?->toISOString(),
            'completed_at' => $this->completed_at?->toISOString(),
            'is_completed' => $this->status === 'completed',
            'creator' => $this->whenLoaded('creator', fn () => [
                'id' => $this->creator?->id,
                'name' => $this->creator?->name,
                'email' => $this->creator?->email,
            ]),
            'primary_assignee' => $this->whenLoaded('primaryAssignee', fn () => [
                'id' => $this->primaryAssignee?->id,
                'name' => $this->primaryAssignee?->name,
                'email' => $this->primaryAssignee?->email,
            ]),
            'assigned_role' => $this->whenLoaded('assignedRole', fn () => [
                'id' => $this->assignedRole?->id,
                'name' => $this->assignedRole?->name,
            ]),
            'co_assignees' => $this->whenLoaded(
                'coAssignees',
                fn () => $coAssignees->map(fn ($assignee) => [
                    'id' => $assignee->id,
                    'name' => $assignee->name,
                    'email' => $assignee->email,
                ])->values()
            ),
            'completed_by' => $this->whenLoaded('completer', fn () => [
                'id' => $this->completer?->id,
                'name' => $this->completer?->name,
                'email' => $this->completer?->email,
            ]),
            'can' => [
                'edit' => $user?->can('edit_tasks') ?? false,
                'complete' => $user && (
                    $user->can('edit_tasks')
                    ||
                    (int) $this->primary_assignee_id === (int) $user->id
                    || $coAssignees->contains('id', $user->id)
                    || (int) $this->created_by === (int) $user->id
                    || $userRoleIds->contains($this->assigned_role_id)
                ),
            ],
        ];
    }
}
