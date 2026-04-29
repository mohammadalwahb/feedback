@extends('layouts.app')
@section('title', __('nav.feedback_forms'))
@section('content')
    <h1 class="mb-6 text-2xl font-bold tracking-tight text-slate-900">{{ __('nav.feedback_forms') }}</h1>
    <form method="post" action="{{ route('admin.feedback.forms.store') }}" class="admin-card mb-8 grid gap-4 p-6 md:grid-cols-2 md:p-8">
        @csrf
        <div>
            <label class="block text-sm font-medium text-slate-700">{{ __('fields.title_en') }}</label>
            <input name="title_en" required class="admin-input" placeholder="e.g. Fall 2026 evaluation">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700">{{ __('fields.title_ku') }}</label>
            <input name="title_ku" class="admin-input">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700">{{ __('fields.title_ar') }}</label>
            <input name="title_ar" class="admin-input">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700">{{ __('fields.description_en') }}</label>
            <textarea name="description_en" rows="2" class="admin-input"></textarea>
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700">{{ __('fields.description_ku') }}</label>
            <textarea name="description_ku" rows="2" class="admin-input"></textarea>
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700">{{ __('fields.description_ar') }}</label>
            <textarea name="description_ar" rows="2" class="admin-input"></textarea>
        </div>
        <div class="md:col-span-2">
            <button type="submit" class="admin-btn-primary shrink-0">{{ __('admin.add') }}</button>
        </div>
    </form>
    <div class="overflow-x-auto admin-card">
        <table class="w-full text-left text-sm" id="tbl">
            <thead class="admin-table-head"><tr><th class="p-3">{{ __('fields.title_en') }}</th><th class="p-3">Status</th><th class="p-3"></th></tr></thead>
            <tbody>
                @foreach($items as $f)
                    <tr class="border-b">
                        <td class="p-3">{{ $f->title_en }}</td>
                        <td class="p-3">{{ $f->status->value }}</td>
                        <td class="p-3 text-right whitespace-nowrap">
                            <a href="{{ route('admin.feedback.forms.edit', $f) }}" class="text-indigo-600">{{ __('admin.edit') }}</a>
                            <a href="{{ route('admin.feedback.forms.preview', $f) }}" class="text-slate-600">{{ __('feedback.preview') }}</a>
                            <form action="{{ route('admin.feedback.forms.destroy', $f) }}" method="post" class="inline" onsubmit="return confirm('{{ __('admin.confirm_delete') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600">{{ __('admin.delete') }}</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @push('scripts')<script>document.addEventListener('DOMContentLoaded',()=>{window.initAdminDataTable('#tbl',{pageLength:20});});</script>@endpush
@endsection
