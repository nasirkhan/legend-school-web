@extends('backend.layouts.app')

@section('title') {{ __($module_action) }} {{ __($module_title) }} @endsection

@section('breadcrumbs')
<x-backend.breadcrumbs>
    <x-backend.breadcrumb-item route='{{route("backend.$module_name.index")}}' icon='{{ $module_icon }}'>
        {{ __($module_title) }}
    </x-backend.breadcrumb-item>
    <x-backend.breadcrumb-item type="active">{{ __($module_action) }}</x-backend.breadcrumb-item>
</x-backend.breadcrumbs>
@endsection

@section('content')
<x-backend.layouts.create :module_name="$module_name" :module_path="$module_path" :module_title="$module_title" :module_icon="$module_icon" :module_action="$module_action">
    <x-cube::backend-section-header
        :module_name="$module_name"
        :module_title="$module_title"
        :module_icon="$module_icon"
        :module_action="$module_action"
    />

    <div class="row mt-4">
        <div class="col">
            {{ html()->form("POST", route("backend.$module_name.store"))->acceptsFiles()->open() }}

            @include("$module_path.$module_name.form", ['userOptions' => $userOptions, 'roleOptions' => $roleOptions])

            <div class="row">
                <div class="col-6">
                    <x-cube::backend-button-create>Create</x-cube::backend-button-create>
                </div>
            </div>

            {{ html()->form()->close() }}

            <div class="row">
                <div class="col-12 mt-3">
                    <div class="float-end">
                        <x-cube::backend-button-cancel />
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-backend.layouts.create>
@endsection
