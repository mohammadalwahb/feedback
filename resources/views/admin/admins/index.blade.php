@extends('layouts.app')
@section('title', __('nav.admins'))
@section('content')
    <div class="mb-4 flex justify-between">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ __('nav.admins') }}</h1>
        <a href="{{ route('admin.admins.create') }}" class="admin-btn-primary">{{ __('admin.add') }}</a>
    </div>
    <div class="overflow-x-auto admin-card">
        <table class="w-full text-left text-sm" id="tbl">
            <thead class="admin-table-head"><tr><th class="p-3">{{ __('fields.email') }}</th><th class="p-3">{{ __('fields.english_name') }}</th><th class="p-3"></th></tr></thead>
            <tbody>
                @foreach($items as $a)
                    <tr class="border-b">
                        <td class="p-3">{{ $a->email }}</td>
                        <td class="p-3">{{ $a->english_name }}</td>
                        <td class="p-3 text-right">
                            <a href="{{ route('admin.admins.edit', $a) }}" class="text-indigo-600">{{ __('admin.edit') }}</a>
                            <form action="{{ route('admin.admins.destroy', $a) }}" method="post" class="inline" onsubmit="return confirm('{{ __('admin.confirm_delete') }}')">@csrf @method('DELETE')
                                <button type="submit" class="text-red-600">{{ __('admin.delete') }}</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $items->links() }}</div>
    @push('scripts')<script>document.addEventListener('DOMContentLoaded',()=>{new DataTable('#tbl',{pageLength:25});});</script>@endpush
@endsection
