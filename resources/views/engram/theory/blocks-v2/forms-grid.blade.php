@php($data = $data ?? json_decode($block->body ?? '[]', true) ?? [])
@php($items = $data['items'] ?? [])

<section id="block-{{ $block->id }}" class="scroll-mt-24">
    <div class="rounded-3xl border border-border/50 bg-card/80 p-6 md:p-8 shadow-sm backdrop-blur-sm">
        @if(!empty($data['title']))
            <h2 class="text-xl md:text-2xl font-bold text-foreground mb-2 flex items-center gap-3">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary/10 text-primary text-sm font-bold">
                    {{ preg_replace('/[^0-9]/', '', $data['title']) ?: '•' }}
                </span>
                {{ preg_replace('/^\d+\.\s*/', '', $data['title']) }}
            </h2>
        @endif

        @if(!empty($data['intro']))
            <p class="text-muted-foreground mb-6 leading-relaxed">{!! $data['intro'] !!}</p>
        @endif

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($items as $item)
                <article class="group relative rounded-2xl border border-border/50 bg-gradient-to-br from-background to-muted/30 p-5 transition-all hover:shadow-md hover:border-primary/20">
                    @if(!empty($item['label']))
                        <span class="inline-block rounded-full bg-primary/10 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-primary mb-3">
                            {{ $item['label'] }}
                        </span>
                    @endif
                    
                    <h3 class="text-lg font-bold text-foreground mb-1">
                        {{ $item['title'] ?? '' }}
                    </h3>
                    
                    @if(!empty($item['subtitle']))
                        <p class="text-sm text-muted-foreground leading-relaxed">
                            {{ $item['subtitle'] }}
                        </p>
                    @endif

                    {{-- Decorative accent --}}
                    <div class="absolute bottom-0 left-4 right-4 h-0.5 rounded-full bg-gradient-to-r from-primary/0 via-primary/20 to-primary/0 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                </article>
            @endforeach
        </div>
    </div>
</section>
