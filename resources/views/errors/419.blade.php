@extends('errors::layout')

@section('title', '419 - ' . __('public.errors.419.title'))
@section('code', '419')
@section('error-title', __('public.errors.419.title'))
@section('error-message', __('public.errors.419.text'))

@section('error-icon')
<div class="inline-flex h-24 w-24 items-center justify-center rounded-full bg-yellow-100 text-yellow-600 dark:bg-yellow-900/30 dark:text-yellow-400">
    <svg class="h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
</div>
@endsection
