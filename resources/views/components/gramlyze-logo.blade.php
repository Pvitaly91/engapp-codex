@props([
    'variant' => 'stacked',
    'tagline' => 'Language OS',
    'size' => 'h-11 w-11',
])

@php
    $taglineText = strtoupper($tagline);
@endphp

@if ($variant === 'compact')
    <span {{ $attributes->class("inline-flex {$size} items-center justify-center rounded-2xl bg-gradient-to-br from-primary via-secondary to-accent text-white shadow-soft") }}>
        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M4 8.5C4 5.46243 6.46243 3 9.5 3H13C16.3137 3 19 5.68629 19 9C19 12.3137 16.3137 15 13 15H10.5C8.567 15 7 16.567 7 18.5C7 20.433 8.567 22 10.5 22H16" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" />
            <path d="M4 21.5H11.5" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" />
            <circle cx="13.5" cy="9" r="1.5" fill="currentColor" />
            <circle cx="10.5" cy="18.5" r="1.5" fill="currentColor" />
        </svg>
    </span>
@elseif ($variant === 'badge')
    <span {{ $attributes->class('inline-flex items-center gap-3 rounded-full bg-gradient-to-r from-primary via-secondary to-accent px-4 py-1.5 text-[0.6rem] font-semibold uppercase tracking-[0.5em] text-white shadow-soft') }}>
        GLZ
        <span class="rounded-full bg-white/20 px-2 py-0.5 text-[0.55rem] tracking-[0.35em]">AI</span>
    </span>
@else
    <span {{ $attributes->class('inline-flex items-center gap-3') }}>
        <span class="relative inline-flex {{ $size }} items-center justify-center overflow-hidden rounded-2xl bg-gradient-to-br from-primary via-secondary to-accent text-white shadow-soft">
            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M4 8.5C4 5.46243 6.46243 3 9.5 3H13C16.3137 3 19 5.68629 19 9C19 12.3137 16.3137 15 13 15H10.5C8.567 15 7 16.567 7 18.5C7 20.433 8.567 22 10.5 22H16" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M4 21.5H11.5" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" />
                <circle cx="13.5" cy="9" r="1.5" fill="currentColor" />
                <circle cx="10.5" cy="18.5" r="1.5" fill="currentColor" />
            </svg>
        </span>
        <span class="flex flex-col">
            <span class="text-lg font-semibold tracking-tight">Gramlyze</span>
            <span class="text-[0.65rem] font-medium uppercase tracking-[0.45em] text-muted-foreground">{{ $taglineText }}</span>
        </span>
    </span>
@endif
