@php($data = $data ?? json_decode($block->body ?? '[]', true) ?? [])
@php($items = $data['items'] ?? [])
<section class="max-w-5xl mx-auto px-4 mb-10">
    <h2 class="text-xl md:text-2xl font-semibold text-slate-900 mb-3">{{ $data['title'] ?? '' }}</h2>
    <div class="rounded-2xl border border-slate-100 bg-slate-50/80 p-4">
        <ul class="list-disc pl-5 space-y-1 text-sm text-slate-800">
            @foreach($items as $item)
                <li>{!! $item !!}</li>
            @endforeach
        </ul>
    </div>
</section>
