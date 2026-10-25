<?php

/** Pure finite M88 SSR assertions; not a runtime adapter or browser-interaction substitute. */
final class M88LiveDiagnosticContract
{
    public const GRADED = [6, 6, 6, 6, 6, 6];
    public const FULL_INPUTS = [2, 2, 2, 2, 2, 2];
    public const DEMOS = [0, 0, 0, 0, 0, 0];

    public static function assertMistakeNotes(array $lessons): array
    {
        if (array_column($lessons, 'identity') !== App\Support\M88PastContinuousPresentPerfectBasics::OWNERS) {
            throw new RuntimeException('M88 diagnostic exact owner vector differs.');
        }
        $notes = 0;
        foreach ($lessons as $i => $lesson) {
            $slots = array_keys(array_filter($lesson['blocks'] ?? [], static fn ($b) => ($b['type'] ?? null) === 'mistakes-grid'));
            if ($slots !== [4] || count($lesson['blocks'][4]['data']['items'] ?? []) !== ($i === 5 ? 4 : 3)) {
                throw new RuntimeException('M88 diagnostic requires six exact slot4 mistake blocks: A-E three, F four.');
            }
            foreach ($lesson['blocks'][4]['data']['items'] as $n => $item) {
                if (array_key_exists('hint', $item)) {
                    throw new RuntimeException('M88 does not authorize the historical M85 hint aliases.');
                }
                foreach (['wrong_en', 'wrong_ua', 'right_en', 'right_ua', 'note'] as $field) {
                    if (!is_string($item[$field] ?? null) || trim($item[$field]) === '') {
                        throw new RuntimeException('M88 required paired mistake/note field missing: '.$i.':'.$n.':'.$field);
                    }
                }
                ++$notes;
            }
        }
        if ($notes !== 19) { throw new RuntimeException('M88 exact nineteen notes differ.'); }
        return ['mistake_blocks' => 6, 'required_notes' => 19, 'legacy_hint_aliases' => 0];
    }

    public static function assertPairedMistakeDom(DOMXPath $xp, DOMNode $point, array $item, callable $plain): array
    {
        $wrong = $xp->query('.//*[contains(concat(" ",normalize-space(@class)," ")," theory-example--wrong ")]//*[@lang="en"]', $point);
        $right = $xp->query('.//*[contains(concat(" ",normalize-space(@class)," ")," theory-example--right ")]//*[@lang="en"]', $point);
        $uk = $xp->query('.//p[@lang="uk" and contains(concat(" ",normalize-space(@class)," ")," theory-translation ")]', $point);
        if ($wrong->length !== 1 || $right->length !== 1 || $uk->length !== 2) {
            throw new RuntimeException('M88 needs two separate EN models and two separate paired UK nodes.');
        }
        foreach (['wrong_en' => $wrong->item(0), 'right_en' => $right->item(0), 'wrong_ua' => $uk->item(0), 'right_ua' => $uk->item(1)] as $field => $node) {
            if ($plain($node->textContent) !== $plain($item[$field])) {
                throw new RuntimeException('M88 paired mistake DOM field differs: '.$field);
            }
        }
        return ['english_nodes' => 2, 'ukrainian_nodes' => 2, 'ordered_fields_exact' => true];
    }

    /** DOMDocument drops @-prefixed Alpine attributes. Alias names only in a private parse copy. */
    public static function domAttribute(string $name): string
    {
        return [
            '@click' => 'data-m88-diagnostic-click',
            '@input' => 'data-m88-diagnostic-input',
            '@focus' => 'data-m88-diagnostic-focus',
            '@input.debounce.150ms' => 'data-m88-diagnostic-input-debounce',
            '@keydown.enter' => 'data-m88-diagnostic-keydown-enter',
            '@keydown.escape.stop.prevent' => 'data-m88-diagnostic-keydown-escape',
        ][$name] ?? $name;
    }

    /**
     * A finite HTML start-tag lexer, not a runtime/source rewrite.
     * Never rewrites attribute values, comments, text, or script/style/textarea/title bodies.
     * Existing diagnostic aliases are refused so the raw response cannot spoof a binding.
     */
    public static function prepareHtmlForDom(string $html): string
    {
        $eventNames = ['@click', '@input', '@focus', '@input.debounce.150ms', '@keydown.enter', '@keydown.escape.stop.prevent'];
        $aliases = array_map(self::domAttribute(...), $eventNames);
        $output = ''; $cursor = 0; $length = strlen($html);
        while (($start = strpos($html, '<', $cursor)) !== false) {
            $output .= substr($html, $cursor, $start - $cursor);
            if (substr($html, $start, 4) === '<!--') {
                $end = strpos($html, '-->', $start + 4);
                if ($end === false) { throw new RuntimeException('M88 diagnostic HTML comment is unterminated.'); }
                $output .= substr($html, $start, $end + 3 - $start); $cursor = $end + 3; continue;
            }
            if (preg_match('/\G<([A-Za-z][A-Za-z0-9:-]*)\b/A', $html, $match, 0, $start) !== 1) {
                $output .= '<'; $cursor = $start + 1; continue;
            }
            $tagName = strtolower($match[1]); $nameEnd = $start + strlen($match[0]);
            $quote = null; $end = $nameEnd;
            for (; $end < $length; ++$end) {
                $character = $html[$end];
                if ($quote !== null) { if ($character === $quote) { $quote = null; } continue; }
                if ($character === '"' || $character === "'") { $quote = $character; continue; }
                if ($character === '>') { break; }
            }
            if ($end === $length) { throw new RuntimeException('M88 diagnostic HTML start tag is unterminated.'); }
            $tag = substr($html, $start, $end + 1 - $start); $position = $nameEnd - $start;
            $rewritten = substr($tag, 0, $position); $tagLength = strlen($tag); $seenEvents = [];
            while ($position < $tagLength) {
                $gap = $position;
                while ($position < $tagLength && ctype_space($tag[$position])) { ++$position; }
                $rewritten .= substr($tag, $gap, $position - $gap);
                if ($position >= $tagLength || $tag[$position] === '>' || $tag[$position] === '/') {
                    $rewritten .= substr($tag, $position); break;
                }
                $attributeStart = $position;
                while ($position < $tagLength && !ctype_space($tag[$position]) && !str_contains('=/>"\'<', $tag[$position])) { ++$position; }
                if ($position === $attributeStart) { throw new RuntimeException('M88 diagnostic HTML attribute cannot be lexed.'); }
                $name = substr($tag, $attributeStart, $position - $attributeStart); $lower = strtolower($name);
                if (in_array($lower, $aliases, true)) { throw new RuntimeException('M88 raw HTML contains a forbidden diagnostic alias.'); }
                if (in_array($lower, $eventNames, true)) {
                    if (isset($seenEvents[$lower])) { throw new RuntimeException('M88 raw HTML contains an ambiguous duplicate event binding.'); }
                    $seenEvents[$lower] = true;
                }
                $rewritten .= in_array($lower, $eventNames, true) ? self::domAttribute($lower) : $name;
                $valueStart = $position;
                while ($position < $tagLength && ctype_space($tag[$position])) { ++$position; }
                if ($position < $tagLength && $tag[$position] === '=') {
                    ++$position; while ($position < $tagLength && ctype_space($tag[$position])) { ++$position; }
                    if ($position < $tagLength && ($tag[$position] === '"' || $tag[$position] === "'")) {
                        $valueQuote = $tag[$position++];
                        while ($position < $tagLength && $tag[$position] !== $valueQuote) { ++$position; }
                        if ($position === $tagLength) { throw new RuntimeException('M88 diagnostic HTML attribute value is unterminated.'); }
                        ++$position;
                    } else {
                        while ($position < $tagLength && !ctype_space($tag[$position]) && $tag[$position] !== '>') { ++$position; }
                    }
                }
                $rewritten .= substr($tag, $valueStart, $position - $valueStart);
            }
            $output .= $rewritten; $cursor = $end + 1;
            if (in_array($tagName, ['script', 'style', 'textarea', 'title'], true)) {
                if (preg_match('~</'.preg_quote($tagName, '~').'\s*>~i', $html, $closing, PREG_OFFSET_CAPTURE, $cursor) !== 1) {
                    throw new RuntimeException('M88 diagnostic raw-text element is unterminated.');
                }
                $after = $closing[0][1] + strlen($closing[0][0]);
                $output .= substr($html, $cursor, $after - $cursor); $cursor = $after;
            }
        }
        return $output.substr($html, $cursor);
    }

    /** Only four unchanged legacy generic questions are unused select metadata, not a hidden stimulus. */
    public static function classifyGenericSelectPrompt(int $ownerIndex, int $taskIndex, array $task, ?array $beforeTask): array
    {
        $labelPins = [
            4 => [
                '92be21dc97d99c267946f31d392b16b073f52888baabbf0acc3461d410f25a64',
                'b8428d02ef59fe0efc86627c69bef8f2be58b75b45d7216ef316f5459fe7c8e0',
            ],
            5 => [
                'b910d2472ee1e31dde1f1ce9c98f917d974de3d49725f69a7cc768d58b9e5a6f',
                '32febe4dd4e26ec2c1a27f900ef8e66307f3b9420943a8fc10d51e794a6fd311',
            ],
        ];
        $literal = 'Який варіант правильний?';
        if (!isset($labelPins[$ownerIndex][$taskIndex]) || ($task['prompt'] ?? null) !== $literal
            || ($beforeTask['prompt'] ?? null) !== $literal || !is_string($task['label'] ?? null)
            || !hash_equals($labelPins[$ownerIndex][$taskIndex], hash('sha256', $task['label']))) {
            throw new RuntimeException('M88 unsupported select prompt: unique stimuli, other owners or unbound labels require visible content review.');
        }
        return ['control' => 'selects/'.$taskIndex, 'source_prompt' => $literal,
            'before_prompt_sha256' => hash('sha256', $literal),
            'frozen_label_sha256' => $labelPins[$ownerIndex][$taskIndex],
            'classification' => 'unchanged generic non-teaching select metadata',
            'prompt_rendered_by_native_view' => false, 'prompt_visible_acceptance' => false,
            'teaching_criterion_location' => 'exact owned label', 'visible_owned_label_required' => true];
    }

    /** Exact data serialization and server-rendered criteria; no Check/Reset/click claims. */
    public static function assertPracticeDom(
        DOMXPath $xp, DOMElement $node, array $data, array $config, array $beforeConfig,
        int $ownerIndex, callable $learningText, callable $plain, callable $contains, callable $same,
        array $beforePractice = []
    ): array {
        $keys = ['selects', 'choices', 'inputs', 'rephrase'];
        $expectedCounts = [2, 2, 2, 0];
        $same(array_keys($config), array_keys($beforeConfig), 'Exact unchanged native config key vector');
        $same(array_diff_key($config, array_flip($keys)), array_diff_key($beforeConfig, array_flip($keys)), 'Independent BEFORE native non-learning config');
        foreach ($keys as $n => $group) {
            $same(count($data[$group] ?? []), $expectedCounts[$n], 'Exact frozen '.$group.' count');
            $same($config[$group] ?? null, $data[$group] ?? [], 'Exact serialized '.$group.' tasks/keys/aliases/feedback');
        }
        $groups = $xp->query('.//*[contains(concat(" ",normalize-space(@class)," ")," theory-exercise ")]', $node);
        $same($groups->length, 3, 'Exact native own exercise groups');
        $graded = 0; $full = 0; $demos = 0; $optionCount = 0; $rephraseCount = 0; $records = []; $genericSelectMetadata = [];
        foreach ($keys as $g => $group) {
            $tasks = $data[$group] ?? [];
            if ($tasks === []) { continue; }
            $groupNode = $groups->item($g);
            if (!$groupNode instanceof DOMElement) { throw new RuntimeException('M88 required exercise DOM group absent.'); }
            $text = $learningText($xp, $groupNode);
            $prefix = ['selects' => 'select', 'choices' => 'choice', 'inputs' => 'input', 'rephrase' => 'rephrase'][$group];
            foreach ([$prefix.'_title', $prefix.'_intro'] as $field) {
                $contains($text, $data[$field] ?? null, 'practice/'.$group.'/'.$field);
            }
            $labels = $xp->query('.//label', $groupNode);
            if (in_array($group, ['selects', 'choices'], true)) {
                $same($labels->length, count($tasks), 'One owned SSR label per '.$group.' control');
            }
            foreach ($tasks as $n => $task) {
                $control = $group.'/'.$n;
                if (!array_key_exists('answer', $task)) { throw new RuntimeException('M88 has no ungraded own demonstration.'); }
                $same(is_array($task['accepted'] ?? null) && in_array($task['answer'], $task['accepted'], true), true, 'Canonical explicitly accepted');
                foreach ($task['feedback'] ?? [] as $feedback) {
                    if (!is_string($feedback) || trim($feedback) === '' || preg_match('/<[^>]+>|\{a[0-9]+\}|&(?:#[0-9]+|#x[0-9a-f]+|[a-z][a-z0-9]+);/iu', $feedback) === 1) {
                        throw new RuntimeException('M88 feedback must remain plain authored text.');
                    }
                }
                if (in_array($group, ['selects', 'choices'], true)) {
                    $same($learningText($xp, $labels->item($n)), $plain($task['label']), $control.'/exact owned label');
                    if ($group === 'choices') {
                        $contains($text, $task['prompt'] ?? null, $control.'/optional separate prompt');
                    } elseif (array_key_exists('prompt', $task)) {
                        $metadata = self::classifyGenericSelectPrompt($ownerIndex, $n, $task, $beforePractice['selects'][$n] ?? null);
                        $metadata['visible_owned_label_exact'] = true; $genericSelectMetadata[] = $metadata;
                    }
                    $options = $task['options'] ?? ($data[$group === 'selects' ? 'options' : 'choice_options'] ?? ['a', 'b']);
                    $actualOptions = []; $binding = ($group === 'selects' ? 'selectAnswers' : 'choiceAnswers').'['.$n.'] = ';
                    foreach ($xp->query('.//button', $groupNode) as $button) {
                        if (str_starts_with($button->getAttribute(self::domAttribute('@click')), $binding)) { $actualOptions[] = $plain($button->textContent); }
                    }
                    $same($actualOptions, array_map($plain, $options), $control.'/ordered owned option texts');
                    $optionCount += count($options);
                } elseif ($group === 'inputs') {
                    $contains($text, $task['after'], $control.'/visible Ukrainian full-sentence criterion');
                    $contains($text, $task['prompt'] ?? null, $control.'/optional instruction');
                    $same(array_key_exists('hint', $task) || array_key_exists('placeholder', $task), false, 'No authored ready-answer input hint');
                    $same(str_contains($task['before'], '/'), true, 'Slash bank enables supported native token mode');
                    $nodes = $xp->query('.//*[@data-word-suggestion-input="inputs-'.$n.'"]', $groupNode);
                    $same($nodes->length, 1, $control.'/one owned manual input'); $input = $nodes->item(0);
                    foreach (['autocomplete' => 'off', 'autocorrect' => 'off', 'autocapitalize' => 'none', 'spellcheck' => 'false', 'placeholder' => '...', 'x-model' => 'inputAnswers['.$n.']', '@input' => 'syncInputTokenBank('.$n.')'] as $attr => $expected) {
                        $same($input->getAttribute(self::domAttribute($attr)), $expected, $control.'/native input '.$attr);
                    }
                    foreach (['value', 'list', '@focus', '@input.debounce.150ms', '@keydown.enter', '@keydown.escape.stop.prevent'] as $attr) {
                        $same($input->hasAttribute(self::domAttribute($attr)), false, $control.'/no answer value or suggestion binding '.$attr);
                    }
                    $same($xp->query('.//template[@x-for="token in inputTokenBank('.$n.')"]', $groupNode)->length, 1, $control.'/owned dynamic token-bank template');
                    ++$full;
                } else {
                    $contains($text, $task['original'], $control.'/visible correction criterion and source wording');
                    $nodes = $xp->query('.//*[@data-word-suggestion-input="rephrase-'.$n.'"]', $groupNode);
                    $same($nodes->length, 1, $control.'/one correction input');
                    $same($nodes->item(0)->getAttribute('placeholder'), $task['placeholder'] ?? '', $control.'/preserved non-answer placeholder');
                    ++$rephraseCount;
                }
                ++$graded;
                $records[] = ['control' => $control, 'graded' => true, 'serialized_task_exact' => true, 'owned_condition_SSR' => true];
            }
        }
        $same([$graded, $full, $demos], [self::GRADED[$ownerIndex], self::FULL_INPUTS[$ownerIndex], self::DEMOS[$ownerIndex]], 'Exact per-owner own/full/demo population');
        $same($xp->query('.//*[@data-word-suggestion-input and starts-with(@data-word-suggestion-input,"inputs-")]', $node)->length, $full, 'No extra full-sentence inputs');
        $same($xp->query('.//*[@data-word-suggestion-input and starts-with(@data-word-suggestion-input,"rephrase-")]', $node)->length, $rephraseCount, 'No extra scored rephrase inputs');
        return ['graded' => $graded, 'full_inputs' => $full, 'ungraded_demos' => $demos, 'rephrase_inputs' => $rephraseCount, 'option_texts' => $optionCount, 'groups' => $groups->length, 'controls' => $records, 'task_counts' => array_combine($keys, $expectedCounts), 'nonrendered_select_prompt_metadata' => $genericSelectMetadata];
    }
}
