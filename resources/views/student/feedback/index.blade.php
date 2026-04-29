@extends('layouts.app')
@push('body_class')
    bg-gradient-to-b from-slate-50 via-white to-indigo-50/50
@endpush
@section('title', __('nav.feedback_forms'))
@section('content')
    <div class="mx-auto max-w-3xl px-3 pb-16 pt-6 md:px-4 md:pt-10">
        <div class="mb-8 text-center">
            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-violet-500 to-indigo-600 text-white shadow-lg shadow-indigo-300/40">
                <svg class="h-8 w-8" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                </svg>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 md:text-3xl">{{ __('nav.feedback_forms') }}</h1>
        </div>

        @if(!$version)
            <div class="mx-auto max-w-lg rounded-2xl border border-amber-200 bg-amber-50/90 p-6 text-center shadow-sm">
                <p class="flex items-center justify-center gap-2 text-amber-900">
                    <svg class="h-5 w-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                    </svg>
                    {{ __('feedback.closed') }}
                </p>
            </div>
        @else
            @php
                $form = $version->form;
                $titles = collect([$form?->title_en, $form?->title_ku, $form?->title_ar])->filter(fn ($x) => filled($x))->unique()->values();
                $descriptions = collect([$form?->description_en, $form?->description_ku, $form?->description_ar])->filter(fn ($x) => filled($x))->unique()->values();
            @endphp
            <div class="mx-auto mb-8 w-full max-w-2xl rounded-2xl border border-slate-200 bg-white/95 p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('nav.feedback_forms') }}</h2>
                <div class="mt-3 space-y-1 text-sm text-slate-700">
                    @foreach($titles as $title)
                        <p>{{ $title }}</p>
                    @endforeach
                </div>
                @if($descriptions->isNotEmpty())
                    <div class="mt-4 border-t border-slate-100 pt-3 space-y-1 text-sm text-slate-600">
                        @foreach($descriptions as $desc)
                            <p>{{ $desc }}</p>
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="mx-auto mb-8 flex max-w-xl flex-col items-center gap-2 rounded-2xl border border-indigo-100 bg-indigo-50/80 px-5 py-4 text-center shadow-sm">
                <p class="flex items-center gap-2 text-sm font-medium text-indigo-900">
                    <svg class="h-5 w-5 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                    </svg>
                    {{ __('student.progress', ['done' => $progress['completed'], 'total' => $progress['total']]) }}
                </p>
            </div>

            @if($assigned->isEmpty())
                <div class="mx-auto max-w-lg rounded-2xl border border-amber-200 bg-amber-50/90 p-6 text-center">
                    <p class="flex flex-col items-center gap-2 text-sm text-amber-900">
                        <svg class="h-8 w-8 text-amber-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                        {{ __('student.no_staff_in_context') }}
                    </p>
                </div>
            @else
                <form method="post" action="{{ route('student.feedback.start') }}" class="mx-auto max-w-2xl space-y-6">
                    @csrf
                    <p class="flex items-start justify-center gap-2 text-center text-sm leading-relaxed text-slate-600">
                        <svg class="mt-0.5 h-5 w-5 shrink-0 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                        </svg>
                        <span class="max-w-md">{{ __('student.select_staff') }}</span>
                    </p>
                    <div class="grid gap-4 sm:grid-cols-2">
                        @foreach($assigned as $st)
                            <label class="group relative block cursor-pointer">
                                <input type="checkbox" name="staff_subject_ids[]" value="{{ $st->id }}" class="sr-only">
                                <div class="flex h-full flex-col items-center gap-3 rounded-2xl border-2 border-slate-200 bg-white/90 p-5 text-center shadow-sm transition-all duration-200 hover:border-indigo-300 hover:shadow-md group-has-[:checked]:border-indigo-600 group-has-[:checked]:bg-indigo-50/80 group-has-[:checked]:shadow-lg group-has-[:checked]:shadow-indigo-200/40">
                                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-500 transition-colors group-has-[:checked]:bg-indigo-600 group-has-[:checked]:text-white">
                                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.813-2.387M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                                        </svg>
                                    </span>
                                    <span class="text-sm font-semibold text-slate-900">{{ $st->instructor_name }}</span>
                                    <span class="flex items-center gap-1.5 text-xs text-slate-500">
                                        <svg class="h-3.5 w-3.5 text-indigo-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v15.128A9.056 9.056 0 016 18c1.052 0 2.062.18 3 .512m0-15.042A8.967 8.967 0 0118 3.75c1.052 0 2.062.18 3 .512v15.128a9.056 9.056 0 01-3 .512m-6-15.042v15.042" />
                                        </svg>
                                        {{ $st->subject_name }}
                                    </span>
                                    <span class="mt-1 inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-0.5 text-[10px] font-medium uppercase tracking-wide text-slate-500 opacity-0 transition-opacity group-has-[:checked]:opacity-100 group-has-[:checked]:bg-indigo-200 group-has-[:checked]:text-indigo-900">
                                        <svg class="h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                        </svg>
                                        {{ __('student.selected') }}
                                    </span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                    <div class="flex justify-center pt-4">
                        <button type="submit" class="group inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-violet-600 to-indigo-600 px-8 py-3.5 text-sm font-semibold text-white shadow-lg shadow-indigo-400/30 transition hover:from-violet-500 hover:to-indigo-500 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z" />
                            </svg>
                            {{ __('student.begin') }}
                        </button>
                    </div>
                </form>
            @endif
        @endif
    </div>
@endsection
