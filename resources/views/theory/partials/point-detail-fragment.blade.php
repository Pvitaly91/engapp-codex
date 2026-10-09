@if($theoryCanonical ?? false)
    @include('theory.components.node', ['node' => \App\Support\TheoryPointDetailAdapter::fragment($fragment, $m42Design ?? null)])
@else
    @include('courses.compatibility.theory.point-detail-fragment')
@endif
