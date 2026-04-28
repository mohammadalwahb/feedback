@extends('layouts.app')
@section('title', __('nav.feedback_forms'))
@section('content')
    <h1 class="mb-6 text-2xl font-bold tracking-tight text-slate-900">{{ __('nav.feedback_forms') }}</h1>
    <form method="post" action="{{ route('admin.feedback.forms.store') }}" class="admin-filter-bar mb-8 max-w-2xl items-end">
        @csrf
        <div class="min-w-[200px] grow">
            <label class="block text-sm font-medium text-slate-700">{{ __('fields.title_en') }}</label>
            <input name="title_en" required class="admin-input" placeholder="e.g. Fall 2026 evaluation">
        </div>
        <button type="submit" class="admin-btn-primary shrink-0">{{ __('admin.add') }}</button>
    </form>
    <div class="overflow-x-auto admin-card">
        <table class="w-full text-left text-sm" id="tbl">
            <thead class="admin-table-head"><tr><th class="p-3">{{ __('fields.title_en') }}</th><th class="p-3">Status</th><th class="p-3"></th></tr></thead>
            <tbody>
                @foreach($items as $f)
                    <tr class="border-b">
                        <td class="p-3">{{ $f->title_en }}</td>
                        <td class="p-3">{{ $f->status->value }}</td>
                        <td class="p-3 text-right">
                            <a href="{{ route('admin.feedback.forms.edit', $f) }}" class="text-indigo-600">{{ __('admin.edit') }}</a>
                            <a href="{{ route('admin.feedback.forms.preview', $f) }}" class="text-slate-600">{{ __('feedback.preview') }}</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @push('scripts')<script>document.addEventListener('DOMContentLoaded',()=>{window.initAdminDataTable('#tbl',{pageLength:20});});</script>@endpush
@endsection
