@props(['block', 'collapsed' => true])

@php
    $tags = $block->tags ?? collect();
@endphp

@if($tags->isNotEmpty())
    <div 
        class="mt-3 pt-3 border-t border-border/30"
        x-data="{ expanded: {{ $collapsed ? 'false' : 'true' }} }"
    >
        <button 
            @click="expanded = !expanded"
            class="flex items-center gap-2 text-xs text-muted-foreground hover:text-foreground transition-colors"
        >
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
            </svg>
            <span>Теги ({{ $tags->count() }})</span>
            <svg 
                class="h-3 w-3 transition-transform" 
                :class="{ 'rotate-180': expanded }"
                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
            >
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>
        <div x-show="expanded" x-collapse class="mt-2">
            <div class="flex flex-wrap gap-1.5">
                @foreach($tags as $tag)
                    <span class="inline-flex items-center rounded-md bg-muted/60 px-2 py-0.5 text-[10px] text-muted-foreground">
                        {{ $tag->name }}
                    </span>
                @endforeach
            </div>
        </div>
    </div>
@endif
