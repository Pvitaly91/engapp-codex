<?php

// Technical field mapping only. Every candidate, accepted variant, token,
// stimulus and educational sentence is supplied by the frozen M41 author.
function m41Practice(array $practice, array $bank): array
{
    if (count($practice) !== 6 || !is_string($bank['seeder_class'] ?? null)
        || !str_starts_with($bank['seeder_class'], 'Database\\Seeders\\V3\\Polyglot\\')
        || (string) ($bank['question_type'] ?? '') !== '4'
        || count($bank['question_ids'] ?? []) !== ($bank['question_count'] ?? null)
        || count(array_unique($bank['question_ids'] ?? [])) !== ($bank['question_count'] ?? null)
        || ($bank['question_count'] ?? 0) < 1) {
        throw new RuntimeException('M41 refuses an incomplete author practice or unverified actual bank record.');
    }
    $escape = static fn (string $text): string => htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
    $paragraph = static fn (string $text): string => '<p>'.$escape($text).'</p>';
    $cases = []; $prompts = []; $answers = []; $ids = [];
    foreach ($practice as $i => $task) {
        if (($task['source_index'] ?? null) !== $i + 1 || ($task['scoring'] ?? null) !== 'all_required_controls'
            || !is_string($task['title'] ?? null) || !is_string($task['prompt_uk'] ?? null)
            || !is_string($task['context_uk'] ?? null) || empty($task['controls'])) {
            throw new RuntimeException('M41 refuses reordered or incomplete authored cases.');
        }
        $prompts[] = '<h4>'.$escape($task['title']).'</h4>'.$paragraph($task['prompt_uk'])
            .($task['context_uk'] !== '' ? $paragraph($task['context_uk']) : '');
        $feedback = '';
        foreach ($task['feedback']['answer_examples'] ?? [] as $example) {
            $feedback .= '<div class="theory-example"><p lang="en">'.$escape($example['en']).'</p><p lang="uk" class="theory-translation">'.$escape($example['uk']).'</p></div>';
        }
        foreach ($task['feedback']['paragraphs_uk'] ?? [] as $text) { $feedback .= $paragraph($text); }
        if ($feedback === '') { throw new RuntimeException('M41 author feedback is incomplete.'); }
        $answers[] = $feedback;
        $controls = [];
        foreach ($task['controls'] as $control) {
            $id = $control['id'] ?? '';
            if ($id === '' || isset($ids[$id]) || ($control['required'] ?? null) !== true
                || !in_array($control['kind'] ?? null, ['select', 'choice', 'manual'], true)
                || !is_string($control['label_uk'] ?? null)) {
                throw new RuntimeException('M41 refuses duplicate or optional/unknown authored controls.');
            }
            $ids[$id] = true;
            $mapped = ['id' => $id, 'kind' => $control['kind'], 'label' => $control['label_uk'], 'required' => true];
            if (isset($control['stimulus_en'])) { $mapped['stimulus_en'] = $control['stimulus_en']; }
            if ($control['kind'] === 'manual') {
                if (implode(' ', $control['tokens'] ?? []) !== ($control['canonical_answer'] ?? null)
                    || !in_array($control['canonical_answer'], $control['accepted_answers'] ?? [], true)) {
                    throw new RuntimeException('M41 canonical answer/token/explicit alias fidelity differs.');
                }
                foreach ($control['tokens'] as $token) {
                    if (preg_match_all('/\S+/u', $token) > 3 || preg_match('/[.!?]\s+\S/u', $token)) {
                        throw new RuntimeException('M41 author token crosses a sentence boundary or exceeds three words.');
                    }
                }
                $mapped += ['options' => [], 'answer' => $control['canonical_answer'],
                    'accepted' => $control['accepted_answers'], 'tokens' => $control['tokens']];
            } else {
                $values = array_column($control['options'] ?? [], 'value');
                if (count(array_filter($values, fn ($value) => $value === ($control['correct_value'] ?? null))) !== 1
                    || count(array_unique($values)) !== count($values)) {
                    throw new RuntimeException('M41 authored correct selection is absent.');
                }
                $mapped += ['options' => $control['options'], 'answer' => $control['correct_value']];
            }
            $controls[] = $mapped;
        }
        $cases[] = ['id' => $task['id'], 'scoring' => $task['scoring'], 'source_index' => $task['source_index'], 'interaction' => count($controls) > 1 ? 'compound' : $controls[0]['kind'],
            'controls' => $controls];
    }
    return ['title' => 'Практика', 'm41_practice_ui_v1' => ['revision' => 1],
        'author_self_check' => ['section_title' => 'Практика', 'intro' => '', 'title' => 'Відповідь і пояснення',
            'prompts' => $prompts, 'answers' => $answers], 'cases' => $cases,
        'linked_practice' => ['source' => 'theory_links', 'question_types' => [(string) $bank['question_type']],
            'seeder_classes' => [$bank['seeder_class']], 'title' => 'Побудуй речення',
            'intro' => 'Склади англійське речення за українським.', 'footer' => 'Завдання з наявного тесту цієї сторінки.']];
}
