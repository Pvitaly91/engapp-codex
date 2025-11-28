@php($data = $data ?? json_decode($block->body ?? '[]', true) ?? [])
@php($items = $data['items'] ?? [])

<section id="block-{{ $block->id }}" class="scroll-mt-24">
    <div class="rounded-3xl border border-border/50 bg-gradient-to-br from-emerald-50/50 via-card to-primary/5 p-6 md:p-8 shadow-sm backdrop-blur-sm">
        @if(!empty($data['title']))
            <h2 class="text-xl md:text-2xl font-bold text-foreground mb-6 flex items-center gap-3">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-500 text-white text-sm font-bold">
                    {{ preg_replace('/[^0-9]/', '', $data['title']) ?: '✓' }}
                </span>
                {{ preg_replace('/^\d+\.\s*/', '', $data['title']) }}
            </h2>
        @endif

        <div class="rounded-2xl border border-emerald-200/50 bg-white/80 p-5 md:p-6">
            <ul class="space-y-3">
                @foreach($items as $index => $item)
                    <li class="flex items-start gap-3 group">
                        <span class="flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 text-xs font-bold mt-0.5 group-hover:bg-emerald-500 group-hover:text-white transition-colors">
                            {{ $index + 1 }}
                        </span>
                        <span class="text-foreground/80 text-sm leading-relaxed">
                            {!! $item !!}
                        </span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</section>
