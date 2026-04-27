<nav class="h-full">
    <div class="mb-3 flex items-center justify-between lg:hidden">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('nav.reports') }} & {{ __('nav.feedback_forms') }}</p>
        <button type="button" id="adminSidebarClose" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 shadow-sm hover:bg-slate-50" aria-label="Close admin menu">
            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
    <div class="mb-4 hidden items-center gap-2 rounded-2xl border border-indigo-100 bg-white/80 p-3 lg:flex">
        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-violet-500 to-indigo-600 text-white shadow-md shadow-indigo-200/40">
            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </span>
        <div>
            <p class="text-sm font-semibold text-slate-900">{{ __('nav.dashboard') }}</p>
            <p class="text-xs text-slate-500">{{ __('messages.app_title') }}</p>
        </div>
    </div>
    <div class="flex flex-col gap-1 text-sm">
        <a @class([
            'inline-flex items-center gap-2 rounded-xl px-3 py-2 font-medium transition',
            'bg-indigo-100 text-indigo-900 shadow-sm' => request()->routeIs('admin.dashboard'),
            'text-indigo-900 hover:bg-white/80 hover:shadow-sm' => ! request()->routeIs('admin.dashboard'),
        ]) href="{{ route('admin.dashboard') }}">
            <svg class="h-4 w-4 shrink-0 text-violet-600 opacity-80" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" /></svg>
            {{ __('nav.dashboard') }}
        </a>
        <a @class([
            'inline-flex items-center gap-2 rounded-xl px-3 py-2 font-medium transition',
            'bg-indigo-100 text-indigo-900 shadow-sm' => request()->routeIs('admin.colleges.*'),
            'text-indigo-900 hover:bg-white/80 hover:shadow-sm' => ! request()->routeIs('admin.colleges.*'),
        ]) href="{{ route('admin.colleges.index') }}">
            <svg class="h-4 w-4 shrink-0 text-violet-600 opacity-80" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6v9.75a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18.75V9z" /></svg>
            {{ __('nav.colleges') }}
        </a>
        <a @class([
            'inline-flex items-center gap-2 rounded-xl px-3 py-2 font-medium transition',
            'bg-indigo-100 text-indigo-900 shadow-sm' => request()->routeIs('admin.departments.*'),
            'text-indigo-900 hover:bg-white/80 hover:shadow-sm' => ! request()->routeIs('admin.departments.*'),
        ]) href="{{ route('admin.departments.index') }}">
            <svg class="h-4 w-4 shrink-0 text-violet-600 opacity-80" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 6.878V6a2.25 2.25 0 012.25-2.25h9.568L18 6.878m0 0L6.22 21.75m11.78 0L18 6.878m0 0h-9.568L6.22 21.75M6.22 21.75H3.75a.75.75 0 01-.75-.75V8.25m3.22 13.5L6 6.878" /></svg>
            {{ __('nav.departments') }}
        </a>
        <a @class([
            'inline-flex items-center gap-2 rounded-xl px-3 py-2 font-medium transition',
            'bg-indigo-100 text-indigo-900 shadow-sm' => request()->routeIs('admin.semesters.*'),
            'text-indigo-900 hover:bg-white/80 hover:shadow-sm' => ! request()->routeIs('admin.semesters.*'),
        ]) href="{{ route('admin.semesters.index') }}">
            <svg class="h-4 w-4 shrink-0 text-violet-600 opacity-80" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" /></svg>
            {{ __('nav.semesters') }}
        </a>
        <a @class([
            'inline-flex items-center gap-2 rounded-xl px-3 py-2 font-medium transition',
            'bg-indigo-100 text-indigo-900 shadow-sm' => request()->routeIs('admin.students.*'),
            'text-indigo-900 hover:bg-white/80 hover:shadow-sm' => ! request()->routeIs('admin.students.*'),
        ]) href="{{ route('admin.students.index') }}">
            <svg class="h-4 w-4 shrink-0 text-violet-600 opacity-80" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.813-2.387M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>
            {{ __('nav.students') }}
        </a>
        <a @class([
            'inline-flex items-center gap-2 rounded-xl px-3 py-2 font-medium transition',
            'bg-indigo-100 text-indigo-900 shadow-sm' => request()->routeIs('admin.staff.*'),
            'text-indigo-900 hover:bg-white/80 hover:shadow-sm' => ! request()->routeIs('admin.staff.*'),
        ]) href="{{ route('admin.staff.index') }}">
            <svg class="h-4 w-4 shrink-0 text-violet-600 opacity-80" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v15.128A9.056 9.056 0 016 18c1.052 0 2.062.18 3 .512m0-15.042A8.967 8.967 0 0118 3.75c1.052 0 2.062.18 3 .512v15.128a9.056 9.056 0 01-3 .512m-6-15.042v15.042" /></svg>
            {{ __('nav.staff') }}
        </a>
        <a @class([
            'inline-flex items-center gap-2 rounded-xl px-3 py-2 font-medium transition',
            'bg-indigo-100 text-indigo-900 shadow-sm' => request()->routeIs('admin.admins.*'),
            'text-indigo-900 hover:bg-white/80 hover:shadow-sm' => ! request()->routeIs('admin.admins.*'),
        ]) href="{{ route('admin.admins.index') }}">
            <svg class="h-4 w-4 shrink-0 text-violet-600 opacity-80" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" /></svg>
            {{ __('nav.admins') }}
        </a>
        <a @class([
            'inline-flex items-center gap-2 rounded-xl px-3 py-2 font-medium transition',
            'bg-indigo-100 text-indigo-900 shadow-sm' => request()->routeIs('admin.import.students*'),
            'text-indigo-900 hover:bg-white/80 hover:shadow-sm' => ! request()->routeIs('admin.import.students*'),
        ]) href="{{ route('admin.import.students') }}">
            <svg class="h-4 w-4 shrink-0 text-violet-600 opacity-80" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" /></svg>
            {{ __('nav.import_students') }}
        </a>
        <a @class([
            'inline-flex items-center gap-2 rounded-xl px-3 py-2 font-medium transition',
            'bg-indigo-100 text-indigo-900 shadow-sm' => request()->routeIs('admin.import.staff*'),
            'text-indigo-900 hover:bg-white/80 hover:shadow-sm' => ! request()->routeIs('admin.import.staff*'),
        ]) href="{{ route('admin.import.staff') }}">
            <svg class="h-4 w-4 shrink-0 text-violet-600 opacity-80" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
            {{ __('nav.import_staff') }}
        </a>
        <a @class([
            'inline-flex items-center gap-2 rounded-xl px-3 py-2 font-medium transition',
            'bg-indigo-100 text-indigo-900 shadow-sm' => request()->routeIs('admin.feedback.forms.*'),
            'text-indigo-900 hover:bg-white/80 hover:shadow-sm' => ! request()->routeIs('admin.feedback.forms.*'),
        ]) href="{{ route('admin.feedback.forms.index') }}">
            <svg class="h-4 w-4 shrink-0 text-violet-600 opacity-80" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
            {{ __('nav.feedback_forms') }}
        </a>
        <a @class([
            'inline-flex items-center gap-2 rounded-xl px-3 py-2 font-medium transition',
            'bg-indigo-100 text-indigo-900 shadow-sm' => request()->routeIs('admin.reports.participation'),
            'text-indigo-900 hover:bg-white/80 hover:shadow-sm' => ! request()->routeIs('admin.reports.participation'),
        ]) href="{{ route('admin.reports.participation') }}">
            <svg class="h-4 w-4 shrink-0 text-violet-600 opacity-80" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" /></svg>
            {{ __('nav.reports') }}
        </a>
        <a @class([
            'inline-flex items-center gap-2 rounded-xl px-3 py-2 font-medium transition',
            'bg-indigo-100 text-indigo-900 shadow-sm' => request()->routeIs('admin.reports.results*'),
            'text-indigo-900 hover:bg-white/80 hover:shadow-sm' => ! request()->routeIs('admin.reports.results*'),
        ]) href="{{ route('admin.reports.results') }}">
            <svg class="h-4 w-4 shrink-0 text-violet-600 opacity-80" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h18m-18 3.75h18m-18 3.75h18M4.5 19.5h15a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H4.5A1.5 1.5 0 003 6v12a1.5 1.5 0 001.5 1.5z" /></svg>
            {{ __('nav.results') }}
        </a>
        <a @class([
            'inline-flex items-center gap-2 rounded-xl px-3 py-2 font-medium transition',
            'bg-indigo-100 text-indigo-900 shadow-sm' => request()->routeIs('admin.audit.*'),
            'text-indigo-900 hover:bg-white/80 hover:shadow-sm' => ! request()->routeIs('admin.audit.*'),
        ]) href="{{ route('admin.audit.index') }}">
            <svg class="h-4 w-4 shrink-0 text-violet-600 opacity-80" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            {{ __('nav.audit') }}
        </a>
    </div>
</nav>
