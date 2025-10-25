@php($recentSeedRunOrdinals = collect($recentSeedRunOrdinals ?? []))

@foreach($nodes as $node)
    @include('seed-runs.partials.executed-node', [
        'node' => $node,
        'depth' => $depth,
        'recentSeedRunOrdinals' => $recentSeedRunOrdinals,
    ])
@endforeach
