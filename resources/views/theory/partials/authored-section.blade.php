{{-- Guarded data and finite editorial grouping; appearance belongs to semantic components. --}}
@php($node = \App\Support\TheoryAuthoredAdapter::section($block, $data, $pointSections ?? null, $compact ?? null, $guarded ?? true))
@include('theory.components.node', ['node' => $node])
