@extends('layouts.app')

@section('title', __('nav.login_google'))

@section('content')
    <div class="mx-auto max-w-md rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
        <h1 class="mb-2 text-xl font-semibold">{{ __('messages.app_title') }}</h1>
        <p class="mb-6 text-sm text-slate-600">{{ __('nav.login_google') }}</p>
        @if(session('error'))
            <p class="mb-4 text-sm text-red-600">{{ session('error') }}</p>
        @endif
        <a href="{{ route('auth.google') }}" class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-3 font-medium shadow-sm hover:bg-slate-50">
            <span>{{ __('nav.login_google') }}</span>
        </a>
    </div>
@endsection
