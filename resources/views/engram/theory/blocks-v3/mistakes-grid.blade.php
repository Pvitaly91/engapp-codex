@if($theoryCanonical ?? false)
    @include('theory.components.node', ['node' => \App\Support\TheoryLegacyAdapter::section($block, $data ?? json_decode($block->body ?? '[]', true) ?? [], $pointSections ?? [], $lessonLinks ?? [], $embeddedDetail ?? false, $m42Design ?? null)])
@else
    @include('courses.compatibility.theory.mistakes-grid')
@endif
