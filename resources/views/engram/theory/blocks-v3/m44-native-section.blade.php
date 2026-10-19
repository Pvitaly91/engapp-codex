{{-- Compatibility boundary: packages supply data, not a theory design. --}}
@if($theoryCanonical ?? false)
@if(isset($data['m44_native_design']))
    @php
        $hasAuthoredDetails = count(array_filter($data['author_section']['points'], fn ($point) => isset($point['detail']))) > 0;
        $compact = !$hasAuthoredDetails || isset($pointSections)
            ? \App\Support\M44SimplifiedPresentation::section($data['author_section']) : null;
    @endphp
    @include('theory.partials.authored-section')
@else
    @include('theory.partials.authored-fallback')
@endif
@else
    @include('courses.compatibility.theory.m44-native-section')
@endif
