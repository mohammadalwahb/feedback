@extends('layouts.app')
@section('title', $item->exists ? __('admin.edit') : __('admin.add'))
@section('content')
    <h1 class="mb-6 text-2xl font-bold tracking-tight text-slate-900">{{ __('nav.colleges') }}</h1>
    <form method="post" action="{{ $item->exists ? route('admin.colleges.update', $item) : route('admin.colleges.store') }}" class="admin-form-panel">
        @csrf
        @if($item->exists) @method('PUT') @endif
        <div>
            <label class="block text-sm font-medium text-slate-700">{{ __('fields.name_en') }}</label>
            <input name="name_en" value="{{ old('name_en', $item->name_en) }}" required class="admin-input">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700">{{ __('fields.name_ku') }}</label>
            <input name="name_ku" value="{{ old('name_ku', $item->name_ku) }}" class="admin-input">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700">{{ __('fields.name_ar') }}</label>
            <input name="name_ar" value="{{ old('name_ar', $item->name_ar) }}" class="admin-input">
        </div>
        <button type="submit" class="admin-btn-primary">{{ __('admin.save') }}</button>
    </form>
@endsection
