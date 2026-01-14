@extends('errors::layout')

@section('title', '429 - ' . __('public.errors.429.title'))
@section('code', '429')
@section('error-title', __('public.errors.429.title'))
@section('error-message', __('public.errors.429.text'))

@section('error-icon')
<div class="inline-flex h-24 w-24 items-center justify-center rounded-full bg-orange-100 text-orange-600 dark:bg-orange-900/30 dark:text-orange-400">
    <svg class="h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
    </svg>
</div>
@endsection
