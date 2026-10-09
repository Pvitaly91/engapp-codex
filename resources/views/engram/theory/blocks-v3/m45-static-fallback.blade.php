{{-- Compatibility boundary: packages supply data, not a theory design. --}}
@if($theoryCanonical ?? false)
@include('theory.partials.authored-fallback')
@else
    @include('courses.compatibility.theory.m45-static-fallback')
@endif
