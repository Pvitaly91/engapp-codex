@php($data = $data ?? json_decode($block->body ?? '[]', true) ?? [])
@php($sections = $data['sections'] ?? [])
@php($color = fn($variant) => match($variant) {
    'emerald' => ['bg' => 'bg-emerald-50/60', 'border' => 'border-emerald-100', 'text' => 'text-emerald-700'],
    'rose' => ['bg' => 'bg-rose-50/60', 'border' => 'border-rose-100', 'text' => 'text-rose-700'],
    'sky' => ['bg' => 'bg-sky-50/60', 'border' => 'border-sky-100', 'text' => 'text-sky-700'],
    default => ['bg' => 'bg-slate-50/60', 'border' => 'border-slate-100', 'text' => 'text-slate-700'],
})
<section class="max-w-5xl mx-auto px-4 mb-10">
    <h2 class="text-xl md:text-2xl font-semibold text-slate-900 mb-3">{{ $data['title'] ?? '' }}</h2>
    <div class="grid gap-4 md:grid-cols-3">
        @foreach($sections as $section)
            @php($palette = $color($section['color'] ?? null))
            <article class="rounded-2xl border {{ $palette['border'] }} {{ $palette['bg'] }} p-4">
                @if(! empty($section['label']))
                    <p class="text-xs font-semibold uppercase tracking-wide {{ $palette['text'] }} mb-1">
                        {{ $section['label'] }}
                    </p>
                @endif
                @if(! empty($section['description']))
                    <p class="text-sm text-slate-800 mb-2">{!! $section['description'] !!}</p>
                @endif
                @if(! empty($section['examples']))
                    <ul class="space-y-1 text-sm text-slate-800 mb-3">
                        @foreach($section['examples'] as $example)
                            <li>• <span class="font-mono text-xs">{{ $example['en'] ?? '' }}</span></li>
                            @if(! empty($example['ua']))
                                <li class="pl-4 text-xs text-slate-600">{{ $example['ua'] }}</li>
                            @endif
                        @endforeach
                    </ul>
                @endif
                @if(! empty($section['note']))
                    <p class="text-xs text-slate-600">{!! $section['note'] !!}</p>
                @endif
            </article>
        @endforeach
    </div>
</section>
