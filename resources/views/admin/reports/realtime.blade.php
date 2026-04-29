@extends('layouts.app')
@section('title', __('nav.realtime_statistics'))
@section('content')
    <h1 class="mb-2 text-2xl font-bold tracking-tight text-slate-900">{{ __('nav.realtime_statistics') }}</h1>
    <p class="mb-6 max-w-3xl text-sm text-slate-600">{{ __('reports.realtime_intro') }}</p>

    <div class="mb-4">
        <a class="admin-btn-secondary py-2 text-sm" href="{{ route('admin.reports.realtime.export.excel') }}">{{ __('reports.export_excel') }}</a>
    </div>

    <div class="overflow-x-auto admin-card">
        <table class="w-full min-w-[720px] text-left text-sm" id="tbl-realtime">
            <thead class="admin-table-head">
                <tr>
                    <th class="whitespace-nowrap p-3">{{ __('reports.col_staff') }}</th>
                    <th class="whitespace-nowrap p-3">{{ __('reports.col_subject') }}</th>
                    <th class="whitespace-nowrap p-3">{{ __('reports.col_college') }}</th>
                    <th class="whitespace-nowrap p-3">{{ __('reports.col_department') }}</th>
                    <th class="whitespace-nowrap p-3">{{ __('reports.col_semester') }}</th>
                    <th class="whitespace-nowrap p-3">{{ __('reports.realtime_evaluation_count') }}</th>
                    <th class="whitespace-nowrap p-3">{{ __('reports.realtime_expected_students') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $r)
                    <tr class="border-b">
                        <td class="p-3">{{ $r['staff'] }}</td>
                        <td class="p-3">{{ $r['subject'] }}</td>
                        <td class="p-3">{{ $r['college'] ?? '—' }}</td>
                        <td class="p-3">{{ $r['department'] ?? '—' }}</td>
                        <td class="p-3">{{ $r['semester'] ?? '—' }}</td>
                        <td class="p-3 font-semibold text-indigo-700">{{ $r['evaluation_count'] }}</td>
                        <td class="p-3">{{ $r['expected_students'] }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="p-6 text-center text-slate-500">{{ __('reports.results_empty') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                window.initAdminDataTable('#tbl-realtime', {
                    order: [[5, 'desc']],
                });
            });
        </script>
    @endpush
@endsection
