@php($data = $data ?? json_decode($block->body ?? '[]', true) ?? [])
@php($items = $data['items'] ?? [])

<section id="block-{{ $block->id }}" class="scroll-mt-24">
    <div class="rounded-2xl border border-emerald-200/60 bg-gradient-to-br from-emerald-50/30 to-card overflow-hidden">
        @if(!empty($data['title']))
            <div class="border-b border-emerald-100 bg-emerald-50/50 px-5 py-4">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="flex items-center gap-3 text-lg font-bold text-foreground">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-500 text-white text-xs font-bold">
                            ✓
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
