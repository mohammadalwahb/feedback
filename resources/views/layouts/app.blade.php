<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ in_array(app()->getLocale(), ['ar', 'ku'], true) ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', __('messages.app_title'))</title>
    {{-- Browser tab icon: place file at public/images/logo.png --}}
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.dataTables.min.css">
    @stack('head')
</head>
@php
    $isAdminLayout = auth()->check() && auth()->user()->isAdmin();
@endphp
<body class="min-h-screen text-slate-900 antialiased {{ $isAdminLayout ? 'bg-gradient-to-b from-slate-50 via-white to-violet-50/40' : 'bg-slate-50' }} @stack('body_class')">
    <header class="{{ $isAdminLayout ? 'border-b border-indigo-100/80 bg-white/85 shadow-md shadow-indigo-100/20 backdrop-blur-md' : 'border-b border-slate-200 bg-white shadow-sm' }}">
        <div @class([
            'mx-auto flex flex-wrap items-center justify-between gap-4 px-4 py-3',
            'w-full' => $isAdminLayout,
            'max-w-7xl' => ! $isAdminLayout,
        ])>
            <a href="{{ auth()->check() ? ($isAdminLayout ? route('admin.dashboard') : route('student.dashboard')) : route('login') }}" @class([
                'inline-flex items-center gap-2 text-lg font-bold tracking-tight',
                'bg-gradient-to-r from-violet-700 to-indigo-600 bg-clip-text text-transparent' => $isAdminLayout,
                'font-semibold text-indigo-700' => ! $isAdminLayout,
            ])>
                @if($isAdminLayout)
                    <img src="{{ asset('images/logo.png') }}" alt="" width="36" height="36" class="h-9 w-9 shrink-0 object-contain" decoding="async">
                @endif
                {{ __('messages.app_title') }}
            </a>
            <div class="flex flex-wrap items-center gap-3 text-sm">
                @if($isAdminLayout)
                    <button type="button" id="adminSidebarOpen" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border-2 border-slate-200 bg-white text-slate-700 shadow-sm transition hover:border-indigo-200 hover:bg-indigo-50 lg:hidden" aria-label="Open admin menu">
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 5.25h16.5M3.75 12h16.5m-16.5 6.75h16.5" />
                        </svg>
                    </button>
                @endif
                <div @class([
                    'flex gap-1 rounded-xl border p-1',
                    'border-indigo-100 bg-indigo-50/60' => $isAdminLayout,
                    'border-slate-200 bg-slate-50' => ! $isAdminLayout,
                ])>
                    <a href="{{ route('locale.switch', 'en') }}" @class([
                        'rounded-lg px-2.5 py-1 text-xs font-semibold',
                        'bg-white text-indigo-900 shadow-sm' => app()->getLocale() === 'en',
                        'text-slate-600 hover:bg-white/70' => app()->getLocale() !== 'en',
                    ])>EN</a>
                    <a href="{{ route('locale.switch', 'ku') }}" @class([
                        'rounded-lg px-2.5 py-1 text-xs font-semibold',
                        'bg-white text-indigo-900 shadow-sm' => app()->getLocale() === 'ku',
                        'text-slate-600 hover:bg-white/70' => app()->getLocale() !== 'ku',
                    ])>KU</a>
                    <a href="{{ route('locale.switch', 'ar') }}" @class([
                        'rounded-lg px-2.5 py-1 text-xs font-semibold',
                        'bg-white text-indigo-900 shadow-sm' => app()->getLocale() === 'ar',
                        'text-slate-600 hover:bg-white/70' => app()->getLocale() !== 'ar',
                    ])>AR</a>
                </div>
                @auth
                    <form method="post" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" @class([
                            'inline-flex items-center gap-1.5 rounded-xl px-4 py-2 text-sm font-semibold transition',
                            'border-2 border-slate-200 bg-white text-slate-700 shadow-sm hover:border-rose-200 hover:bg-rose-50/80' => $isAdminLayout,
                            'bg-slate-800 text-white hover:bg-slate-900' => ! $isAdminLayout,
                        ])>
                            @if($isAdminLayout)
                                <svg class="h-4 w-4 opacity-70" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" /></svg>
                            @endif
                            {{ __('nav.logout') }}
                        </button>
                    </form>
                @endauth
            </div>
        </div>
        @auth
            @unless($isAdminLayout)
                <nav class="border-t border-indigo-100/80 bg-gradient-to-r from-indigo-50/90 via-white to-violet-50/80">
                    <div class="mx-auto flex max-w-7xl flex-wrap gap-1 px-4 py-2.5 text-sm">
                        <a class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 font-medium text-indigo-800 hover:bg-white/80 hover:shadow-sm" href="{{ route('student.dashboard') }}">
                            <svg class="h-4 w-4 opacity-70" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" /></svg>
                            {{ __('nav.dashboard') }}
                        </a>
                        <a class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 font-medium text-indigo-800 hover:bg-white/80 hover:shadow-sm" href="{{ route('student.feedback.index') }}">
                            <svg class="h-4 w-4 opacity-70" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                            {{ __('nav.feedback_forms') }}
                        </a>
                    </div>
                </nav>
            @endunless
        @endauth
    </header>

    @if($isAdminLayout)
        <div id="adminSidebarOverlay" class="fixed inset-0 z-40 hidden bg-slate-900/40 lg:hidden"></div>
        <div @class([
            'mx-auto flex lg:gap-6 lg:px-4',
            'w-full' => $isAdminLayout,
            'max-w-7xl' => ! $isAdminLayout,
        ])>
            <aside id="adminSidebar" class="fixed inset-y-0 start-0 z-50 w-72 -translate-x-full overflow-y-auto border-e border-indigo-100/80 bg-gradient-to-b from-white via-indigo-50/35 to-violet-50/40 p-4 shadow-2xl shadow-indigo-200/40 transition-transform duration-200 ease-out lg:static lg:z-0 lg:block lg:w-72 lg:translate-x-0 lg:rounded-3xl lg:border lg:shadow-xl lg:shadow-indigo-100/30">
                @include('layouts.nav-admin')
            </aside>
            <main class="min-w-0 flex-1 px-4 py-8 lg:px-0">
    @else
            <main class="mx-auto max-w-7xl px-4 py-8">
    @endif
        @if(session('ok'))
            <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-900">{{ session('ok') }}</div>
        @endif
        @if(session('error'))
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-900">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-900">
                <ul class="list-inside list-disc text-sm">
                    @foreach($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @yield('content')
    </main>
    @if($isAdminLayout)
        </div>
    @endif
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
    @if($isAdminLayout)
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const openBtn = document.getElementById('adminSidebarOpen');
                const sidebar = document.getElementById('adminSidebar');
                const overlay = document.getElementById('adminSidebarOverlay');
                const closeBtn = document.getElementById('adminSidebarClose');
                if (!openBtn || !sidebar || !overlay) return;

                const openSidebar = () => {
                    sidebar.classList.remove('-translate-x-full');
                    overlay.classList.remove('hidden');
                    document.body.classList.add('overflow-hidden');
                };
                const closeSidebar = () => {
                    sidebar.classList.add('-translate-x-full');
                    overlay.classList.add('hidden');
                    document.body.classList.remove('overflow-hidden');
                };

                openBtn.addEventListener('click', openSidebar);
                closeBtn?.addEventListener('click', closeSidebar);
                overlay.addEventListener('click', closeSidebar);
                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape') closeSidebar();
                });
                sidebar.querySelectorAll('a').forEach((link) => {
                    link.addEventListener('click', () => {
                        if (window.innerWidth < 1024) closeSidebar();
                    });
                });
            });
        </script>
    @endif
    @stack('scripts')
</body>
</html>
