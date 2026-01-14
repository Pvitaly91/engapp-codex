@extends('errors::layout')

@section('title', $exception->getStatusCode() . ' - ' . __('public.errors.5xx.title'))
@section('code', $exception->getStatusCode())
@section('error-title', __('public.errors.5xx.title'))
@section('error-message', __('public.errors.5xx.text'))

@section('error-icon')
<div class="inline-flex h-24 w-24 items-center justify-center rounded-full bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400">
    <svg class="h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
    </svg>
</div>
@endsection
