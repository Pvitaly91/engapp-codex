@props(['caption' => null])
<div {{ $attributes->class(['w-full']) }}>
    <p class="mb-1.5 text-xs font-semibold text-muted-foreground flex items-center gap-2">
        <span class="inline-block h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
        {{ $caption ?? __('frontend.tests.compose.token_bank') }}
    </p>
    <div class="flex flex-wrap items-center gap-1.5">{{ $slot }}</div>
</div>
