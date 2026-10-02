@include('engram.theory.widgets.lesson-rule-cards', [
    'block' => $block,
    'embeddedDetail' => $embeddedDetail ?? false,
    'nativeSection' => $nativeSection ?? null,
    'data' => $data ?? json_decode($block->body ?? '[]', true) ?? [],
    'lessonLinks' => $lessonLinks ?? [],
    'practiceQuestions' => $practiceQuestions ?? collect(),
])
