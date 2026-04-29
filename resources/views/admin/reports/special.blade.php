@extends('layouts.app')
@section('title', __('reports.special_title'))
@section('content')
    <h1 class="mb-6 text-2xl font-bold tracking-tight text-slate-900">{{ __('reports.special_title') }}</h1>
    <form method="get" class="admin-filter-bar">
        <select id="report-special-college" name="college_id" class="admin-input min-w-[10rem] !mt-0"><option value="">{{ __('fields.college') }}</option>@foreach($lists['colleges'] as $c)<option value="{{ $c->id }}" @selected(($filters['college_id']??null)==$c->id)>{{ $c->name_en }}</option>@endforeach</select>
        <select id="report-special-department" name="department_id" class="admin-input min-w-[10rem] !mt-0"><option value="">{{ __('fields.department') }}</option>@foreach($lists['departments'] as $d)<option value="{{ $d->id }}" @selected(($filters['department_id']??null)==$d->id)>{{ $d->name_en }}</option>@endforeach</select>
        <select name="semester_id" class="admin-input min-w-[10rem] !mt-0"><option value="">{{ __('fields.semester') }}</option>@foreach($lists['semesters'] as $s)<option value="{{ $s->id }}" @selected(($filters['semester_id']??null)==$s->id)>{{ $s->name_en }}</option>@endforeach</select>
        <input name="subject" value="{{ $filters['subject'] ?? '' }}" placeholder="{{ __('fields.subject') }}" class="admin-input min-w-[8rem] !mt-0">
        <select name="form_version_id" class="admin-input min-w-[12rem] !mt-0">@foreach($versions as $v)<option value="{{ $v->id }}" @selected($versionId==$v->id)>#{{ $v->feedback_form_id }} v{{ $v->version_number }}</option>@endforeach</select>
        <button type="submit" class="admin-btn-primary shrink-0">Filter</button>
    </form>
    <div class="mb-4 flex flex-wrap gap-2">
        <a class="admin-btn-secondary py-2 text-sm" href="{{ route('admin.reports.export.excel', request()->query()) }}">{{ __('reports.export_excel') }}</a>
        <a class="admin-btn-secondary py-2 text-sm" href="{{ route('admin.reports.export.pdf', request()->query()) }}">{{ __('reports.export_pdf') }}</a>
        <a class="admin-btn-secondary py-2 text-sm" href="{{ route('admin.reports.export.csv', request()->query()) }}">{{ __('reports.export_csv') }}</a>
    </div>
    <div class="overflow-x-auto admin-card">
        <table class="w-full text-left text-sm" id="tbl">
            <thead class="admin-table-head"><tr><th class="p-3">Staff</th><th class="p-3">Subject</th><th class="p-3">College</th><th class="p-3">Dept</th><th class="p-3">Sem</th><th class="p-3">Overall</th></tr></thead>
            <tbody>
                @foreach($rows as $r)
                    <tr class="border-b">
                        <td class="p-3">{{ $r['staff'] }}</td>
                        <td class="p-3">{{ $r['subject'] }}</td>
                        <td class="p-3">{{ $r['college'] }}</td>
                        <td class="p-3">{{ $r['department'] }}</td>
                        <td class="p-3">{{ $r['semester'] }}</td>
                        <td class="p-3">{{ $r['overall'] ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @push('scripts')
        @include('partials.admin-college-department-filter', [
            'departments' => $lists['departments'],
            'collegeElementId' => 'report-special-college',
            'departmentElementId' => 'report-special-department',
            'selectedDepartmentId' => $filters['department_id'] ?? null,
        ])
        <script>document.addEventListener('DOMContentLoaded',()=>{window.initAdminDataTable('#tbl');});</script>
    @endpush
@endsection
