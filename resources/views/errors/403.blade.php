@extends('errors::layout')

@section('title', '403 - ' . __('public.errors.403.title'))
@section('code', '403')
@section('error-title', __('public.errors.403.title'))
@section('error-message', __('public.errors.403.text'))

@section('error-icon')
<div class="inline-flex h-24 w-24 items-center justify-center rounded-full bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400">
    <svg class="h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
    </svg>
</div>
@endsection
