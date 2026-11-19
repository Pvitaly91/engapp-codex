{{-- Package-owned practice factory, shared theory presentation. --}}
@if($theoryCanonical ?? false)
    @include('engram.theory.blocks-v3.authored-practice-ui', [
        'practiceScope' => 'm45', 'practiceFactory' => 'm45PracticeUi', 'practiceScript' => 'js/m45-practice-ui.js',
    ])
@else
    @include('courses.compatibility.theory.m45-practice-ui')
@endif
