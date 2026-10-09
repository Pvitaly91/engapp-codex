<div data-theory-component="summary" class="space-y-3" @if(!empty($node['id'])) id="{{ $node['id'] }}" @endif{{ \App\Support\TheoryComponents::attrs($node['attrs'] ?? []) }}>
    @include('theory.components.aliases')
    @foreach($node['items'] ?? [] as $index => $item)
        <div class="flex items-start gap-3 group">
            <span class="flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 text-xs font-bold mt-0.5 group-hover:bg-emerald-500 group-hover:text-white transition-colors">{{ $index + 1 }}</span>
            @if($item['block_html'] ?? false)<div class="text-sm text-foreground/80 leading-relaxed pt-0.5">{{ \App\Support\TheoryComponents::body($item['html'] ?? '') }}</div>@else<span class="text-sm text-foreground/80 leading-relaxed pt-0.5">{{ \App\Support\TheoryComponents::body($item['html'] ?? '') }}</span>@endif
            @if(isset($item['detail']))@include('theory.components.node', ['node' => $item['detail']])@endif
        </div>
    @endforeach
</div>
