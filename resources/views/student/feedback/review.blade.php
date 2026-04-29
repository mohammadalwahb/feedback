@extends('layouts.app')
@push('body_class')
    bg-gradient-to-b from-slate-50 via-white to-violet-50/40
@endpush
@section('title', __('student.review_title'))
@section('content')
    <div class="mx-auto max-w-3xl px-3 pb-16 pt-6 md:px-4 md:pt-10">
        <div class="mb-10 text-center">
            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-violet-500 to-indigo-600 text-white shadow-lg shadow-indigo-300/40">
                <svg class="h-8 w-8" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 md:text-3xl">{{ __('student.review_title') }}</h1>
            <p class="mx-auto mt-3 flex max-w-xl items-start justify-center gap-2 text-sm leading-relaxed text-slate-600">
                <svg class="mt-0.5 h-4 w-4 shrink-0 text-amber-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                </svg>
                {{ __('student.review_note') }}
            </p>
        </div>

        <div class="space-y-8 md:space-y-10">
            @foreach($staffIds as $sid)
                @php $st = $staffModels[$sid]; @endphp
                <div class="rounded-3xl border border-slate-200/80 bg-white/95 p-6 shadow-xl shadow-slate-200/50 ring-1 ring-slate-100/80 backdrop-blur-sm md:p-8">
                    <div class="mb-6 flex flex-col items-center border-b border-slate-100 pb-6 text-center">
                        <div class="mb-3 flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-100 text-indigo-700">
                            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                            </svg>
                        </div>
                        <p class="text-base font-semibold text-slate-900">{{ $st->instructor_name }}</p>
                        <p class="mt-1 flex items-center justify-center gap-2 text-sm text-slate-500">
                            <svg class="h-4 w-4 text-violet-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v15.128A9.056 9.056 0 016 18c1.052 0 2.062.18 3 .512m0-15.042A8.967 8.967 0 0118 3.75c1.052 0 2.062.18 3 .512v15.128a9.056 9.056 0 01-3 .512m-6-15.042v15.042" />
                            </svg>
                            {{ $st->subject_name }}
                        </p>
                    </div>
                    <ul class="mx-auto max-w-xl space-y-4">
                        @foreach($version->questions as $q)
                            @php
                                $cell = $answers[$q->id][$sid] ?? null;
                                $display = '—';
                                if (is_array($cell)) {
                                    switch ($q->type->value) {
                                        case 'likert_5':
                                            $n = (int) ($cell['v'] ?? 0);
                                            $display = $n >= 1 && $n <= 5 ? $n.'/5' : '—';
                                            break;
                                        case 'yes_no':
                                            $b = $cell['v'] ?? null;
                                            if ($b === true || $b === 1 || $b === '1') {
                                                $display = __('student.yes');
                                            } elseif ($b === false || $b === 0 || $b === '0') {
                                                $display = __('student.no');
                                            }
                                            break;
                                        case 'multiple_choice':
                                            $key = (string) ($cell['v'] ?? '');
                                            foreach (($q->options['choices'] ?? []) as $ch) {
                                                if (($ch['key'] ?? '') === $key) {
                                                    $display = $ch[app()->getLocale()] ?? $ch['en'] ?? $key;
                                                    break;
                                                }
                                            }
                                            break;
                                        default:
                                            $display = trim((string) ($cell['t'] ?? '')) ?: '—';
                                    }
                                }
                            @endphp
                            @php
                                $questionLabels = collect([$q->label_en, $q->label_ku, $q->label_ar])
                                    ->filter(fn ($x) => filled($x))
                                    ->unique()
                                    ->values();
                            @endphp
                            <li class="rounded-2xl border border-slate-100 bg-slate-50/60 px-4 py-4 text-center md:px-5 md:text-left">
                                <div class="mb-2 flex flex-col items-center gap-1 md:flex-row md:items-center md:gap-2">
                                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide text-violet-600">
                                        <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6h.007v.008H3.75V6zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                        </svg>
                                        {{ __('student.review_question') }}
                                    </span>
                                </div>
                                <div class="space-y-1 text-sm font-medium text-slate-800">
                                    @foreach($questionLabels as $label)
                                        <p>{{ $label }}</p>
                                    @endforeach
                                </div>
                                <div class="mt-3 flex items-center justify-center gap-2 border-t border-slate-200/80 pt-3 md:justify-start">
                                    <svg class="h-4 w-4 shrink-0 text-emerald-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z" />
                                    </svg>
                                    <span class="text-sm text-slate-700">{{ $display }}</span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>

        <form method="post" action="{{ route('student.feedback.submit') }}" class="mt-12 flex justify-center">
            @csrf
            <button type="submit" class="group inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 px-10 py-4 text-sm font-semibold text-white shadow-lg shadow-emerald-400/30 transition hover:from-emerald-500 hover:to-teal-500 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ __('student.submit_final') }}
            </button>
        </form>
    </div>
@endsection
