@if(in_array($node['kind'] ?? null, \App\Support\TheoryComponents::KINDS, true))
    @include('theory.components.'.$node['kind'], ['node' => $node])
@else
    <div class="theory-fallback-content">{{ $node['text'] ?? '' }}</div>
@endif
