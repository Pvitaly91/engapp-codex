@php($data = $data ?? json_decode($block->body ?? '[]', true) ?? [])
@php($rules = $data['rules'] ?? [])
@php($level = $data['level'] ?? null)
@php($intro = $data['intro'] ?? null)
@php($breadcrumbs = $breadcrumbs ?? [])
@php($color = fn($variant) => match($variant) {
    'emerald' => ['bg' => 'bg-emerald-50/60', 'border' => 'border-emerald-100', 'text' => 'text-emerald-700'],
    'blue' => ['bg' => 'bg-blue-50/60', 'border' => 'border-blue-100', 'text' => 'text-blue-700'],
    'rose' => ['bg' => 'bg-rose-50/60', 'border' => 'border-rose-100', 'text' => 'text-rose-700'],
    default => ['bg' => 'bg-slate-50/60', 'border' => 'border-slate-100', 'text' => 'text-slate-700'],
})
<div class="max-w-5xl mx-auto px-4 py-10">
    <div class="flex items-center justify-between gap-4 mb-6">
        <nav class="text-sm text-slate-500">
            <ol class="flex items-center gap-2">
                @foreach($breadcrumbs as $index => $crumb)
                    <li>
                        @if(! empty($crumb['url']))
                            <a href="{{ $crumb['url'] }}" class="hover:text-slate-700 hover:underline">{{ $crumb['label'] }}</a>
                        @else
                            <span class="text-slate-700 font-medium">{{ $crumb['label'] }}</span>
                        @endif
                    </li>
                    @if($index < count($breadcrumbs) - 1)
                        <li class="text-slate-400">/</li>
                    @endif
                @endforeach
            </ol>
        </nav>
        @if($level)
            <span class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 border border-emerald-100">
                Рівень {{ $level }}
            </span>
        @endif
    </div>

    <header class="mb-8">
        <h1 class="text-3xl md:text-4xl font-bold tracking-tight text-slate-900 mb-3">
            {{ $page->title }}
        </h1>
        @if($intro)
            <p class="text-slate-600 mb-4">{!! $intro !!}</p>
        @endif

        @if(! empty($rules))
            <div class="grid gap-3 md:grid-cols-3">
                @foreach($rules as $rule)
                    @php($palette = $color($rule['color'] ?? null))
                    <div class="rounded-2xl border {{ $palette['border'] }} {{ $palette['bg'] }} p-4">
                        @if(! empty($rule['label']))
                            <p class="text-xs font-semibold uppercase tracking-wide {{ $palette['text'] }} mb-1">{{ $rule['label'] }}</p>
                        @endif
                        <p class="text-sm text-slate-800">
                            {!! $rule['text'] ?? '' !!}
                            @if(! empty($rule['example']))
                                <br><span class="font-mono text-xs text-slate-600">{{ $rule['example'] }}</span>
                            @endif
                        </p>
                    </div>
                @endforeach
            </div>
        @endif
    </header>
</div>
