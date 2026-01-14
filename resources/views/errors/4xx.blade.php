@extends('errors::layout')

@section('title', $exception->getStatusCode() . ' - ' . __('public.errors.4xx.title'))
@section('code', $exception->getStatusCode())
@section('error-title', __('public.errors.4xx.title'))
@section('error-message', __('public.errors.4xx.text'))

@section('error-icon')
<div class="inline-flex h-24 w-24 items-center justify-center rounded-full bg-brand-100 text-brand-600">
    <svg class="h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
    </svg>
</div>
@endsection
