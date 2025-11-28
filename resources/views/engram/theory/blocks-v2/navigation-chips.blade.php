@php($data = $data ?? json_decode($block->body ?? '[]', true) ?? [])
@php($items = $data['items'] ?? [])

<section id="block-{{ $block->id }}" class="scroll-mt-24 border-t border-border/30 pt-8 mt-4">
    @if(!empty($data['title']))
        <h3 class="text-sm font-bold uppercase tracking-wider text-muted-foreground mb-4">
            {{ $data['title'] }}
        </h3>
    @endif

    <div class="flex flex-wrap gap-3">
        @foreach($items as $item)
            @if(!empty($item['current']))
                <span class="inline-flex items-center gap-2 rounded-full border-2 border-primary bg-primary/10 px-4 py-2 text-sm font-semibold text-primary">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ $item['label'] ?? '' }}
                </span>
            @else
                <a 
                    href="{{ $item['url'] ?? '#' }}"
                    class="inline-flex items-center gap-2 rounded-full border border-border bg-card px-4 py-2 text-sm font-medium text-muted-foreground transition-all hover:border-primary/50 hover:text-primary hover:shadow-sm"
                >
                    {{ $item['label'] ?? '' }}
                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            @endif
        @endforeach
    </div>
</section>
