@props([
    'variant' => 'horizontal',
    'size' => 'h-11 w-auto',
])

@php
    $isCompact = $variant === 'compact';
@endphp

@if($isCompact)
    <span {{ $attributes->class('inline-flex items-center justify-center rounded-2xl bg-gradient-to-br from-brand-600 via-indigo-500 to-sky-400 text-white shadow-card') }}>
        <svg class="h-10 w-10" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect x="6" y="6" width="52" height="52" rx="14" fill="url(#grad)"/>
            <path d="M20 40c4 4 10 4 14 0l10-10" stroke="white" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M24 26l6 6 12-12" stroke="white" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
            <defs>
                <linearGradient id="grad" x1="12" y1="12" x2="52" y2="52" gradientUnits="userSpaceOnUse">
                    <stop stop-color="#5a6bff"/>
                    <stop offset="1" stop-color="#5cd6ff"/>
                </linearGradient>
            </defs>
        </svg>
    </span>
@else
    <span {{ $attributes->class('inline-flex items-center gap-3 '.$size) }}>
        <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-600 via-indigo-500 to-sky-400 text-white shadow-card">
            <svg class="h-7 w-7" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect x="6" y="6" width="52" height="52" rx="14" fill="url(#grad)"/>
                <path d="M20 40c4 4 10 4 14 0l10-10" stroke="white" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M24 26l6 6 12-12" stroke="white" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
                <defs>
                    <linearGradient id="grad" x1="12" y1="12" x2="52" y2="52" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#5a6bff"/>
                        <stop offset="1" stop-color="#5cd6ff"/>
                    </linearGradient>
                </defs>
            </svg>
        </span>
        <span class="flex flex-col">
            <span class="text-xl font-extrabold tracking-tight">Gramlyze</span>
            <span class="text-[0.7rem] uppercase tracking-[0.35em] text-brand-600">Grammar Studio</span>
        </span>
    </span>
@endif
