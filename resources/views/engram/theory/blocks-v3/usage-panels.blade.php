@php($data = $data ?? json_decode($block->body ?? '[]', true) ?? [])
@php($sections = $data['sections'] ?? [])

<section id="block-{{ $block->id }}" class="scroll-mt-24">
    <div class="rounded-2xl border border-border/60 bg-card overflow-hidden">
        @if(!empty($data['title']))
            <div class="border-b border-border/40 bg-muted/30 px-5 py-4">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="flex items-center gap-3 text-lg font-bold text-foreground">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-secondary text-secondary-foreground text-xs font-bold">
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

        <div class="p-5 space-y-4">
            @foreach($sections as $index => $section)
                @php($color = $section['color'] ?? 'slate')
                @php($colorStyles = match($color) {
                    'emerald' => ['border' => 'border-emerald-200', 'bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'badge' => 'bg-emerald-500'],
                    'rose' => ['border' => 'border-rose-200', 'bg' => 'bg-rose-50', 'text' => 'text-rose-700', 'badge' => 'bg-rose-500'],
                    'sky' => ['border' => 'border-sky-200', 'bg' => 'bg-sky-50', 'text' => 'text-sky-700', 'badge' => 'bg-sky-500'],
                    'blue' => ['border' => 'border-blue-200', 'bg' => 'bg-blue-50', 'text' => 'text-blue-700', 'badge' => 'bg-blue-500'],
                    'amber' => ['border' => 'border-amber-200', 'bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'badge' => 'bg-amber-500'],
                    default => ['border' => 'border-slate-200', 'bg' => 'bg-slate-50', 'text' => 'text-slate-700', 'badge' => 'bg-slate-500'],
                })
                
                <article class="rounded-xl border {{ $colorStyles['border'] }} {{ $colorStyles['bg'] }} overflow-hidden">
                    <div class="p-4">
                        {{-- Section Header --}}
                        @if(!empty($section['label']))
                            <div class="flex items-center gap-2 mb-3">
                                <span class="flex h-5 w-5 items-center justify-center rounded-full {{ $colorStyles['badge'] }} text-white text-[10px] font-bold">
                                    {{ $index + 1 }}
                                </span>
                                <span class="text-xs font-bold uppercase tracking-wider {{ $colorStyles['text'] }}">
                                    {{ $section['label'] }}
                                </span>
                            </div>
                        @endif

                        {{-- Description --}}
                        @if(!empty($section['description']))
                            <p class="text-sm text-foreground/80 leading-relaxed mb-4">
                                {!! $section['description'] !!}
                            </p>
                        @endif

                        {{-- Examples --}}
                        @if(!empty($section['examples']))
                            <div class="space-y-2">
                                @foreach($section['examples'] as $example)
                                    <div class="flex items-start gap-3 rounded-lg bg-white/60 border border-white/80 p-3">
                                        <span class="flex-shrink-0 text-lg">💬</span>
                                        <div class="min-w-0 flex-1">
                                            <p class="font-mono text-xs font-medium text-foreground">
                                                {{ $example['en'] ?? '' }}
                                            </p>
                                            @if(!empty($example['ua']))
                                                <p class="text-xs text-muted-foreground mt-0.5 italic">
                                                    {{ $example['ua'] }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        {{-- Note --}}
                        @if(!empty($section['note']))
                            <div class="mt-3 flex items-start gap-2 text-xs text-muted-foreground bg-white/40 rounded-lg p-2.5">
                                <svg class="h-4 w-4 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>{!! $section['note'] !!}</span>
                            </div>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
