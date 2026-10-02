@php($data = $data ?? json_decode($block->body ?? '[]', true) ?? [])
@php($sections = $data['sections'] ?? [])

<section id="block-{{ $block->id }}" class="theory-native-block scroll-mt-24">
    <div @if(!($embeddedDetail ?? false)) class="theory-section-card rounded-2xl border border-border/60 bg-card" @endif>
        @if(!($embeddedDetail ?? false) && !empty($data['title']))
            <x-theory-native-header :title="$data['title']" :level="$block->level ?? null" />
        @endif

        <div class="theory-section-body p-5 space-y-4">
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
                
                <article class="theory-item rounded-xl border {{ $colorStyles['border'] }} {{ $colorStyles['bg'] }}">
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
                                    <div class="theory-example flex items-start gap-3 rounded-lg bg-white/60 border border-white/80 p-3">
                                        <span class="flex-shrink-0 text-lg">💬</span>
                                        <div class="min-w-0 flex-1">
                                            <p class="font-mono text-xs font-medium text-foreground">
                                                {{ \App\Support\TheoryInlineHtml::render($example['en'] ?? '') }}
                                            </p>
                                            @if(!empty($example['ua']))
                                                <p class="theory-translation text-xs text-muted-foreground mt-0.5 italic">
                                                    {{ \App\Support\TheoryInlineHtml::render($example['ua']) }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        {{-- Note --}}
                        @if(!empty($section['note']))
                            <div class="theory-note mt-3 flex items-start gap-2 text-xs text-muted-foreground bg-white/40 rounded-lg p-2.5">
                                <svg class="h-4 w-4 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>{!! $section['note'] !!}</span>
                            </div>
                        @endif
                        @include('theory.partials.point-disclosure', ['index' => $index])
                    </div>
                </article>
            @endforeach

            {{-- Block Tags --}}
            @unless($embeddedDetail ?? false)
            <x-text-block-tags :block="$block" />

            {{-- Practice Questions --}}
            <x-text-block-practice-questions :questions="$practiceQuestions ?? collect()" :blockUuid="$block->uuid" />
            @endunless
        </div>
    </div>
</section>
