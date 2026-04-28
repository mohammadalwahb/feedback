@extends('layouts.app')
@section('title', __('nav.audit'))
@section('content')
    <h1 class="mb-6 text-2xl font-bold tracking-tight text-slate-900">{{ __('nav.audit') }}</h1>
    <div class="overflow-x-auto admin-card">
        <table class="w-full text-left text-sm" id="tbl">
            <thead class="admin-table-head"><tr><th class="p-3">When</th><th class="p-3">Admin</th><th class="p-3">Action</th><th class="p-3">Target</th></tr></thead>
            <tbody>
                @foreach($items as $log)
                    <tr class="border-b">
                        <td class="p-3 whitespace-nowrap">{{ $log->created_at }}</td>
                        <td class="p-3">{{ $log->user?->email }}</td>
                        <td class="p-3">{{ $log->action }}</td>
                        <td class="p-3 text-xs">{{ $log->auditable_type }} #{{ $log->auditable_id }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $items->links() }}</div>
    @push('scripts')<script>document.addEventListener('DOMContentLoaded',()=>{window.initAdminDataTable('#tbl',{paging:false,lengthChange:false});});</script>@endpush
@endsection
