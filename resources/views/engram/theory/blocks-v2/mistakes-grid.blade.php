@php($data = $data ?? json_decode($block->body ?? '[]', true) ?? [])
@php($items = $data['items'] ?? [])
@php($colorClasses = fn($variant) => match($variant) {
    'rose' => [
        'bg' => 'from-rose-50 to-rose-50/50',
        'border' => 'border-rose-200/60',
        'icon' => 'bg-rose-500',
        'label' => 'text-rose-700',
        'wrong' => 'bg-rose-100/80 text-rose-700',
        'right' => 'bg-emerald-100/80 text-emerald-700',
    ],
    'amber' => [
        'bg' => 'from-amber-50 to-amber-50/50',
        'border' => 'border-amber-200/60',
        'icon' => 'bg-amber-500',
        'label' => 'text-amber-700',
        'wrong' => 'bg-amber-100/80 text-amber-700',
        'right' => 'bg-emerald-100/80 text-emerald-700',
    ],
    'sky' => [
        'bg' => 'from-sky-50 to-sky-50/50',
        'border' => 'border-sky-200/60',
        'icon' => 'bg-sky-500',
        'label' => 'text-sky-700',
        'wrong' => 'bg-sky-100/80 text-sky-700',
        'right' => 'bg-emerald-100/80 text-emerald-700',
    ],
    default => [
        'bg' => 'from-slate-50 to-slate-50/50',
        'border' => 'border-slate-200/60',
        'icon' => 'bg-slate-500',
        'label' => 'text-slate-700',
        'wrong' => 'bg-slate-100/80 text-slate-700',
        'right' => 'bg-emerald-100/80 text-emerald-700',
    ],
})

<section id="block-{{ $block->id }}" class="scroll-mt-24">
    <div class="rounded-3xl border border-border/50 bg-card/80 p-6 md:p-8 shadow-sm backdrop-blur-sm">
        @if(!empty($data['title']))
            <h2 class="text-xl md:text-2xl font-bold text-foreground mb-6 flex items-center gap-3">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-rose-100 text-rose-600 text-sm font-bold">
                    {{ preg_replace('/[^0-9]/', '', $data['title']) ?: '!' }}
                </span>
                {{ preg_replace('/^\d+\.\s*/', '', $data['title']) }}
            </h2>
        @endif

        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            @foreach($items as $index => $item)
                @php($colors = $colorClasses($item['color'] ?? null))
                <article class="group overflow-hidden rounded-2xl border {{ $colors['border'] }} bg-gradient-to-br {{ $colors['bg'] }} transition-all hover:shadow-lg">
                    {{-- Header --}}
                    <div class="p-5 pb-4">
                        @if(!empty($item['label']))
                            <div class="flex items-center gap-2 mb-3">
                                <span class="flex h-5 w-5 items-center justify-center rounded-full {{ $colors['icon'] }} text-white text-[10px] font-bold">
                                    {{ $index + 1 }}
                                </span>
                                <span class="text-xs font-bold uppercase tracking-wider {{ $colors['label'] }}">
                                    {{ $item['label'] }}
                                </span>
                            </div>
                        @endif

                        @if(!empty($item['title']))
                            <h3 class="text-sm font-semibold text-foreground leading-relaxed">
                                {!! $item['title'] !!}
                            </h3>
                        @endif
                    </div>

                    {{-- Content --}}
                    <div class="px-5 pb-5 space-y-2">
                        @if(!empty($item['wrong']))
                            <div class="flex items-center gap-2 rounded-xl {{ $colors['wrong'] }} px-3 py-2">
                                <svg class="h-4 w-4 flex-shrink-0 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                <code class="font-mono text-xs break-words">{{ $item['wrong'] }}</code>
                            </div>
                        @endif

                        @if(!empty($item['right']))
                            <div class="flex items-center gap-2 rounded-xl {{ $colors['right'] }} px-3 py-2">
                                <svg class="h-4 w-4 flex-shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span class="text-xs break-words">{!! $item['right'] !!}</span>
                            </div>
                        @endif

                        @if(!empty($item['hint']))
                            <div class="mt-3 flex items-start gap-2 text-xs text-muted-foreground bg-background/50 rounded-xl p-3">
                                <svg class="h-4 w-4 flex-shrink-0 mt-0.5 text-muted-foreground/70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                </svg>
                                <span class="leading-relaxed">{!! $item['hint'] !!}</span>
                            </div>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
