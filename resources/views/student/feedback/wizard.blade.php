@extends('layouts.app')
@push('body_class')
    bg-gradient-to-b from-slate-50 via-white to-violet-50/40
@endpush
@section('title', __('nav.feedback_forms'))
@section('content')
    <div class="mx-auto max-w-3xl px-3 pb-16 pt-4 md:px-4 md:pt-8">
        {{-- Progress --}}
        <div class="mb-8 flex max-w-md flex-col items-center gap-3 md:mx-auto">
            <div class="flex w-full gap-1.5">
                @for($i = 0; $i < $totalSteps; $i++)
                    <div class="h-2 flex-1 overflow-hidden rounded-full bg-slate-200/90">
                        <div class="h-full rounded-full bg-gradient-to-r from-violet-600 to-indigo-500 transition-all duration-500 {{ $i <= $step ? 'w-full' : 'w-0' }}"></div>
                    </div>
                @endfor
            </div>
            <p class="flex items-center gap-2 text-sm font-medium text-slate-500">
                <svg class="h-4 w-4 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                </svg>
                {{ __('student.step', ['current' => $step + 1, 'total' => $totalSteps]) }}
            </p>
        </div>

        {{-- Question --}}
        <div class="mb-10 text-center md:mb-12">
            @php
                $questionLabels = collect([$question->label_en, $question->label_ku, $question->label_ar])
                    ->filter(fn ($x) => filled($x))
                    ->unique()
                    ->values();
                $form = $version->form;
                $descriptions = collect([$form?->description_en, $form?->description_ku, $form?->description_ar])
                    ->filter(fn ($x) => filled($x))
                    ->unique()
                    ->values();
            @endphp
            <h1 class="mx-auto max-w-2xl text-2xl font-bold leading-snug tracking-tight text-slate-900 md:text-3xl">
                @foreach($questionLabels as $label)
                    <div>{{ $label }}</div>
                @endforeach
                @if($question->is_required)
                    <span class="ml-1 align-super text-rose-500" title="{{ __('validation.required') }}">*</span>
                @endif
            </h1>
            @if($descriptions->isNotEmpty())
                <div class="mt-4 space-y-1 text-sm text-slate-600">
                    @foreach($descriptions as $desc)
                        <p>{{ $desc }}</p>
                    @endforeach
                </div>
            @endif
            @if($question->type->value === 'likert_5')
                <p class="mt-3 flex items-center justify-center gap-2 text-sm text-slate-500">
                    <svg class="h-4 w-4 shrink-0 text-amber-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                    </svg>
                    {{ __('student.likert_scale_hint') }}
                </p>
            @endif
        </div>

        <form method="post" action="{{ route('student.feedback.wizard.save') }}" class="space-y-10 md:space-y-12">
            @csrf
            @foreach($staffIds as $sid)
                @php
                    $st = $staffModels[$sid];
                    $raw = $existing[$sid] ?? null;
                    $val = is_array($raw) ? ($raw['v'] ?? $raw['t'] ?? null) : $raw;
                @endphp
                <div class="rounded-3xl border border-slate-200/80 bg-white/90 p-6 shadow-xl shadow-slate-200/40 ring-1 ring-slate-100/80 backdrop-blur-sm md:p-8">
                    <div class="mb-8 flex flex-col items-center justify-center gap-3 text-center">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-violet-500 to-indigo-600 text-white shadow-lg shadow-indigo-300/50">
                            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-base font-semibold text-slate-900">{{ $st->instructor_name }}</p>
                            <p class="mt-1 flex items-center justify-center gap-2 text-sm text-slate-500">
                                <svg class="h-4 w-4 text-indigo-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v15.128A9.056 9.056 0 016 18c1.052 0 2.062.18 3 .512m0-15.042A8.967 8.967 0 0118 3.75c1.052 0 2.062.18 3 .512v15.128a9.056 9.056 0 01-3 .512m-6-15.042v15.042" />
                                </svg>
                                {{ $st->subject_name }}
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-col items-center">
                        @switch($question->type->value)
                            @case('likert_5')
                                <div class="flex flex-wrap justify-center gap-4 md:gap-5">
                                    @for($i = 1; $i <= 5; $i++)
                                        <label class="group flex min-w-[4.5rem] cursor-pointer flex-col items-center justify-center gap-1 rounded-2xl border-2 border-slate-200 bg-slate-50/50 px-5 py-5 transition-all duration-200 hover:border-indigo-300 hover:bg-white hover:shadow-md has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50 has-[:checked]:shadow-lg has-[:checked]:shadow-indigo-200/50">
                                            <input class="sr-only" type="radio" name="per_staff[{{ $sid }}]" value="{{ $i }}" @checked((string) $val === (string) $i) @if($question->is_required && $i === 1) required @endif>
                                            <span class="text-2xl font-bold text-indigo-600 tabular-nums group-has-[:checked]:scale-110">{{ $i }}</span>
                                        </label>
                                    @endfor
                                </div>
                                @break

                            @case('yes_no')
                                <div class="flex w-full max-w-md flex-wrap justify-center gap-5 sm:gap-6">
                                    <label class="group flex min-h-[120px] min-w-[140px] flex-1 cursor-pointer flex-col items-center justify-center gap-3 rounded-2xl border-2 border-slate-200 bg-white px-6 py-5 transition-all duration-200 hover:border-emerald-400/80 hover:shadow-md has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50 has-[:checked]:shadow-lg has-[:checked]:shadow-emerald-200/40">
                                        <input class="sr-only" type="radio" name="per_staff[{{ $sid }}]" value="1" @checked($val === true || $val === '1' || $val === 1) @if($question->is_required) required @endif>
                                        <span class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 group-has-[:checked]:ring-2 group-has-[:checked]:ring-emerald-500">
                                            <svg class="h-7 w-7" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                            </svg>
                                        </span>
                                        <span class="text-sm font-semibold text-slate-800">{{ __('student.yes') }}</span>
                                    </label>
                                    <label class="group flex min-h-[120px] min-w-[140px] flex-1 cursor-pointer flex-col items-center justify-center gap-3 rounded-2xl border-2 border-slate-200 bg-white px-6 py-5 transition-all duration-200 hover:border-rose-400/80 hover:shadow-md has-[:checked]:border-rose-500 has-[:checked]:bg-rose-50 has-[:checked]:shadow-lg has-[:checked]:shadow-rose-200/40">
                                        <input class="sr-only" type="radio" name="per_staff[{{ $sid }}]" value="0" @checked($val === false || $val === '0' || $val === 0)>
                                        <span class="flex h-12 w-12 items-center justify-center rounded-full bg-rose-100 text-rose-600 group-has-[:checked]:ring-2 group-has-[:checked]:ring-rose-500">
                                            <svg class="h-7 w-7" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </span>
                                        <span class="text-sm font-semibold text-slate-800">{{ __('student.no') }}</span>
                                    </label>
                                </div>
                                @break

                            @case('multiple_choice')
                                <div class="flex w-full max-w-lg flex-col items-stretch gap-4">
                                    @foreach(($question->options['choices'] ?? []) as $chIdx => $ch)
                                        @php $choiceLabel = $ch[app()->getLocale()] ?? $ch['en'] ?? $ch['key']; @endphp
                                        <label class="group flex cursor-pointer items-center gap-4 rounded-2xl border-2 border-slate-200 bg-slate-50/40 px-4 py-4 transition-all duration-200 hover:border-violet-400 hover:bg-white hover:shadow-md has-[:checked]:border-violet-600 has-[:checked]:bg-violet-50 has-[:checked]:shadow-md has-[:checked]:shadow-violet-200/50 md:px-5 md:py-5">
                                            <input class="sr-only" type="radio" name="per_staff[{{ $sid }}]" value="{{ $ch['key'] }}" @checked($val == ($ch['key'] ?? '')) @if($question->is_required && $chIdx === 0) required @endif>
                                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border-2 border-slate-200 bg-white text-slate-400 transition-colors group-has-[:checked]:border-violet-600 group-has-[:checked]:bg-violet-600 group-has-[:checked]:text-white">
                                                <svg class="h-5 w-5 opacity-0 transition-opacity group-has-[:checked]:opacity-100" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                                </svg>
                                            </span>
                                            <span class="text-left text-sm font-medium leading-snug text-slate-800">{{ $choiceLabel }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                @break

                            @default
                                <div class="w-full max-w-xl">
                                    <label class="mb-2 flex items-center justify-center gap-2 text-sm font-medium text-slate-600">
                                        <svg class="h-4 w-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                        </svg>
                                        {{ __('student.optional_comment') }}
                                    </label>
                                    <textarea name="per_staff[{{ $sid }}]" rows="4" class="mx-auto block w-full rounded-2xl border-2 border-slate-200 bg-white px-4 py-3 text-slate-800 shadow-inner transition-colors placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20" placeholder="{{ __('student.optional_comment') }}">{{ is_array($raw) ? ($raw['t'] ?? '') : '' }}</textarea>
                                </div>
                        @endswitch
                    </div>
                </div>
            @endforeach

            <div class="flex justify-center pt-2">
                <button type="submit" class="group inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-violet-600 to-indigo-600 px-8 py-3.5 text-sm font-semibold text-white shadow-lg shadow-indigo-400/30 transition hover:from-violet-500 hover:to-indigo-500 hover:shadow-xl hover:shadow-indigo-400/40 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    {{ __('student.next') }}
                    <svg class="h-5 w-5 transition-transform group-hover:translate-x-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                    </svg>
                </button>
            </div>
        </form>
    </div>
@endsection
