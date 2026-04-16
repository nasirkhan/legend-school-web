<?php

namespace Modules\Task\Http\Requests;

class UpdateTaskRequest extends TaskRequest
{
    public function authorize(): bool
    {
        return $this->canEditTasks();
    }
}
