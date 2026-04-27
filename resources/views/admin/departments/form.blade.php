@extends('layouts.app')
@section('content')
    <h1 class="mb-6 text-2xl font-bold tracking-tight text-slate-900">{{ __('nav.departments') }}</h1>
    <form method="post" action="{{ $item->exists ? route('admin.departments.update', $item) : route('admin.departments.store') }}" class="admin-form-panel">
        @csrf @if($item->exists) @method('PUT') @endif
        <div>
            <label class="block text-sm font-medium text-slate-700">{{ __('fields.college') }}</label>
            <select name="college_id" class="admin-input" required>
                @foreach($colleges as $c)
                    <option value="{{ $c->id }}" @selected(old('college_id', $item->college_id)==$c->id)>{{ $c->name_en }}</option>
                @endforeach
            </select>
        </div>
        <div><label class="block text-sm font-medium text-slate-700">{{ __('fields.name_en') }}</label><input name="name_en" value="{{ old('name_en', $item->name_en) }}" required class="admin-input"></div>
        <div><label class="block text-sm font-medium text-slate-700">{{ __('fields.name_ku') }}</label><input name="name_ku" value="{{ old('name_ku', $item->name_ku) }}" class="admin-input"></div>
        <div><label class="block text-sm font-medium text-slate-700">{{ __('fields.name_ar') }}</label><input name="name_ar" value="{{ old('name_ar', $item->name_ar) }}" class="admin-input"></div>
        <button type="submit" class="admin-btn-primary">{{ __('admin.save') }}</button>
    </form>
@endsection
