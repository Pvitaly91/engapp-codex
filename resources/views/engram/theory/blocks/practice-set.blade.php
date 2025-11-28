@php($data = $data ?? json_decode($block->body ?? '[]', true) ?? [])
@php($selects = $data['selects'] ?? [])
@php($inputs = $data['inputs'] ?? [])
@php($rephrase = $data['rephrase'] ?? [])
@php($options = $data['options'] ?? [])
<section class="max-w-5xl mx-auto px-4 mb-12">
    <h2 class="text-xl md:text-2xl font-semibold text-slate-900 mb-4">{{ $data['title'] ?? '' }}</h2>

    <div class="mb-6 rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
        <h3 class="text-lg font-semibold text-slate-900 mb-2">{{ $data['select_title'] ?? '' }}</h3>
        @if(! empty($data['select_intro']))
            <p class="text-sm text-slate-600 mb-3">{!! $data['select_intro'] !!}</p>
        @endif
        <div class="space-y-3 text-sm text-slate-800">
            @foreach($selects as $index => $item)
                <div class="flex flex-col gap-1">
                    <label class="font-medium">
                        {{ $index + 1 }}. {{ $item['label'] ?? '' }}
                    </label>
                    <div class="flex items-center gap-2">
                        <select class="rounded-lg border-slate-200 text-sm">
                            <option value="">— обери —</option>
                            @foreach($options as $option)
                                <option>{{ $option }}</option>
                            @endforeach
                        </select>
                        @if(! empty($item['prompt']))
                            <span class="text-xs text-slate-400">{{ $item['prompt'] }}</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="mb-6 rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
        <h3 class="text-lg font-semibold text-slate-900 mb-2">{{ $data['input_title'] ?? '' }}</h3>
        @if(! empty($data['input_intro']))
            <p class="text-sm text-slate-600 mb-3">{!! $data['input_intro'] !!}</p>
        @endif
        <div class="space-y-3 text-sm text-slate-800">
            @foreach($inputs as $item)
                <label class="block">
                    {!! $item['before'] ?? '' !!}
                    <input type="text" class="ml-1 inline-block w-32 rounded-lg border-slate-200 text-sm" placeholder="..." />
                    {!! $item['after'] ?? '' !!}
                </label>
            @endforeach
        </div>
    </div>

    <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
        <h3 class="text-lg font-semibold text-slate-900 mb-2">{{ $data['rephrase_title'] ?? '' }}</h3>
        @if(! empty($data['rephrase_intro']))
            <p class="text-sm text-slate-600 mb-3">{!! $data['rephrase_intro'] !!}</p>
        @endif
        <div class="space-y-3 text-sm text-slate-800">
            @foreach($rephrase as $index => $item)
                @if($index === 0 && ! empty($item['example_original']))
                    <div>
                        <p class="text-xs text-slate-500 mb-1">{{ $item['example_label'] ?? 'Приклад:' }}</p>
                        <p class="font-mono text-xs mb-1">{{ $item['example_original'] }}</p>
                        <p class="font-mono text-xs text-emerald-700">→ {{ $item['example_target'] ?? '' }}</p>
                    </div>
                @else
                    <div>
                        <p class="font-mono text-xs mb-1">{{ $item['original'] ?? '' }}</p>
                        <input type="text" class="mt-1 block w-full rounded-lg border-slate-200 text-sm" placeholder="{{ $item['placeholder'] ?? '' }}" />
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</section>
