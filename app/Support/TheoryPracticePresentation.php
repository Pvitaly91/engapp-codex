<?php

namespace App\Support;

use Illuminate\Support\HtmlString;

/** Render-only shared instruction/feedback presentation, never answer authority. */
final class TheoryPracticePresentation
{
    private const NATIVE_DISPLAY = 'docs/content/theory-native-practice-display.v1.json';
    private const NATIVE_DISPLAY_SHA = '4d08ff3ce28a8746fdfdab4e112c37d34f50d7acbd205292e9949790d6ffb2f0';

    /**
     * Reviewed labels are independent of the raw values used by the engine.
     * Exact source guards also bind each deferred key to its native control.
     * Unknown consumers or changed payloads retain the complete original view.
     */
    public static function nativeDisplay(object $block, array $data, ?array $design, bool $theoryPage): ?array
    {
        if (!$theoryPage || ($block->type ?? null) !== 'practice-set'
            || ($design['component'] ?? null) !== 'practice-set') {
            return null;
        }

        try {
            $binding = M42NativeDesignPackage::binding($block, $data);
            if ($binding === null || $binding['plan'] !== $design) { return null; }

            if (!is_array($data['author_self_check']['answers'] ?? null)) {
                // Earlier native lessons contain real short candidates, not keys.
                // Move only exact reviewed label ranges onto their own buttons.
                $practice = TheoryHtmlAdapter::nativePresentation($design)['practice'] ?? null;
                if (!is_array($practice)) { return null; }
                $display = ['selects' => [], 'choices' => [], 'answer_groups' => []];
                foreach (['selects', 'choices'] as $group) {
                    foreach ($practice[$group] ?? [] as $field) {
                        $index = $field['index'] ?? null;
                        $item = $data[$group][$index] ?? null;
                        if (!is_int($index) || !is_array($item) || !is_string($item['label'] ?? null)
                            || !is_string($field['label_html'] ?? null) || isset($display[$group][$index])
                            || !hash_equals($field['label_sha256'], hash('sha256', $item['label']))) { return null; }
                        $display[$group][$index] = ['label_html' => $field['label_html']];
                        if (isset($field['option_labels'])) {
                            $options = $item['options'] ?? $data['choice_options'] ?? ['a', 'b'];
                            if ($group !== 'choices' || !is_array($field['option_labels'])
                                || count($field['option_labels']) !== count($options)) { return null; }
                            $labels = [];
                            foreach ($options as $option) {
                                $label = $field['option_labels'][$option] ?? null;
                                if (!is_string($label) || trim($label) === '' || in_array($label, $labels, true)) { return null; }
                                $labels[$option] = $label;
                            }
                            $display[$group][$index]['option_labels'] = $labels;
                        }
                    }
                }
                return $display;
            }

            $answers = $data['author_self_check']['answers'];
            if ($answers === [] || !array_is_list($answers)) { return null; }
            $result = ['selects' => [], 'choices' => [], 'answer_groups' => []];
            foreach (['selects', 'choices', 'inputs', 'rephrase'] as $group) {
                $items = $data[$group] ?? [];
                if (!is_array($items) || !array_is_list($items)) { return null; }
                foreach ($items as $index => $item) {
                    $sourceIndex = $item['source_index'] ?? null;
                    if (!is_int($sourceIndex) || $sourceIndex < 1 || $sourceIndex > count($answers)
                        || isset($result['answer_groups'][$sourceIndex])) { return null; }
                    $result['answer_groups'][$sourceIndex] = ['group' => $group, 'index' => $index];
                }
            }
            // The view indexes keys by author number, not native group order.
            if (count($result['answer_groups']) !== count($answers)) { return null; }
            ksort($result['answer_groups']);

            $path = base_path(self::NATIVE_DISPLAY);
            if (!is_file($path)) { return null; }
            $bytes = file_get_contents($path);
            if (!is_string($bytes) || !hash_equals(self::NATIVE_DISPLAY_SHA, hash('sha256', $bytes))) { return null; }
            static $mapping = null;
            $mapping ??= json_decode($bytes, true, flags: JSON_THROW_ON_ERROR);
            if (($mapping['schema_version'] ?? null) !== 1 || !is_array($mapping['targets'] ?? null)) { return null; }

            $entry = null;
            foreach ($mapping['targets'] as $candidate) {
                if (($candidate['identity'] ?? null) !== ($block->seeder ?? null)) { continue; }
                if ($entry !== null) { return null; }
                $entry = $candidate;
            }
            // Other exact source-linked native owners need key gating only.
            if ($entry === null) { return $result; }
            $sourceClass = $binding['target']['package_class'];
            if (($entry['slug'] ?? null) !== $binding['target']['slug']
                || ($entry['source'] ?? null) !== $sourceClass::SOURCE
                || ($entry['source_block_index'] ?? null) !== $design['source_index']
                || ($entry['body_sha256'] ?? null) !== $design['body_sha256']) { return null; }

            foreach ($entry['selects'] ?? [] as $select) {
                $index = $select['index'];
                $item = $data['selects'][$index] ?? null;
                if (!is_int($index) || !is_array($item)
                    || ($item['source_index'] ?? null) !== $select['source_index']
                    || isset($result['selects'][$index])) { return null; }
                $options = $item['options'] ?? $data['options'] ?? [];
                $labels = [];
                foreach ($select['options'] as $option) {
                    $optionIndex = $option['index'];
                    $original = $options[$optionIndex] ?? null;
                    $display = $option['display'] ?? null;
                    if (!is_int($optionIndex) || !is_string($original)
                        || !is_string($display) || trim($display) === ''
                        || isset($labels[$optionIndex])
                        || !hash_equals($option['source_sha256'], hash('sha256', $original))) { return null; }
                    $labels[$optionIndex] = $display;
                }
                // Distinct graded values must never become identical buttons.
                $visible = [];
                foreach ($options as $optionIndex => $original) {
                    $label = $labels[$optionIndex] ?? $original;
                    if (isset($visible[$label]) && $visible[$label] !== $original) { return null; }
                    $visible[$label] = $original;
                }
                $result['selects'][$index] = ['options' => $labels];
            }
            foreach ($entry['choices'] ?? [] as $choice) {
                $index = $choice['index'];
                $item = $data['choices'][$index] ?? null;
                $original = $item['prompt'] ?? null;
                $display = $choice['prompt_html'] ?? null;
                if (!is_int($index) || !is_array($item) || !is_string($original)
                    || ($item['source_index'] ?? null) !== $choice['source_index']
                    || !is_string($display) || trim($display) === ''
                    || isset($result['choices'][$index])
                    || !hash_equals($choice['source_sha256'], hash('sha256', $original))) { return null; }
                // HTML is trusted only from the pinned finite file, never input.
                $result['choices'][$index] = ['prompt_html' => $display];
            }

            return $result;
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * A finite presentation projection of the existing learning objectives.
     * IDs identify data contracts only: roles, never package provenance, select
     * the visual accent. None of these entries changes the stored control kind.
     */
    private const CONTROL_ROLES = [
        'm45-a-q4-meaning' => 'meaning-selection',
        'm45-a-q1-tense' => 'form-selection',
        'm45-b-practice-state-answer' => 'form-selection',
        'm44-choice-q3-activity' => 'form-entry',
        'm44-choice-q3-state' => 'form-entry',
        'm45-a-q2-verb' => 'form-entry',
        'm45-a-q6-repair' => 'form-entry',
        'm45-b-practice-negative-answer' => 'form-entry',
        'm45-b-practice-repeated-answer' => 'form-entry',
        'used-q5a' => 'form-entry',
    ];

    /** These tasks explicitly request a note/story of several sentences. */
    private const EXTENDED_WRITING = [
        'm40-p6-answer', 'm40-n6-answer', 'n5-answer', 'n6-answer',
        'c1-6-answer', 'c2-5-answer', 'c2-6-answer',
    ];

    /** Mixed controls keep their own roles; the shell reflects the whole task. */
    private const COMPOUND_ROLES = [
        'm43-past-q5-state|m43-past-q5-explanation' => 'meaning-selection',
        'm43-past-q6-meaning|m43-past-q6-result' => 'meaning-selection',
        'past-q5a|past-q5b' => 'meaning-selection',
        'present-q6a|present-q6b' => 'form-selection',
        'm40-n2-process|m40-n2-arrival|m40-n2-stopped' => 'form-selection',
        'n1-form|n1-head' => 'form-selection',
        'n3-equivalence|n3-added-facts|n3-rewrite' => 'meaning-selection',
        'c2-3-a|c2-3-b' => 'meaning-selection',
        'c2-4-guaranteed|c2-4-scope' => 'meaning-selection',
    ];

    public static function prompt(string $html, int $number): string
    {
        return preg_replace_callback('/<h4>(.*?)<\/h4>/s', static fn ($match) => view('components.theory-practice-heading', [
            'title' => new HtmlString($match[1]), 'number' => $number, 'level' => 'h4', 'technical' => true,
        ])->render(), $html, 1) ?? $html;
    }

    public static function task(array $task, string $prompt, int $number): array
    {
        $sourceControls = array_values($task['controls'] ?? []);
        $controls = [];
        foreach ($sourceControls as $index => $control) {
            $id = (string) ($control['id'] ?? '');
            $role = self::CONTROL_ROLES[$id] ?? match ($control['kind'] ?? '') {
                'select' => 'form-selection',
                'choice', 'multi' => 'meaning-selection',
                'manual' => in_array($id, self::EXTENDED_WRITING, true) ? 'extended-writing' : 'sentence-building',
                default => 'response',
            };
            $label = (string) ($control['label'] ?? '');
            $marker = count($sourceControls) > 1 ? self::letter($index) : null;
            // Move an existing source marker; never show an additional a/b/c.
            if (preg_match('/^\s*([a-zабвгґдеєжзиіїйклмнопрстуфхцчшщюя])\s*[:.)]\s*(.+)$/iu', $label, $parts) === 1) {
                $marker = $parts[1];
                $label = $parts[2];
            } elseif (preg_match('/^\s*[a-zабвгґдеєжзиіїйклмнопрстуфхцчшщюя]\s*$/iu', $label) === 1) {
                // A standalone source label still names the accessible field.
                $marker = null;
            }
            $controls[] = [
                'role' => $role, 'accent' => self::accent($role),
                'marker' => $marker, 'label' => $label,
                // Keep textarea editing/paste/Ctrl+Enter behavior. A compact
                // one-row textarea may wrap without narrowing valid answers.
                'field' => ($control['kind'] ?? '') === 'manual' ? 'textarea' : null,
                'rows' => $role === 'extended-writing' ? 2 : 1,
            ];
        }

        $signature = implode('|', array_map(static fn ($control) => (string) ($control['id'] ?? ''), $sourceControls));
        $roles = array_values(array_unique(array_column($controls, 'role')));
        $role = self::COMPOUND_ROLES[$signature] ?? (count($roles) === 1 ? $roles[0] : 'mixed-response');
        $accent = self::accent($role);

        return [
            'role' => $role, 'accent' => $accent, 'number' => $number,
            'prompt_html' => self::taskPrompt($prompt, $number, $accent),
            'controls' => $controls,
        ];
    }

    private static function accent(string $role): string
    {
        return match ($role) {
            'form-selection', 'form-entry' => 'blue',
            'meaning-selection', 'mixed-response' => 'amber',
            'sentence-building', 'extended-writing' => 'emerald',
            default => 'slate',
        };
    }

    private static function letter(int $index): string
    {
        $letter = '';
        do {
            $letter = chr(97 + $index % 26).$letter;
            $index = intdiv($index, 26) - 1;
        } while ($index >= 0);

        return $letter;
    }

    private static function taskPrompt(string $html, int $number, string $accent): string
    {
        $heading = static function (string $title) use ($number, $accent): string {
            // Move the matching title number into the shared technical badge;
            // preserve every author word, inline element and punctuation after it.
            $title = preg_replace('/^(\s*(?:Вправа\s+)?)'.preg_quote((string) $number, '/').'[.)]\s*/u', '$1', $title) ?? $title;

            return view('components.theory-practice-heading', [
                'title' => new HtmlString($title), 'number' => $number,
                'accent' => $accent, 'level' => 'h4', 'technical' => true,
            ])->render();
        };

        if (preg_match('/^\s*<h4>(.*?)<\/h4>/su', $html) === 1) {
            return preg_replace_callback('/^(\s*)<h4>(.*?)<\/h4>/su',
                static fn ($match) => $match[1].$heading($match[2]), $html, 1) ?? $html;
        }

        // Earlier authored prompts put their title in the first p > strong.
        // Move just that title; the instruction and context retain their HTML.
        return preg_replace_callback('/^(\s*)<p><strong>(.*?)<\/strong>(.*?)<\/p>/su',
            static fn ($match) => $match[1].$heading($match[2])
                .(trim($match[3]) !== '' ? '<p>'.$match[3].'</p>' : ''), $html, 1) ?? $html;
    }

    public static function author(array $author, array $cases = []): array
    {
        if ($cases === []) {
            // Compatibility callers retain the pre-refactor presentation path.
            $author['prompts'] = array_map(static fn ($html, $index) => self::prompt($html, $index + 1),
                $author['prompts'], array_keys($author['prompts']));
        } else {
            $author['presentation'] = [];
            foreach (array_values($cases) as $index => $task) {
                $sourceIndex = (int) ($task['source_index'] ?? ($index + 1)) - 1;
                $author['presentation'][] = self::task($task, (string) ($author['prompts'][$sourceIndex] ?? ''), $index + 1);
            }
        }
        $author['answers'] = array_map(static fn ($html) => TheoryHtmlAdapter::fragment($html)->toHtml(), $author['answers']);
        return $author;
    }
}
