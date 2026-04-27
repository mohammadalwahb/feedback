@extends('layouts.app')
@section('title', __('feedback.preview'))
@section('content')
    <h1 class="mb-2 text-2xl font-bold tracking-tight text-slate-900">{{ __('feedback.preview') }}</h1>
    <p class="mb-6 text-sm text-slate-600">{{ $form->title_en }} — v{{ $version->version_number }}</p>
    <ol class="admin-card list-decimal space-y-6 p-6 pl-10 md:p-8 md:pl-12">
        @foreach($questions as $q)
            <li>
                <p class="font-medium">{{ $q->localizedLabel() }} @if($q->is_required)<span class="text-red-600">*</span>@endif</p>
                <p class="text-xs text-slate-500">{{ $q->type->value }}</p>
            </li>
        @endforeach
    </ol>
@endsection
