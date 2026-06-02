@php($data = $data ?? json_decode($block->body ?? '[]', true) ?? [])
@php($items = $data['items'] ?? [])
@php($color = fn($variant) => match($variant) {
    'rose' => ['bg' => 'bg-rose-50/70', 'border' => 'border-rose-100', 'text' => 'text-rose-700'],
    'amber' => ['bg' => 'bg-amber-50/70', 'border' => 'border-amber-100', 'text' => 'text-amber-700'],
    'sky' => ['bg' => 'bg-sky-50/70', 'border' => 'border-sky-100', 'text' => 'text-sky-700'],
    default => ['bg' => 'bg-slate-50/60', 'border' => 'border-slate-100', 'text' => 'text-slate-700'],
})
<section class="max-w-5xl mx-auto px-4 mb-10">
    <h2 class="text-xl md:text-2xl font-semibold text-slate-900 mb-3">{{ $data['title'] ?? '' }}</h2>
    <div class="grid gap-4 md:grid-cols-3">
        @foreach($items as $item)
            @php($palette = $color($item['color'] ?? null))
            <div class="rounded-2xl border {{ $palette['border'] }} {{ $palette['bg'] }} p-4">
                @if(! empty($item['label']))
                    <p class="text-xs font-semibold uppercase tracking-wide {{ $palette['text'] }} mb-1">{{ $item['label'] }}</p>
                @endif
                @if(! empty($item['title']))
                    <p class="text-sm text-slate-800 mb-2">{!! $item['title'] !!}</p>
                @endif
                @if(! empty($item['wrong']))
                    <p class="text-xs mb-1 text-rose-700">❌ <span class="font-mono">{{ $item['wrong'] }}</span></p>
                @endif
                @if(! empty($item['right']))
                    <p class="text-xs text-emerald-700">{!! $item['right'] !!}</p>
                @endif
                @if(! empty($item['hint']))
                    <p class="text-xs text-slate-700 mt-2">{!! $item['hint'] !!}</p>
                @endif
            </div>
        @endforeach
    </div>
</section>
