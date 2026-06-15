@include('engram.theory.widgets.lesson-rule-cards', [
    'block' => $block,
    'data' => $data ?? json_decode($block->body ?? '[]', true) ?? [],
    'lessonLinks' => $lessonLinks ?? [],
    'practiceQuestions' => $practiceQuestions ?? collect(),
])
