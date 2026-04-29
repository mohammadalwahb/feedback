@extends('layouts.app')
@section('title', __('nav.departments'))
@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-4">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ __('nav.departments') }}</h1>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.departments.export.excel') }}" class="admin-btn-secondary py-2 text-sm">{{ __('admin.export_departments_excel') }}</a>
            <a href="{{ route('admin.departments.create') }}" class="admin-btn-primary">{{ __('admin.add') }}</a>
        </div>
    </div>
    <div class="overflow-x-auto admin-card">
        <table class="w-full text-left text-sm" id="tbl">
            <thead class="admin-table-head"><tr><th class="p-3">{{ __('fields.college') }}</th><th class="p-3">{{ __('fields.name_en') }}</th><th class="p-3"></th></tr></thead>
            <tbody>
                @foreach($items as $d)
                    <tr class="border-b">
                        <td class="p-3">{{ $d->college?->name_en }}</td>
                        <td class="p-3">{{ $d->name_en }}</td>
                        <td class="p-3 text-right">
                            <a href="{{ route('admin.departments.edit', $d) }}" class="text-indigo-600">{{ __('admin.edit') }}</a>
                            <form action="{{ route('admin.departments.destroy', $d) }}" method="post" class="inline" onsubmit="return confirm('{{ __('admin.confirm_delete') }}')">@csrf @method('DELETE')
                                <button type="submit" class="text-red-600">{{ __('admin.delete') }}</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @push('scripts')<script>document.addEventListener('DOMContentLoaded',()=>{window.initAdminDataTable('#tbl');});</script>@endpush
@endsection
