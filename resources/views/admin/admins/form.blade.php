@extends('layouts.app')
@section('content')
    <h1 class="mb-6 text-2xl font-bold tracking-tight text-slate-900">{{ __('nav.admins') }}</h1>
    <p class="mb-4 flex items-start gap-2 rounded-2xl border border-amber-100 bg-amber-50/80 px-4 py-3 text-sm text-amber-900">
        <svg class="mt-0.5 h-5 w-5 shrink-0 text-amber-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg>
        @uoz.edu.krd only (not @stud).
    </p>
    <form method="post" action="{{ $item->exists ? route('admin.admins.update', $item) : route('admin.admins.store') }}" class="admin-form-panel">
        @csrf @if($item->exists) @method('PUT') @endif
        <div><label class="block text-sm font-medium text-slate-700">{{ __('fields.email') }}</label><input type="email" name="email" value="{{ old('email', $item->email) }}" required class="admin-input"></div>
        <div><label class="block text-sm font-medium text-slate-700">{{ __('fields.english_name') }}</label><input name="english_name" value="{{ old('english_name', $item->english_name) }}" required class="admin-input"></div>
        <div><label class="block text-sm font-medium text-slate-700">{{ __('fields.name_ku') }}</label><input name="kurdish_name" value="{{ old('kurdish_name', $item->kurdish_name) }}" class="admin-input"></div>
        <div><label class="block text-sm font-medium text-slate-700">{{ __('fields.name_ar') }}</label><input name="arabic_name" value="{{ old('arabic_name', $item->arabic_name) }}" class="admin-input"></div>
        <button type="submit" class="admin-btn-primary">{{ __('admin.save') }}</button>
    </form>
@endsection
