@extends('layouts.app')
@section('title', __('reports.results_title'))
@section('content')
    <h1 class="mb-2 text-2xl font-bold tracking-tight text-slate-900">{{ __('reports.results_title') }}</h1>
    <p class="mb-6 max-w-3xl text-sm text-slate-600">{{ __('reports.results_intro') }}</p>

    <form method="get" class="admin-filter-bar">
        <select name="college_id" class="admin-input min-w-[10rem] !mt-0">
            <option value="">{{ __('fields.college') }}</option>
            @foreach($lists['colleges'] as $c)<option value="{{ $c->id }}" @selected(($filters['college_id']??null)==$c->id)>{{ $c->name_en }}</option>@endforeach
        </select>
        <select name="department_id" class="admin-input min-w-[10rem] !mt-0">
            <option value="">{{ __('fields.department') }}</option>
            @foreach($lists['departments'] as $d)<option value="{{ $d->id }}" @selected(($filters['department_id']??null)==$d->id)>{{ $d->name_en }}</option>@endforeach
        </select>
        <select name="semester_id" class="admin-input min-w-[10rem] !mt-0">
            <option value="">{{ __('fields.semester') }}</option>
            @foreach($lists['semesters'] as $s)<option value="{{ $s->id }}" @selected(($filters['semester_id']??null)==$s->id)>{{ $s->name_en }}</option>@endforeach
        </select>
        <input name="subject" value="{{ $filters['subject'] ?? '' }}" placeholder="{{ __('fields.subject') }}" class="admin-input min-w-[8rem] !mt-0">
        <select name="form_version_id" class="admin-input min-w-[12rem] !mt-0">
            @foreach($versions as $v)<option value="{{ $v->id }}" @selected($versionId==$v->id)>Form #{{ $v->feedback_form_id }} v{{ $v->version_number }}</option>@endforeach
        </select>
        <button type="submit" class="admin-btn-primary shrink-0">{{ __('reports.apply_filter') }}</button>
    </form>

    @php $exportQuery = array_merge(request()->query(), ['form_version_id' => $versionId]); @endphp
    <div class="mb-4">
        @if($versionId)
            <a class="admin-btn-secondary py-2 text-sm" href="{{ route('admin.reports.results.export.excel', $exportQuery) }}">{{ __('reports.export_results_excel') }}</a>
        @endif
    </div>

    <div class="overflow-x-auto admin-card">
        <table class="w-full min-w-[720px] text-left text-sm" id="tbl-results">
            <thead class="admin-table-head">
                <tr>
                    <th class="whitespace-nowrap p-3">{{ __('reports.col_staff') }}</th>
                    <th class="whitespace-nowrap p-3">{{ __('reports.col_subject') }}</th>
                    <th class="whitespace-nowrap p-3">{{ __('reports.col_college') }}</th>
                    <th class="whitespace-nowrap p-3">{{ __('reports.col_department') }}</th>
                    <th class="whitespace-nowrap p-3">{{ __('reports.col_semester') }}</th>
                    <th class="whitespace-nowrap p-3">{{ __('reports.col_submissions') }}</th>
                    <th class="whitespace-nowrap p-3">{{ __('reports.col_overall') }}</th>
                    @foreach($questions as $q)
                        <th class="max-w-[14rem] p-3 text-xs font-normal leading-tight text-slate-700" title="{{ $q['label'] }}">{{ \Illuminate\Support\Str::limit($q['label'], 40) }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $r)
                    @php $byQ = collect($r['per_question'])->keyBy('question_id'); @endphp
                    <tr class="border-b">
                        <td class="p-3">{{ $r['staff'] }}</td>
                        <td class="p-3">{{ $r['subject'] }}</td>
                        <td class="p-3">{{ $r['college'] ?? '—' }}</td>
                        <td class="p-3">{{ $r['department'] ?? '—' }}</td>
                        <td class="p-3">{{ $r['semester'] ?? '—' }}</td>
                        <td class="p-3">{{ $r['submission_count'] }}</td>
                        <td class="p-3 font-medium">{{ $r['overall_average'] ?? '—' }}</td>
                        @foreach($questions as $q)
                            @php $cell = $byQ->get($q['question_id']); @endphp
                            <td class="p-3">{{ is_array($cell) && isset($cell['average']) && $cell['average'] !== null ? $cell['average'] : '—' }}</td>
                        @endforeach
                    </tr>
                @empty
                    <tr><td colspan="{{ 7 + count($questions) }}" class="p-6 text-center text-slate-500">{{ __('reports.results_empty') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                window.initAdminDataTable('#tbl-results');
            });
        </script>
    @endpush
@endsection
