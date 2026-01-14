@extends('errors::layout')

@section('title', '404 - ' . __('public.errors.404.title'))
@section('code', '404')
@section('error-title', __('public.errors.404.title'))
@section('error-message', __('public.errors.404.text'))

@section('error-icon')
<div class="inline-flex h-24 w-24 items-center justify-center rounded-full bg-brand-100 text-brand-600">
    <svg class="h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
</div>
@endsection
