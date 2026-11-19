{{-- Compatibility boundary: packages supply data, not a theory design. --}}
@if($theoryCanonical ?? false)
@if($detail !== null)
    @include('theory.components.node', ['node' => \App\Support\TheoryAuthoredAdapter::detailFragment($detail, $detail['id'])])
@endif
@else
    @include('courses.compatibility.theory.m45-detail')
@endif
