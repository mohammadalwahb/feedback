@extends('layouts.app')
@section('content')
    <h1 class="mb-6 text-2xl font-bold tracking-tight text-slate-900">{{ __('nav.students') }}</h1>
    <form method="post" action="{{ $item->exists ? route('admin.students.update', $item) : route('admin.students.store') }}" class="admin-form-panel">
        @csrf @if($item->exists) @method('PUT') @endif
        <div><label class="block text-sm font-medium text-slate-700">{{ __('fields.email') }} (@stud.uoz.edu.krd)</label><input type="email" name="email" value="{{ old('email', $item->email) }}" required class="admin-input"></div>
        <div><label class="block text-sm font-medium text-slate-700">{{ __('fields.english_name') }}</label><input name="english_name" value="{{ old('english_name', $item->english_name) }}" required class="admin-input"></div>
        <div><label class="block text-sm font-medium text-slate-700">{{ __('fields.name_ku') }}</label><input name="kurdish_name" value="{{ old('kurdish_name', $item->kurdish_name) }}" class="admin-input"></div>
        <div><label class="block text-sm font-medium text-slate-700">{{ __('fields.name_ar') }}</label><input name="arabic_name" value="{{ old('arabic_name', $item->arabic_name) }}" class="admin-input"></div>
        <div><label class="block text-sm font-medium text-slate-700">{{ __('fields.college') }}</label>
            <select id="student-form-college" name="college_id" class="admin-input" required>@foreach($colleges as $c)<option value="{{ $c->id }}" @selected(old('college_id', $item->college_id)==$c->id)>{{ $c->name_en }}</option>@endforeach</select>
        </div>
        <div><label class="block text-sm font-medium text-slate-700">{{ __('fields.department') }}</label>
            <select id="student-form-department" name="department_id" class="admin-input" required>@foreach($departments as $d)<option value="{{ $d->id }}" @selected(old('department_id', $item->department_id)==$d->id)>{{ $d->name_en }}</option>@endforeach</select>
        </div>
        <div><label class="block text-sm font-medium text-slate-700">{{ __('fields.semester') }}</label>
            <select name="semester_id" class="admin-input" required>@foreach($semesters as $s)<option value="{{ $s->id }}" @selected(old('semester_id', $item->semester_id)==$s->id)>{{ $s->name_en }}</option>@endforeach</select>
        </div>
        <button type="submit" class="admin-btn-primary">{{ __('admin.save') }}</button>
    </form>
    @push('scripts')
        @include('partials.admin-college-department-filter', [
            'departments' => $departments,
            'collegeElementId' => 'student-form-college',
            'departmentElementId' => 'student-form-department',
            'selectedDepartmentId' => old('department_id', $item->department_id),
        ])
    @endpush
@endsection
