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
            <path d="M5.5 8C5.5 5.23858 7.73858 3 10.5 3H13.25C16.1495 3 18.5 5.35051 18.5 8.25C18.5 11.1495 16.1495 13.5 13.25 13.5H11.5C9.567 13.5 8 15.067 8 17C8 18.933 9.567 20.5 11.5 20.5H16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            <path d="M5 20.5H12.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
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
                <path d="M5.5 7.75C5.5 5.12665 7.62665 3 10.25 3H13.25C16.1495 3 18.5 5.35051 18.5 8.25C18.5 11.1495 16.1495 13.5 13.25 13.5H11.75C10.2312 13.5 9 14.7312 9 16.25C9 17.7688 10.2312 19 11.75 19H15.75" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M5 19H12" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
            </svg>
        </span>
        <span class="flex flex-col">
            <span class="text-lg font-semibold tracking-tight">Gramlyze</span>
            <span class="text-[0.65rem] font-medium uppercase tracking-[0.45em] text-muted-foreground">{{ $taglineText }}</span>
        </span>
    </span>
@endif
