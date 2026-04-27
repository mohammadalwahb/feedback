@extends('layouts.app')
@section('title', __('nav.reports'))
@section('content')
    <h1 class="mb-6 text-2xl font-bold tracking-tight text-slate-900">{{ __('reports.participation_title') }}</h1>
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
        <select name="form_version_id" class="admin-input min-w-[12rem] !mt-0">
            @foreach($versions as $v)<option value="{{ $v->id }}" @selected(($filters['form_version_id']??null)==$v->id)>Form #{{ $v->feedback_form_id }} v{{ $v->version_number }}</option>@endforeach
        </select>
        <button type="submit" class="admin-btn-primary shrink-0">Filter</button>
    </form>
    <div class="grid gap-6 md:grid-cols-3">
        <div class="admin-stat-tile"><p class="text-sm font-medium text-slate-500">{{ __('reports.eligible_students') }}</p><p class="mt-2 text-3xl font-bold tabular-nums text-indigo-700">{{ $participation['eligible'] }}</p></div>
        <div class="admin-stat-tile"><p class="text-sm font-medium text-slate-500">{{ __('reports.submitted_students') }}</p><p class="mt-2 text-3xl font-bold tabular-nums text-emerald-700">{{ $participation['submitted'] }}</p></div>
        <div class="admin-stat-tile"><p class="text-sm font-medium text-slate-500">{{ __('reports.participation_ratio') }}</p><p class="mt-2 text-3xl font-bold tabular-nums text-amber-700">{{ number_format($participation['ratio']*100,1) }}%</p></div>
    </div>
    <div class="admin-card mt-8 max-w-md p-6">
        <canvas id="pie"></canvas>
    </div>
    @push('scripts')
        <script>
            const sub = {{ $participation['submitted'] }}, el = {{ $participation['eligible'] }}, rem = Math.max(0, el - sub);
            new Chart(document.getElementById('pie'), {
                type: 'pie',
                data: {
                    labels: ['Submitted', 'Remaining'],
                    datasets: [{ data: [sub, rem], backgroundColor: ['#7c3aed', '#e2e8f0'] }]
                },
                options: { plugins: { legend: { position: 'bottom' } } }
            });
        </script>
    @endpush
@endsection
