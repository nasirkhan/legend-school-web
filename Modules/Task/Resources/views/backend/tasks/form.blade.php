<div class="row">
    <div class="col-12 col-md-6 mb-3">
        <div class="form-group">
            <?php
            $field_name = 'name';
            $field_lable = label_case($field_name);
            $field_placeholder = $field_lable;
            $required = 'required';
            ?>
            {{ html()->label($field_lable, $field_name)->class('form-label') }} {!! field_required($required) !!}
            {{ html()->text($field_name)->placeholder($field_placeholder)->class('form-control')->attributes(["$required"]) }}
        </div>
    </div>
    <div class="col-12 col-md-3 mb-3">
        <div class="form-group">
            <?php
            $field_name = 'due_at';
            $field_lable = 'Due Date & Time';
            $required = 'required';
            ?>
            {{ html()->label($field_lable, $field_name)->class('form-label') }} {!! field_required($required) !!}
            {{ html()->input('datetime-local', $field_name, old($field_name, isset($task) && $task->due_at ? $task->due_at->format('Y-m-d\TH:i') : null))->class('form-control')->attributes(["$required"]) }}
        </div>
    </div>
    <div class="col-12 col-md-3 mb-3">
        <div class="form-group">
            <?php
            $field_name = 'status';
            $field_lable = label_case($field_name);
            $required = 'required';
            $select_options = [
                'pending' => 'Pending',
                'completed' => 'Completed',
            ];
            ?>
            {{ html()->label($field_lable, $field_name)->class('form-label') }} {!! field_required($required) !!}
            {{ html()->select($field_name, $select_options)->class('form-select')->attributes(["$required"]) }}
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 col-md-6 mb-3">
        <div class="form-group">
            <?php
            $field_name = 'primary_assignee_id';
            $field_lable = 'Primary Assignee';
            $required = '';
            ?>
            {{ html()->label($field_lable, $field_name)->class('form-label') }} {!! field_required($required) !!}
            {{ html()->select($field_name, ['' => '-- Select an option --'] + $userOptions)->class('form-select select2') }}
        </div>
    </div>
    <div class="col-12 col-md-6 mb-3">
        <div class="form-group">
            <?php
            $field_name = 'co_assignee_ids[]';
            $field_lable = 'Co-Assignees';
            $required = '';
            $selected = old('co_assignee_ids', isset($task) ? $task->coAssignees->pluck('id')->all() : []);
            ?>
            {{ html()->label($field_lable, 'co_assignee_ids')->class('form-label') }} {!! field_required($required) !!}
            {{ html()->select($field_name, $userOptions, $selected)->class('form-select select2')->attributes(['multiple']) }}
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 col-md-6 mb-3">
        <div class="form-group">
            <?php
            $field_name = 'assigned_role_id';
            $field_lable = 'Assign To Role';
            $required = '';
            ?>
            {{ html()->label($field_lable, $field_name)->class('form-label') }} {!! field_required($required) !!}
            {{ html()->select($field_name, ['' => '-- Select an option --'] + $roleOptions)->class('form-select select2') }}
        </div>
    </div>
    @role('super_admin')
    <div class="col-12 col-md-6 mb-3">
        <div class="form-group">
            <?php
            $field_name = 'completed_at';
            $field_lable = 'Completed Date & Time';
            $required = '';
            ?>
            {{ html()->label($field_lable, $field_name)->class('form-label') }} {!! field_required($required) !!}
            {{ html()->input('datetime-local', $field_name, old($field_name, isset($task) && $task->completed_at ? $task->completed_at->format('Y-m-d\TH:i') : null))->class('form-control') }}
        </div>
    </div>
    @endrole
</div>

<div class="row">
    <div class="col-12 mb-3">
        <div class="form-group">
            <?php
            $field_name = 'description';
            $field_lable = label_case($field_name);
            $field_placeholder = $field_lable;
            $required = '';
            ?>
            {{ html()->label($field_lable, $field_name)->class('form-label') }} {!! field_required($required) !!}
            {{ html()->textarea($field_name)->placeholder($field_placeholder)->class('form-control')->attributes(['rows' => 5]) }}
        </div>
    </div>
</div>

<x-library.select2 />
