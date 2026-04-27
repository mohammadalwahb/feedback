@extends('layouts.app')
@section('title', __('nav.import_students'))
@section('content')
    <h1 class="mb-4 text-2xl font-bold tracking-tight text-slate-900">{{ __('nav.import_students') }}</h1>
    <p class="mb-4 max-w-2xl text-sm text-slate-600">{{ __('imports.template_hint') }}</p>
    <a href="{{ route('admin.import.students.template') }}" class="admin-btn-secondary mb-6">
        {{ __('imports.download_template') }} ({{ $excelSupported ? '.xlsx' : '.csv' }})
    </a>
    <p class="mb-4 max-w-2xl text-sm text-slate-600">{{ __('imports.students_help') }}</p>
    @if(session('import_result'))
        @php $r = session('import_result'); @endphp
        <div class="admin-card mb-4 p-4 text-sm">
            <p>{{ __('imports.created') }}: {{ $r['created'] }}, {{ __('imports.updated') }}: {{ $r['updated'] ?? 0 }}, {{ __('imports.skipped') }}: {{ $r['skipped'] }}</p>
            @if(count($r['errors']))
                <p class="mt-2 font-medium">{{ __('imports.errors') }}</p>
                <ul class="mt-1 max-h-48 list-inside list-disc overflow-y-auto text-red-700">
                    @foreach($r['errors'] as $err)
                        <li>Row {{ $err['row'] }}: {{ implode('; ', $err['messages']) }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
    @endif
    <form method="post" action="{{ route('admin.import.students.run') }}" enctype="multipart/form-data" class="admin-form-panel max-w-md">
        @csrf
        <input type="file" name="file" accept="{{ $excelSupported ? '.xlsx,.xls,.csv' : '.csv,.txt' }}" required class="w-full rounded-xl border-2 border-dashed border-slate-200 bg-slate-50/50 px-3 py-4 text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-100 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-indigo-800 hover:file:bg-indigo-200">
        <button type="submit" class="admin-btn-primary">{{ __('imports.upload') }}</button>
    </form>
@endsection
