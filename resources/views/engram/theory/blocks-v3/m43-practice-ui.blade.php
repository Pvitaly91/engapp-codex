{{-- Package-owned practice factory, shared theory presentation. --}}
@if($theoryCanonical ?? false)
    @include('engram.theory.blocks-v3.authored-practice-ui', [
        'practiceScope' => 'm43', 'practiceFactory' => 'm43PracticeUi', 'practiceScript' => 'js/m43-practice-ui.js',
    ])
@else
    @include('courses.compatibility.theory.m43-practice-ui')
@endif
