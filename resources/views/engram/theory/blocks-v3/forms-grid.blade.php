@php($data = $data ?? json_decode($block->body ?? '[]', true) ?? [])
@php($items = $data['items'] ?? [])

<section id="block-{{ $block->id }}" class="scroll-mt-24">
    <div class="rounded-2xl border border-border/60 bg-card overflow-hidden">
        @if(!empty($data['title']))
            <div class="border-b border-border/40 bg-muted/30 px-5 py-4">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="flex items-center gap-3 text-lg font-bold text-foreground">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-primary text-primary-foreground text-xs font-bold">
                            {{ preg_replace('/[^0-9]/', '', $data['title']) ?: '#' }}
                        </span>
                        {{ preg_replace('/^\d+\.\s*/', '', $data['title']) }}
                    </h2>
                    @if(!empty($block->level))
                        <span class="inline-flex items-center gap-1.5 rounded-lg bg-amber-100 px-3 py-1 text-xs font-bold text-amber-700">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                            {{ $block->level }}
                        </span>
                    @endif
                </div>
            </div>
        @endif

        <div class="p-5">
            @if(!empty($data['intro']))
                <p class="text-sm text-muted-foreground mb-5 leading-relaxed">{!! $data['intro'] !!}</p>
            @endif

            <div class="grid gap-3 sm:grid-cols-2">
                @foreach($items as $item)
                    <div class="group relative rounded-xl border border-border/50 bg-gradient-to-br from-muted/20 to-transparent p-4 transition-all hover:border-primary/30 hover:shadow-sm">
                        @if(!empty($item['label']))
                            <span class="inline-block rounded-md bg-primary/10 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-primary mb-2">
                                {{ $item['label'] }}
                            </span>
                        @endif
                        
                        <h3 class="text-base font-bold text-foreground mb-1">
                            {{ $item['title'] ?? '' }}
                        </h3>
                        
                        @if(!empty($item['subtitle']))
                            <p class="text-sm text-muted-foreground">
                                {{ $item['subtitle'] }}
                            </p>
                        @endif

                        {{-- Hover indicator --}}
                        <div class="absolute right-3 top-1/2 -translate-y-1/2 opacity-0 group-hover:opacity-100 transition-opacity">
                            <svg class="h-4 w-4 text-primary/50" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
