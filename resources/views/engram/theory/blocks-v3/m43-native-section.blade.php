{{-- Compatibility boundary: packages supply data, not a theory design. --}}
@if($theoryCanonical ?? false)
@if(isset($data['m43_native_design']))
    @include('theory.partials.authored-section')
@else
    @include('theory.partials.authored-fallback')
@endif
@else
    @include('courses.compatibility.theory.m43-native-section')
@endif
