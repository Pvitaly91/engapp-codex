@php($data = $data ?? json_decode($block->body ?? '[]', true) ?? [])
@php($items = $data['items'] ?? [])

<section id="block-{{ $block->id }}" class="scroll-mt-24">
    <div class="rounded-2xl border border-emerald-200/60 bg-gradient-to-br from-emerald-50/30 to-card overflow-hidden">
        @if(!empty($data['title']))
            <div class="border-b border-emerald-100 bg-emerald-50/50 px-5 py-4">
                <h2 class="flex items-center gap-3 text-lg font-bold text-foreground">
                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-500 text-white text-xs font-bold">
                        ✓
                    </span>
                    {{ preg_replace('/^\d+\.\s*/', '', $data['title']) }}
                </h2>
            </div>
        @endif

        <div class="p-5">
            <div class="space-y-3">
                @foreach($items as $index => $item)
                    <div class="flex items-start gap-3 group">
                        <span class="flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 text-xs font-bold mt-0.5 group-hover:bg-emerald-500 group-hover:text-white transition-colors">
                            {{ $index + 1 }}
                        </span>
                        <span class="text-sm text-foreground/80 leading-relaxed pt-0.5">
                            {!! $item !!}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
