@include('engram.theory.widgets.lesson-rule-cards', [
    'block' => $block,
    'embeddedDetail' => $embeddedDetail ?? false,
    'pointSections' => $pointSections ?? [],
    'data' => $data ?? json_decode($block->body ?? '[]', true) ?? [],
    'lessonLinks' => $lessonLinks ?? [],
    'practiceQuestions' => $practiceQuestions ?? collect(),
])
