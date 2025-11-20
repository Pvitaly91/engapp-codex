@props([
    'variant' => 'full',
    'size' => 'h-10 w-10',
])

@if ($variant === 'icon')
    {{-- Minimalist geometric logo --}}
    <span {{ $attributes->class("inline-flex {$size} items-center justify-center rounded-lg bg-gradient-to-tr from-emerald-500 via-teal-600 to-cyan-600 text-white shadow-md") }} role="img" aria-label="Gramlyze Logo">
        <svg class="h-6 w-6" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <path d="M8 24V8L16 12L24 8V24L16 20L8 24Z" fill="currentColor" opacity="0.9"/>
            <circle cx="16" cy="16" r="3" fill="currentColor"/>
        </svg>
    </span>
@else
    {{-- Full logo with text --}}
    <span {{ $attributes->class('inline-flex items-center gap-2.5') }} role="img" aria-label="Gramlyze - English Platform">
        <span class="inline-flex {{ $size }} items-center justify-center rounded-lg bg-gradient-to-tr from-emerald-500 via-teal-600 to-cyan-600 text-white shadow-md">
            <svg class="h-6 w-6" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M8 24V8L16 12L24 8V24L16 20L8 24Z" fill="currentColor" opacity="0.9"/>
                <circle cx="16" cy="16" r="3" fill="currentColor"/>
            </svg>
        </span>
        <span class="flex flex-col" aria-hidden="true">
            <span class="text-xl font-bold tracking-tight text-gray-900 dark:text-white">Gramlyze</span>
            <span class="text-[0.6rem] font-medium uppercase tracking-widest text-gray-500 dark:text-gray-400">English Platform</span>
        </span>
    </span>
@endif
