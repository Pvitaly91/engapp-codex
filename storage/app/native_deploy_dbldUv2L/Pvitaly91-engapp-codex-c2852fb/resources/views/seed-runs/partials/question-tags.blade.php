@php
    $tags = collect($tags ?? []);
@endphp

@if($tags->isEmpty())
    <p class="text-xs text-gray-500">Теги не знайдені.</p>
@else
    <div class="space-y-2">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Теги</p>
        <ul class="flex flex-wrap gap-2">
            @foreach($tags as $tag)
                <li class="inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">
                    <span>{{ $tag['name'] ?? '—' }}</span>
                    @if(!empty($tag['category']))
                        <span class="ml-1 text-[11px] font-normal uppercase tracking-wide text-blue-500">{{ $tag['category'] }}</span>
                    @endif
                </li>
            @endforeach
        </ul>
    </div>
@endif
