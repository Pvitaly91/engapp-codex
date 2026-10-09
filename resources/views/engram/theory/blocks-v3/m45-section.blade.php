{{-- Compatibility boundary: packages supply data, not a theory design. --}}
@if($theoryCanonical ?? false)
@include('theory.partials.authored-section')
@else
    @include('courses.compatibility.theory.m45-section')
@endif
