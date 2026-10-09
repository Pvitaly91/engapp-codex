{{-- Compatibility boundary: packages supply data, not a theory design. --}}
@if($theoryCanonical ?? false)
@include('theory.partials.authored-section', ['guarded' => isset($data['m41_existing_design'])])
@else
    @include('courses.compatibility.theory.m41-existing-design-section')
@endif
