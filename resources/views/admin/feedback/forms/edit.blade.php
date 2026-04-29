@extends('layouts.app')
@section('title', $form->title_en)
@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ $form->title_en }}</h1>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.feedback.forms.preview', $form) }}" class="admin-btn-secondary text-sm">{{ __('feedback.preview') }}</a>
            <button type="button"
                class="inline-flex items-center gap-1 rounded-2xl border-2 border-rose-200 bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-800 shadow-sm transition hover:bg-rose-100"
                onclick="window.submitDeleteFormResponses('delete-form-responses-form','delete-form-responses-confirmation','DELETE RESPONSES')">{{ __('admin.delete_form_responses') }}</button>
        </div>
    </div>
    <form id="delete-form-responses-form" action="{{ route('admin.feedback.forms.responses.delete-all', $form) }}" method="post" class="hidden">
        @csrf
        <input type="hidden" name="confirmation" id="delete-form-responses-confirmation" value="">
    </form>

    <form method="post" action="{{ route('admin.feedback.forms.update', $form) }}" class="admin-card mb-8 grid gap-4 p-6 md:grid-cols-2 md:p-8">
        @csrf @method('PUT')
        <div><label class="text-sm font-medium text-slate-700">{{ __('fields.title_en') }}</label><input name="title_en" value="{{ old('title_en', $form->title_en) }}" required class="admin-input"></div>
        <div><label class="text-sm font-medium text-slate-700">{{ __('fields.title_ku') }}</label><input name="title_ku" value="{{ old('title_ku', $form->title_ku) }}" class="admin-input"></div>
        <div><label class="text-sm font-medium text-slate-700">{{ __('fields.title_ar') }}</label><input name="title_ar" value="{{ old('title_ar', $form->title_ar) }}" class="admin-input"></div>
        <div><label class="text-sm font-medium text-slate-700">{{ __('fields.description_en') }}</label><textarea name="description_en" rows="3" class="admin-input">{{ old('description_en', $form->description_en) }}</textarea></div>
        <div><label class="text-sm font-medium text-slate-700">{{ __('fields.description_ku') }}</label><textarea name="description_ku" rows="3" class="admin-input">{{ old('description_ku', $form->description_ku) }}</textarea></div>
        <div><label class="text-sm font-medium text-slate-700">{{ __('fields.description_ar') }}</label><textarea name="description_ar" rows="3" class="admin-input">{{ old('description_ar', $form->description_ar) }}</textarea></div>
        <div>
            <label class="text-sm font-medium text-slate-700">Lifecycle</label>
            <select name="status" class="admin-input">
                @foreach(['draft'=>'Draft','active'=>'Active','closed'=>'Closed'] as $k=>$lab)
                    <option value="{{ $k }}" @selected(old('status', $form->status->value)==$k)>{{ $lab }}</option>
                @endforeach
            </select>
        </div>
        <div class="md:col-span-2"><button type="submit" class="admin-btn-primary">{{ __('admin.save') }}</button></div>
    </form>

    <div class="admin-card mb-8 p-6 md:p-8">
        <h2 class="mb-4 text-lg font-semibold">Version {{ $version->version_number }} window</h2>
        <form method="post" action="{{ route('admin.feedback.forms.versions.update', [$form, $version]) }}" class="grid gap-4 md:grid-cols-2">
            @csrf @method('PUT')
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="accepts_submissions" value="1" @checked(old('accepts_submissions', $version->accepts_submissions))> Accepting submissions</label>
            <div></div>
            <div><label class="text-sm font-medium text-slate-700">Starts</label><input type="datetime-local" name="starts_at" value="{{ old('starts_at', optional($version->starts_at)->format('Y-m-d\TH:i')) }}" class="admin-input"></div>
            <div><label class="text-sm font-medium text-slate-700">Ends</label><input type="datetime-local" name="ends_at" value="{{ old('ends_at', optional($version->ends_at)->format('Y-m-d\TH:i')) }}" class="admin-input"></div>
            <div class="md:col-span-2"><button type="submit" class="admin-btn-secondary">Update window</button></div>
        </form>
        <form method="post" action="{{ route('admin.feedback.forms.versions.publish', $form) }}" class="mt-4 border-t border-slate-100 pt-4">
            @csrf
            <button type="submit" class="text-sm text-amber-800 underline" onclick="return confirm('Create new version (copies questions)?')">Publish new version from current</button>
        </form>
    </div>

    <div class="grid gap-8 lg:grid-cols-2">
        <div class="admin-card p-6 md:p-8">
            <h2 class="mb-4 text-lg font-semibold">Questions (drag to reorder)</h2>
            <ul id="sort-questions" class="space-y-2">
                @foreach($questions as $q)
                    <li data-id="{{ $q->id }}" class="cursor-grab rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm active:cursor-grabbing">
                        <span class="font-medium">{{ $q->type->value }}</span> — {{ $q->label_en }}
                        <form action="{{ route('admin.feedback.forms.questions.destroy', [$form, $version, $q]) }}" method="post" class="float-right inline" onsubmit="return confirm('Delete question?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-600">×</button>
                        </form>
                    </li>
                @endforeach
            </ul>
            <form id="reorder-form" method="post" action="{{ route('admin.feedback.forms.questions.reorder', [$form, $version]) }}" class="mt-4">
                @csrf
                <button type="button" id="save-order" class="admin-btn-secondary py-2 text-sm">Save order</button>
            </form>
        </div>
        <div class="admin-card p-6 md:p-8">
            <h2 class="mb-4 text-lg font-semibold">Add question</h2>
            <form method="post" action="{{ route('admin.feedback.forms.questions.store', [$form, $version]) }}" class="space-y-3">
                @csrf
                <select name="type" class="admin-input">
                    <option value="likert_5">Likert 1–5</option>
                    <option value="yes_no">Yes / No</option>
                    <option value="multiple_choice">Multiple choice</option>
                    <option value="text">Text</option>
                    <option value="note">Note</option>
                </select>
                <input name="label_en" placeholder="Label EN" required class="admin-input">
                <input name="label_ku" placeholder="Label KU" class="admin-input">
                <input name="label_ar" placeholder="Label AR" class="admin-input">
                <input type="hidden" name="is_required" value="0">
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_required" value="1" checked> Required</label>
                <div>
                    <label class="text-xs font-medium text-slate-500">MC options JSON (optional)</label>
                    <textarea name="options_json" rows="3" class="admin-input font-mono text-xs" placeholder='{"choices":[{"key":"a","en":"A"}]}'></textarea>
                </div>
                <button type="submit" class="admin-btn-primary">Add</button>
            </form>
        </div>
    </div>
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const el = document.getElementById('sort-questions');
                if (el) new Sortable(el, { animation: 150 });
                document.getElementById('save-order')?.addEventListener('click', () => {
                    const form = document.getElementById('reorder-form');
                    form.querySelectorAll('input[name^="order"]').forEach(e => e.remove());
                    document.querySelectorAll('#sort-questions > li').forEach(li => {
                        const inp = document.createElement('input');
                        inp.type = 'hidden';
                        inp.name = 'order[]';
                        inp.value = li.dataset.id;
                        form.appendChild(inp);
                    });
                    form.submit();
                });
            });
            window.submitDeleteFormResponses ??= function (formId, inputId, phrase) {
                const typed = prompt(@json(__('admin.delete_form_responses_prompt')));
                if (typed !== phrase) {
                    if (typed !== null) alert(@json(__('admin.delete_form_responses_confirmation_invalid')));
                    return;
                }
                document.getElementById(inputId).value = typed;
                document.getElementById(formId).submit();
            };
        </script>
    @endpush
@endsection
