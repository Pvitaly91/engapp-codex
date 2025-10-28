@php
    $preparedQuestions = collect($questions ?? [])->map(function ($question) use ($seedRunId, $categoryKey, $sourceKey) {
        $questionId = $question['id'] ?? null;

        return [
            'id' => $questionId,
            'content_html' => $question['highlighted_text'] ?? '',
            'toggle' => [
                'url' => route('seed-runs.questions.answers', [$seedRunId, $questionId]),
                'data' => [
                    'question-id' => $questionId,
                    'seed-run-id' => $seedRunId,
                    'category-key' => $categoryKey,
                    'source-key' => $sourceKey,
                ],
            ],
            'toggle_labels' => [
                'collapsed' => 'Показати варіанти',
                'expanded' => 'Сховати варіанти',
            ],
            'delete' => [
                'url' => route('seed-runs.questions.destroy', $questionId),
                'method' => 'DELETE',
                'confirm' => 'Видалити це питання?',
                'data' => [
                    'question-id' => $questionId,
                    'seed-run-id' => $seedRunId,
                    'category-key' => $categoryKey,
                    'source-key' => $sourceKey,
                ],
                'icon' => '<i class="fa-solid fa-trash-can"></i>',
                'label' => 'Видалити',
                'button_class' => 'inline-flex items-center justify-center gap-1 text-xs font-semibold text-red-700 px-2.5 py-1 rounded-full bg-red-50 hover:bg-red-100 transition w-full sm:w-auto',
            ],
            'details' => [
                'answers' => [
                    'url' => route('seed-runs.questions.answers', [$seedRunId, $questionId]),
                ],
                'tags' => [
                    'url' => route('seed-runs.questions.tags', [$seedRunId, $questionId]),
                ],
            ],
            'container_data' => [
                'question-container' => true,
                'question-id' => $questionId,
                'seed-run-id' => $seedRunId,
                'category-key' => $categoryKey,
                'source-key' => $sourceKey,
            ],
        ];
    });
@endphp

@include('admin.questions.list', [
    'questions' => $preparedQuestions,
    'emptyMessage' => 'Питань не знайдено.',
])
