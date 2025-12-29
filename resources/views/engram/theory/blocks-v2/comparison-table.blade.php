@php($data = $data ?? json_decode($block->body ?? '[]', true) ?? [])
@php($rows = $data['rows'] ?? [])

<section id="block-{{ $block->id }}" class="scroll-mt-24">
    <div class="rounded-3xl border border-border/50 bg-card/80 p-6 md:p-8 shadow-sm backdrop-blur-sm">
        @if(!empty($data['title']))
            <h2 class="text-xl md:text-2xl font-bold text-foreground mb-2 flex items-center gap-3">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-accent/20 text-accent-foreground text-sm font-bold">
                    {{ preg_replace('/[^0-9]/', '', $data['title']) ?: '•' }}
                </span>
                {{ preg_replace('/^\d+\.\s*/', '', $data['title']) }}
            </h2>
        @endif

        @if(!empty($data['intro']))
            <p class="text-muted-foreground mb-6 leading-relaxed">{!! $data['intro'] !!}</p>
        @endif

        {{-- Modern Card-based Table --}}
        <div class="space-y-3">
            {{-- Header (visible on larger screens) --}}
            <div class="hidden md:grid md:grid-cols-3 gap-4 px-4 py-2 text-xs font-bold uppercase tracking-wider text-muted-foreground">
                <span>Англійською</span>
                <span>Українською</span>
                <span>Коментар</span>
            </div>

            {{-- Rows --}}
            @foreach($rows as $index => $row)
                <div class="group rounded-2xl border border-border/50 bg-gradient-to-r from-background to-muted/20 p-4 transition-all hover:shadow-md hover:border-brand-500">
                    <div class="grid gap-3 md:grid-cols-3 md:gap-4 items-start">
                        {{-- English --}}
                        <div class="md:col-span-1">
                            <span class="md:hidden text-[10px] font-bold uppercase tracking-wider text-muted-foreground block mb-1">Англійською</span>
                            <code class="font-mono text-sm font-medium text-foreground break-words">
                                {{ $row['en'] ?? '' }}
                            </code>
                        </div>

                        {{-- Ukrainian --}}
                        <div class="md:col-span-1">
                            <span class="md:hidden text-[10px] font-bold uppercase tracking-wider text-muted-foreground block mb-1">Українською</span>
                            <span class="text-sm text-foreground/80">
                                {{ $row['ua'] ?? '' }}
                            </span>
                        </div>

                        {{-- Note --}}
                        <div class="md:col-span-1">
                            <span class="md:hidden text-[10px] font-bold uppercase tracking-wider text-muted-foreground block mb-1">Коментар</span>
                            <span class="text-sm text-muted-foreground">
                                {!! $row['note'] ?? '' !!}
                            </span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Warning --}}
        @if(!empty($data['warning']))
            <div class="mt-6 flex items-start gap-3 rounded-2xl border border-amber-200/60 bg-gradient-to-r from-amber-50 to-amber-50/50 p-4">
                <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-amber-500 text-white">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <p class="text-sm text-amber-800">
                    {!! $data['warning'] !!}
                </p>
            </div>
        @endif
    </div>
</section>
