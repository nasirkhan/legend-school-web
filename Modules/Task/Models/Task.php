<?php

namespace Modules\Task\Models;

use App\Models\Role;
use App\Models\BaseModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends BaseModel
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'tasks';

    protected function casts(): array
    {
        return array_merge(parent::casts(), [
            'due_at' => 'datetime',
            'completed_at' => 'datetime',
        ]);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function primaryAssignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'primary_assignee_id');
    }

    public function completer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function assignedRole(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'assigned_role_id');
    }

    public function coAssignees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_co_assignees')
            ->withTimestamps();
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->can('edit_tasks')) {
            return $query;
        }

        $roleIds = $user->roles->pluck('id')->filter()->values();

        return $query->where(function (Builder $builder) use ($user, $roleIds) {
            $builder->where('created_by', $user->id)
                ->orWhere('primary_assignee_id', $user->id)
                ->orWhereHas('coAssignees', fn (Builder $coAssignees) => $coAssignees->where('users.id', $user->id));

            if ($roleIds->isNotEmpty()) {
                $builder->orWhereIn('assigned_role_id', $roleIds);
            }
        });
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    public function markCompleted(?User $user = null): void
    {
        $this->forceFill([
            'status' => 'completed',
            'completed_at' => now(),
            'completed_by' => $user?->id,
        ])->save();
    }

    public function reopen(): void
    {
        $this->forceFill([
            'status' => 'pending',
            'completed_at' => null,
            'completed_by' => null,
        ])->save();
    }

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return \Modules\Task\database\factories\TaskFactory::new();
    }
}
