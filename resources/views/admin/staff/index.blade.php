@extends('layouts.app')
@section('title', __('nav.staff'))
@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ __('nav.staff') }}</h1>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.staff.create') }}" class="admin-btn-primary">{{ __('admin.add') }}</a>
            <button type="button"
                class="inline-flex items-center gap-1 rounded-2xl border-2 border-rose-200 bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-800 shadow-sm transition hover:bg-rose-100"
                onclick="window.submitAdminDeleteAll('delete-all-staff-form','delete-all-staff-confirmation','DELETE ALL')">{{ __('admin.delete_all_staff') }}</button>
        </div>
    </div>
    <form id="delete-all-staff-form" action="{{ route('admin.staff.delete-all') }}" method="post" class="hidden">
        @csrf
        <input type="hidden" name="confirmation" id="delete-all-staff-confirmation" value="">
    </form>
    <div class="overflow-x-auto admin-card">
        <table class="w-full text-left text-sm" id="tbl">
            <thead class="admin-table-head">
                <tr><th class="p-3">{{ __('fields.instructor') }}</th><th class="p-3">{{ __('fields.subject') }}</th><th class="p-3">{{ __('fields.college') }}</th><th class="p-3"></th></tr>
            </thead>
            <tbody>
                @foreach($items as $s)
                    <tr class="border-b">
                        <td class="p-3">{{ $s->instructor_name }} <span class="text-slate-400">#{{ $s->staff_employee_id }}</span></td>
                        <td class="p-3">{{ $s->subject_name }}</td>
                        <td class="p-3">{{ $s->college?->name_en }}</td>
                        <td class="p-3 text-right">
                            <a href="{{ route('admin.staff.edit', $s) }}" class="text-indigo-600">{{ __('admin.edit') }}</a>
                            <form action="{{ route('admin.staff.destroy', $s) }}" method="post" class="inline" onsubmit="return confirm('{{ __('admin.confirm_delete') }}')">@csrf @method('DELETE')
                                <button type="submit" class="text-red-600">{{ __('admin.delete') }}</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $items->links() }}</div>
    @push('scripts')
        <script>
            window.submitAdminDeleteAll ??= function (formId, inputId, phrase) {
                const typed = prompt(@json(__('admin.delete_all_prompt')));
                if (typed !== phrase) {
                    if (typed !== null) alert(@json(__('admin.delete_all_mismatch')));
                    return;
                }
                document.getElementById(inputId).value = typed;
                document.getElementById(formId).submit();
            };
            document.addEventListener('DOMContentLoaded', () => { window.initAdminDataTable('#tbl'); });
        </script>
    @endpush
@endsection
