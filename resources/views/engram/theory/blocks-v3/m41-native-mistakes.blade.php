{{-- Historical non-theory compatibility only. Theory adapters use semantic components. --}}
@unless($theoryCanonical ?? false)
    @include('courses.compatibility.theory.m41-native-mistakes')
@endunless
