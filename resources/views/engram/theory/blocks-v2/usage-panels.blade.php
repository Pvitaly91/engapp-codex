@php($data = $data ?? json_decode($block->body ?? '[]', true) ?? [])
@php($sections = $data['sections'] ?? [])
@php($colorClasses = fn($variant) => match($variant) {
    'emerald' => [
        'bg' => 'from-emerald-50 to-emerald-50/50',
        'border' => 'border-emerald-200/60',
        'icon' => 'bg-emerald-500 text-white',
        'label' => 'text-emerald-700',
        'example' => 'bg-emerald-100/50 border-emerald-200/50',
    ],
    'rose' => [
        'bg' => 'from-rose-50 to-rose-50/50',
        'border' => 'border-rose-200/60',
        'icon' => 'bg-rose-500 text-white',
        'label' => 'text-rose-700',
        'example' => 'bg-rose-100/50 border-rose-200/50',
    ],
    'sky' => [
        'bg' => 'from-sky-50 to-sky-50/50',
        'border' => 'border-sky-200/60',
        'icon' => 'bg-sky-500 text-white',
        'label' => 'text-sky-700',
        'example' => 'bg-sky-100/50 border-sky-200/50',
    ],
    'blue' => [
        'bg' => 'from-blue-50 to-blue-50/50',
        'border' => 'border-blue-200/60',
        'icon' => 'bg-blue-500 text-white',
        'label' => 'text-blue-700',
        'example' => 'bg-blue-100/50 border-blue-200/50',
    ],
    'amber' => [
        'bg' => 'from-amber-50 to-amber-50/50',
        'border' => 'border-amber-200/60',
        'icon' => 'bg-amber-500 text-white',
        'label' => 'text-amber-700',
        'example' => 'bg-amber-100/50 border-amber-200/50',
    ],
    default => [
        'bg' => 'from-slate-50 to-slate-50/50',
        'border' => 'border-slate-200/60',
        'icon' => 'bg-slate-500 text-white',
        'label' => 'text-slate-700',
        'example' => 'bg-slate-100/50 border-slate-200/50',
    ],
})

<section id="block-{{ $block->id }}" class="scroll-mt-24">
    <div class="rounded-3xl border border-border/50 bg-card/80 p-6 md:p-8 shadow-sm backdrop-blur-sm">
        @if(!empty($data['title']))
            <h2 class="text-xl md:text-2xl font-bold text-foreground mb-6 flex items-center gap-3">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-secondary/50 text-secondary-foreground text-sm font-bold">
                    {{ preg_replace('/[^0-9]/', '', $data['title']) ?: '•' }}
                </span>
                {{ preg_replace('/^\d+\.\s*/', '', $data['title']) }}
            </h2>
        @endif

        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach($sections as $index => $section)
                @php($colors = $colorClasses($section['color'] ?? null))
                <article class="group relative overflow-hidden rounded-2xl border {{ $colors['border'] }} bg-gradient-to-br {{ $colors['bg'] }} p-6 transition-all hover:shadow-lg">
                    {{-- Label Badge --}}
                    @if(!empty($section['label']))
                        <div class="flex items-center gap-2 mb-4">
                            <span class="flex h-6 w-6 items-center justify-center rounded-full {{ $colors['icon'] }} text-xs font-bold">
                                {{ $index + 1 }}
                            </span>
                            <span class="text-sm font-bold uppercase tracking-wider {{ $colors['label'] }}">
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
                                <div class="rounded-xl border {{ $colors['example'] }} p-3">
                                    <p class="font-mono text-xs text-foreground font-medium">
                                        {{ $example['en'] ?? '' }}
                                    </p>
                                    @if(!empty($example['ua']))
                                        <p class="text-xs text-muted-foreground mt-1 italic">
                                            {{ $example['ua'] }}
                                        </p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif

                    {{-- Note --}}
                    @if(!empty($section['note']))
                        <div class="mt-4 flex items-start gap-2 text-xs text-muted-foreground">
                            <svg class="h-4 w-4 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>{!! $section['note'] !!}</span>
                        </div>
                    @endif
                </article>
            @endforeach
        </div>
    </div>
</section>
