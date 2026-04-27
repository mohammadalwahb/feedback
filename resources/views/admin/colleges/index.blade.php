@extends('layouts.app')
@section('title', __('nav.colleges'))
@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-4">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ __('nav.colleges') }}</h1>
        <a href="{{ route('admin.colleges.create') }}" class="admin-btn-primary">{{ __('admin.add') }}</a>
    </div>
    <div class="overflow-x-auto admin-card">
        <table class="dt-table w-full text-left text-sm" id="tbl">
            <thead class="admin-table-head">
                <tr>
                    <th class="p-3">{{ __('fields.name_en') }}</th>
                    <th class="p-3">{{ __('fields.name_ku') }}</th>
                    <th class="p-3">{{ __('fields.name_ar') }}</th>
                    <th class="p-3"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $c)
                    <tr class="border-b border-slate-100">
                        <td class="p-3">{{ $c->name_en }}</td>
                        <td class="p-3">{{ $c->name_ku }}</td>
                        <td class="p-3">{{ $c->name_ar }}</td>
                        <td class="p-3 text-right">
                            <a href="{{ route('admin.colleges.edit', $c) }}" class="text-indigo-600 hover:underline">{{ __('admin.edit') }}</a>
                            <form action="{{ route('admin.colleges.destroy', $c) }}" method="post" class="inline" onsubmit="return confirm('{{ __('admin.confirm_delete') }}')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">{{ __('admin.delete') }}</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $items->links() }}</div>
    @push('scripts')
        <script>document.addEventListener('DOMContentLoaded',()=>{if(document.getElementById('tbl')) new DataTable('#tbl',{pageLength:25});});</script>
    @endpush
@endsection
