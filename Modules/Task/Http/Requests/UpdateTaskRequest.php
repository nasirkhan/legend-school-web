<?php

namespace Modules\Task\Http\Requests;

class UpdateTaskRequest extends TaskRequest
{
    public function authorize(): bool
    {
        $task = $this->route('task');

        if (! $task) {
            return false;
        }

        return $task->canBeEditedBy($this->user());
    }
}
