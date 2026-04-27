@extends('layouts.app')
@section('title', __('reports.staff_title'))
@section('content')
    <h1 class="mb-6 text-2xl font-bold tracking-tight text-slate-900">{{ __('reports.staff_title') }}</h1>
    <form method="get" class="admin-filter-bar mb-8">
        <select name="staff_subject_id" class="admin-input max-w-md grow !mt-0">
            <option value="">{{ __('fields.instructor') }} / {{ __('fields.subject') }}</option>
            @foreach($staffList as $s)
                <option value="{{ $s->id }}" @selected(request('staff_subject_id')==$s->id)>{{ $s->instructor_name }} — {{ $s->subject_name }} ({{ $s->department?->name_en }})</option>
            @endforeach
        </select>
        <select name="form_version_id" class="admin-input min-w-[12rem] !mt-0">
            @foreach($versions as $v)
                <option value="{{ $v->id }}" @selected(request('form_version_id')==$v->id)>#{{ $v->feedback_form_id }} v{{ $v->version_number }}</option>
            @endforeach
        </select>
        <button type="submit" class="admin-btn-primary shrink-0">Run</button>
    </form>
    @if($staffSubject && $stats)
        <div class="admin-card mb-6 p-6">
            <p class="text-lg font-semibold text-slate-900">{{ $staffSubject->instructor_name }} — {{ $staffSubject->subject_name }}</p>
            <p class="mt-1 text-sm text-slate-600">{{ __('reports.likert_overall') }}: <strong>{{ $overall ?? '—' }}</strong> · {{ __('reports.dept_avg') }}: <strong>{{ $deptAvg ?? '—' }}</strong></p>
        </div>
        <div class="overflow-x-auto admin-card">
            <table class="w-full text-left text-sm" id="tbl">
                <thead class="admin-table-head"><tr><th class="p-3">Question</th><th class="p-3">Type</th><th class="p-3">Aggregate</th></tr></thead>
                <tbody>
                    @foreach($stats as $row)
                        <tr class="border-b">
                            <td class="p-3">{{ $row['label'] }}</td>
                            <td class="p-3">{{ $row['type'] }}</td>
                            <td class="p-3">
                                @if($row['likert_avg'] !== null) Avg {{ $row['likert_avg'] }} @endif
                                @if($row['yes_pct'] !== null) Yes {{ $row['yes_pct'] }}% @endif
                                @if($row['choice_counts']) {{ json_encode($row['choice_counts']) }} @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @push('scripts')<script>document.addEventListener('DOMContentLoaded',()=>{new DataTable('#tbl',{pageLength:25});});</script>@endpush
    @endif
@endsection
