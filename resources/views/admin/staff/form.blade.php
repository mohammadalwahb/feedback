@extends('layouts.app')
@section('content')
    <h1 class="mb-6 text-2xl font-bold tracking-tight text-slate-900">{{ __('nav.staff') }}</h1>
    <form method="post" action="{{ $item->exists ? route('admin.staff.update', $item) : route('admin.staff.store') }}" class="admin-form-panel">
        @csrf @if($item->exists) @method('PUT') @endif
        <div><label class="block text-sm font-medium text-slate-700">{{ __('fields.staff_id') }}</label><input name="staff_employee_id" value="{{ old('staff_employee_id', $item->staff_employee_id) }}" required class="admin-input"></div>
        <div><label class="block text-sm font-medium text-slate-700">{{ __('fields.instructor') }}</label><input name="instructor_name" value="{{ old('instructor_name', $item->instructor_name) }}" required class="admin-input"></div>
        <div><label class="block text-sm font-medium text-slate-700">{{ __('fields.subject') }}</label><input name="subject_name" value="{{ old('subject_name', $item->subject_name) }}" required class="admin-input"></div>
        <div><label class="block text-sm font-medium text-slate-700">{{ __('fields.college') }}</label>
            <select name="college_id" class="admin-input" required>@foreach($colleges as $c)<option value="{{ $c->id }}" @selected(old('college_id', $item->college_id)==$c->id)>{{ $c->name_en }}</option>@endforeach</select>
        </div>
        <div><label class="block text-sm font-medium text-slate-700">{{ __('fields.department') }}</label>
            <select name="department_id" class="admin-input" required>@foreach($departments as $d)<option value="{{ $d->id }}" @selected(old('department_id', $item->department_id)==$d->id)>{{ $d->name_en }}</option>@endforeach</select>
        </div>
        <div><label class="block text-sm font-medium text-slate-700">{{ __('fields.semester') }}</label>
            <select name="semester_id" class="admin-input" required>@foreach($semesters as $s)<option value="{{ $s->id }}" @selected(old('semester_id', $item->semester_id)==$s->id)>{{ $s->name_en }}</option>@endforeach</select>
        </div>
        <button type="submit" class="admin-btn-primary">{{ __('admin.save') }}</button>
    </form>
@endsection
