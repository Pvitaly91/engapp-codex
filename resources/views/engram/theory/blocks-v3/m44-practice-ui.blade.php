{{-- Package-owned practice factory, shared theory presentation. --}}
@if($theoryCanonical ?? false)
    @include('engram.theory.blocks-v3.authored-practice-ui', [
        'practiceScope' => 'm44', 'practiceFactory' => 'm44PracticeUi', 'practiceScript' => 'js/m44-practice-ui.js',
    ])
@else
    @include('courses.compatibility.theory.m44-practice-ui')
@endif
