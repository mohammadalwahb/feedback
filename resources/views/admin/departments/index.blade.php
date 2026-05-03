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
            <thead class="admin-table-head">
                <tr>
                    <th class="p-3">{{ __('fields.college') }}</th>
                    <th class="p-3">{{ __('fields.name_en') }}</th>
                    <th class="p-3 text-right">{{ __('fields.students_count') }}</th>
                    <th class="p-3"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $d)
                    <tr class="border-b">
                        <td class="p-3">{{ $d->college?->name_en }}</td>
                        <td class="p-3">{{ $d->name_en }}</td>
                        <td class="p-3 text-right tabular-nums">{{ $d->students_count }}</td>
                        <td class="p-3 text-right">
                            <div class="inline-flex max-w-xl flex-wrap items-center justify-end gap-x-3 gap-y-1">
                            <button type="button" class="text-amber-700 hover:underline" onclick="window.submitDepartmentDeleteResponses('delete-dept-responses-{{ $d->id }}','delete-dept-responses-confirmation-{{ $d->id }}')">{{ __('admin.delete_department_responses') }}</button>
                            <button type="button" class="text-rose-700 hover:underline" onclick="window.submitDepartmentDeleteStudents('delete-dept-students-{{ $d->id }}','delete-dept-students-confirmation-{{ $d->id }}')">{{ __('admin.delete_department_students') }}</button>
                            <a href="{{ route('admin.departments.edit', $d) }}" class="text-indigo-600 hover:underline">{{ __('admin.edit') }}</a>
                            <form action="{{ route('admin.departments.destroy', $d) }}" method="post" class="inline" onsubmit="return confirm('{{ __('admin.confirm_delete') }}')">@csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">{{ __('admin.delete') }}</button>
                            </form>
                            <form id="delete-dept-responses-{{ $d->id }}" action="{{ route('admin.departments.delete-responses', $d) }}" method="post" class="hidden">@csrf
                                <input type="hidden" name="confirmation" id="delete-dept-responses-confirmation-{{ $d->id }}" value="">
                            </form>
                            <form id="delete-dept-students-{{ $d->id }}" action="{{ route('admin.departments.delete-students', $d) }}" method="post" class="hidden">@csrf
                                <input type="hidden" name="confirmation" id="delete-dept-students-confirmation-{{ $d->id }}" value="">
                            </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @push('scripts')
        <script>
            window.submitDepartmentDeleteStudents = function (formId, inputId) {
                const phrase = 'DELETE ALL';
                const typed = prompt(@json(__('admin.delete_department_students_prompt')));
                if (typed !== phrase) {
                    if (typed !== null) alert(@json(__('admin.delete_all_mismatch')));
                    return;
                }
                document.getElementById(inputId).value = typed;
                document.getElementById(formId).submit();
            };
            window.submitDepartmentDeleteResponses = function (formId, inputId) {
                const phrase = 'DELETE RESPONSES';
                const typed = prompt(@json(__('admin.delete_department_responses_prompt')));
                if (typed !== phrase) {
                    if (typed !== null) alert(@json(__('admin.delete_department_responses_confirmation_invalid')));
                    return;
                }
                document.getElementById(inputId).value = typed;
                document.getElementById(formId).submit();
            };
            document.addEventListener('DOMContentLoaded', () => { window.initAdminDataTable('#tbl'); });
        </script>
    @endpush
@endsection
