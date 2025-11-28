@php($data = $data ?? json_decode($block->body ?? '[]', true) ?? [])
@php($items = $data['items'] ?? [])
<section class="max-w-5xl mx-auto px-4 mb-10">
    <h2 class="text-xl md:text-2xl font-semibold text-slate-900 mb-3">{{ $data['title'] ?? '' }}</h2>
    @if(! empty($data['intro']))
        <p class="text-slate-600 mb-4">{!! $data['intro'] !!}</p>
    @endif
    <div class="grid gap-4 md:grid-cols-4">
        @foreach($items as $item)
            <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                @if(! empty($item['label']))
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400 mb-1">{{ $item['label'] }}</p>
                @endif
                <p class="text-base font-semibold text-slate-900">{{ $item['title'] ?? '' }}</p>
                @if(! empty($item['subtitle']))
                    <p class="text-sm text-slate-600">{{ $item['subtitle'] }}</p>
                @endif
            </div>
        @endforeach
    </div>
</section>
