@extends('layouts.app')

@section('title', __('nav.dashboard'))

@section('content')
    <div class="mb-8 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <h1 class="flex items-center gap-3 text-2xl font-bold tracking-tight text-slate-900 md:text-3xl">
                <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-violet-500 to-indigo-600 text-white shadow-lg shadow-indigo-300/40">
                    <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" /></svg>
                </span>
                {{ __('nav.dashboard') }}
            </h1>
            <p class="mt-2 text-sm text-slate-600">{{ __('reports.participation_title') }}</p>
        </div>
    </div>

    <div class="grid gap-6 md:grid-cols-3">
        <div class="admin-stat-tile">
            <p class="flex items-center gap-2 text-sm font-medium text-slate-500">
                <svg class="h-5 w-5 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.813-2.387M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>
                {{ __('reports.eligible_students') }}
            </p>
            <p class="mt-3 text-3xl font-bold tabular-nums text-indigo-700">{{ $participation['eligible'] }}</p>
        </div>
        <div class="admin-stat-tile">
            <p class="flex items-center gap-2 text-sm font-medium text-slate-500">
                <svg class="h-5 w-5 text-emerald-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                {{ __('reports.submitted_students') }}
            </p>
            <p class="mt-3 text-3xl font-bold tabular-nums text-emerald-700">{{ $participation['submitted'] }}</p>
        </div>
        <div class="admin-stat-tile">
            <p class="flex items-center gap-2 text-sm font-medium text-slate-500">
                <svg class="h-5 w-5 text-amber-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" /></svg>
                {{ __('reports.participation_ratio') }}
            </p>
            <p class="mt-3 text-3xl font-bold tabular-nums text-amber-700">{{ number_format($participation['ratio'] * 100, 1) }}%</p>
        </div>
    </div>

    <div class="mt-10 flex flex-wrap items-center gap-4">
        <a href="{{ route('admin.reports.participation') }}" class="admin-btn-primary">
            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
            {{ __('reports.detail_filters') }}
        </a>
    </div>
@endsection
