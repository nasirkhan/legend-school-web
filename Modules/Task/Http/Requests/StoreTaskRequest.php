<?php

namespace Modules\Task\Http\Requests;

class StoreTaskRequest extends TaskRequest
{
    public function authorize(): bool
    {
        return $this->canCreateTasks();
    }
}
