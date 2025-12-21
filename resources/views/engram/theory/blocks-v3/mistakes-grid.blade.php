@php($data = $data ?? json_decode($block->body ?? '[]', true) ?? [])
@php($items = $data['items'] ?? [])

<section id="block-{{ $block->id }}" class="scroll-mt-24">
    <div class="rounded-2xl border border-rose-200/60 bg-gradient-to-br from-rose-50/50 to-card overflow-hidden">
        @if(!empty($data['title']))
            <div class="border-b border-rose-100 bg-rose-50/60 px-5 py-4">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="flex items-center gap-3 text-lg font-bold text-foreground">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-rose-500 text-white text-xs font-bold">
                            ⚠
                        </span>
                        {{ preg_replace('/^\d+\.\s*/', '', $data['title']) }}
                    </h2>
                    <x-text-block-level-badge :level="$block->level ?? null" />
                </div>
            </div>
        @endif

        <div class="p-5 space-y-4">
            @foreach($items as $index => $item)
                @php($color = $item['color'] ?? 'rose')
                @php($colorStyles = match($color) {
                    'rose' => ['border' => 'border-rose-200', 'badge' => 'bg-rose-500'],
                    'amber' => ['border' => 'border-amber-200', 'badge' => 'bg-amber-500'],
                    'sky' => ['border' => 'border-sky-200', 'badge' => 'bg-sky-500'],
                    default => ['border' => 'border-slate-200', 'badge' => 'bg-slate-500'],
                })

                <article class="rounded-xl border {{ $colorStyles['border'] }} bg-white overflow-hidden">
                    <div class="p-4">
                        {{-- Header --}}
                        <div class="flex items-start gap-3 mb-3">
                            @if(!empty($item['label']))
                                <span class="flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full {{ $colorStyles['badge'] }} text-white text-[10px] font-bold mt-0.5">
                                    {{ $index + 1 }}
                                </span>
                            @endif
                            <div class="min-w-0 flex-1">
                                @if(!empty($item['label']))
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground block mb-1">
                                        {{ $item['label'] }}
                                    </span>
                                @endif
                                @if(!empty($item['title']))
                                    <h3 class="text-sm font-semibold text-foreground">
                                        {!! $item['title'] !!}
                                    </h3>
                                @endif
                            </div>
                        </div>

                        {{-- Wrong/Right Examples --}}
                        <div class="space-y-2 pl-9">
                            @if(!empty($item['wrong']))
                                <div class="flex items-center gap-2.5 rounded-lg bg-rose-50 border border-rose-100 px-3 py-2">
                                    <svg class="h-4 w-4 flex-shrink-0 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    <code class="font-mono text-xs text-rose-700 line-through">{{ $item['wrong'] }}</code>
                                </div>
                            @endif

                            @if(!empty($item['right']))
                                <div class="flex items-center gap-2.5 rounded-lg bg-emerald-50 border border-emerald-100 px-3 py-2">
                                    <svg class="h-4 w-4 flex-shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span class="text-xs text-emerald-700">{!! $item['right'] !!}</span>
                                </div>
                            @endif

                            @if(!empty($item['hint']))
                                <div class="flex items-start gap-2 rounded-lg bg-slate-50 border border-slate-100 px-3 py-2 mt-2">
                                    <svg class="h-4 w-4 flex-shrink-0 text-slate-400 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                    </svg>
                                    <span class="text-xs text-muted-foreground leading-relaxed">{!! $item['hint'] !!}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach

            {{-- Block Tags --}}
            <x-text-block-tags :block="$block" />

            {{-- Practice Questions --}}
            <x-text-block-practice-questions :questions="$practiceQuestions ?? collect()" :blockUuid="$block->uuid" />
        </div>
    </div>
</section>
