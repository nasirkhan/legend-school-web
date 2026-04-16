@extends('backend.layouts.app')

@section('title') {{ __($module_action) }} {{ __($module_title) }} @endsection

@section('breadcrumbs')
<x-backend.breadcrumbs>
    <x-backend.breadcrumb-item route='{{ route("backend.$module_name.index") }}' icon='{{ $module_icon }}'>
        {{ __($module_title) }}
    </x-backend.breadcrumb-item>
    <x-backend.breadcrumb-item type="active">{{ $task->name }}</x-backend.breadcrumb-item>
</x-backend.breadcrumbs>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <x-backend.section-header :data="$task" :module_name="$module_name" :module_title="$module_title" :module_icon="$module_icon" :module_action="$module_action" />

        <div class="row mt-4">
            <div class="col-md-6 mb-3">
                <strong>Task</strong>
                <div>{{ $task->name }}</div>
            </div>
            <div class="col-md-3 mb-3">
                <strong>Status</strong>
                <div>{{ ucfirst($task->status) }}</div>
            </div>
            <div class="col-md-3 mb-3">
                <strong>Due At</strong>
                <div>{{ $task->due_at?->format('Y-m-d H:i') ?? '-' }}</div>
            </div>
            <div class="col-md-6 mb-3">
                <strong>Created By</strong>
                <div>{{ $task->creator?->name ?? '-' }}</div>
            </div>
            <div class="col-md-6 mb-3">
                <strong>Primary Assignee</strong>
                <div>{{ $task->primaryAssignee?->name ?? '-' }}</div>
            </div>
            <div class="col-md-6 mb-3">
                <strong>Assigned Role</strong>
                <div>{{ $task->assignedRole?->name ? ucfirst($task->assignedRole->name) : '-' }}</div>
            </div>
            <div class="col-md-6 mb-3">
                <strong>Completed At</strong>
                <div>{{ $task->completed_at?->format('Y-m-d H:i') ?? '-' }}</div>
            </div>
            <div class="col-12 mb-3">
                <strong>Co-Assignees</strong>
                <div>
                    @forelse ($task->coAssignees as $assignee)
                        <span class="badge bg-info text-dark">{{ $assignee->name }}</span>
                    @empty
                        -
                    @endforelse
                </div>
            </div>
            <div class="col-12">
                <strong>Description</strong>
                <div class="mt-2">{{ $task->description ?: '-' }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
