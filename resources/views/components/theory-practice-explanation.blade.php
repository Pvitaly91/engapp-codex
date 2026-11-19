@props(['title' => null, 'disclosure' => false, 'summaryAttributes' => [], 'bodyAttributes' => []])
@if($disclosure)
    <details {{ $attributes }}>
        <summary {{ new \Illuminate\View\ComponentAttributeBag($summaryAttributes) }} class="text-sm font-semibold cursor-pointer">{{ $title }}</summary>
        <div {{ (new \Illuminate\View\ComponentAttributeBag($bodyAttributes))->class(['theory-item rounded-xl p-4 bg-muted/50 mt-2 text-sm leading-relaxed']) }}>{{ $slot }}</div>
    </details>
@else
    <div {{ $attributes->class(['theory-item rounded-xl p-4 bg-muted/50']) }}>
        @if($title !== null)<h3 class="font-semibold text-foreground text-sm mb-3">{{ $title }}</h3>@endif
        {{ $slot }}
    </div>
@endif
