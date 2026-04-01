@php($recentSeedRunOrdinals = collect($recentSeedRunOrdinals ?? []))

@foreach($nodes as $node)
    @include('seed-runs.partials.executed-node', [
        'node' => $node,
        'depth' => $depth,
        'recentSeedRunOrdinals' => $recentSeedRunOrdinals,
        'activeSeederTab' => $activeSeederTab ?? 'main',
    ])
@endforeach
