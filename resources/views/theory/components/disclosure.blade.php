@if(($node['section'] ?? null)?->detail !== null)
    @include('theory.partials.point-disclosure', ['pointSections' => [$node['index'] ?? 0 => $node['section']], 'index' => $node['index'] ?? 0, 'toggleId' => $node['toggle_id'] ?? null, 'disclosureAttrs' => $node['attrs'] ?? []])
@endif
