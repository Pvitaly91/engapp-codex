@php($data = $data ?? json_decode($block->body ?? '[]', true) ?? [])
@php($items = $data['items'] ?? [])
<section class="max-w-5xl mx-auto px-4 border-t border-slate-100 pt-6">
    <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 mb-3">{{ $data['title'] ?? '' }}</h2>
    <div class="flex flex-wrap gap-3">
        @foreach($items as $item)
            @if(! empty($item['current']))
                <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-700">
                    {{ $item['label'] ?? '' }}
                </span>
            @else
                <a href="{{ $item['url'] ?? '#' }}"
                   class="inline-flex items-center rounded-full border border-slate-200 bg-white px-4 py-2 text-sm text-slate-700 hover:border-slate-300 hover:bg-slate-50">
                    {{ $item['label'] ?? '' }}
                </a>
            @endif
        @endforeach
    </div>
</section>
