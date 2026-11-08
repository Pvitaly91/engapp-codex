<?php

namespace App\Support;

use RuntimeException;
use Throwable;

/** Exact teaching-data projection for canonical theory only. Never mutates a model or database row. */
final class WordOrderPracticeSupplement
{
    public const SOURCE = 'docs/content/word-order-practice-supplement.v1.json';
    public const SOURCE_SHA = '0cd876d53f8a0a4db513166a7bdfc5758c14386ba96ae00f30bafdc7b4ead540';
    public const WIDGET_CASE_SOURCE = 'database/seeders/V3/Polyglot/PolyglotInversionAfterAdverbialsC1LessonSeeder/definition.json';
    public const WIDGET_CASE_SHA = 'aa9c318f9097bc1f8c2bc3caffd52b1b7ecf9debd677b40b776888e2e945f08a';

    private const TARGETS = [
        'basic' => ['BasicGrammar\\WordOrder\\BasicWordOrderTheorySeeder', 256, 48, '7cc46418-51ed-5f75-b880-9afda56b5739', 9, null, 'A1', 3],
        'questions-negatives' => ['BasicGrammar\\WordOrder\\WordOrderQuestionsNegativesTheorySeeder', 258, 48, '86092f77-4036-5f6e-b5cc-bde8c2111452', 9, null, 'A1', 3],
        'adverbs-adverbials' => ['BasicGrammar\\WordOrder\\WordOrderAdverbsAdverbialsTheorySeeder', 257, 48, 'f9d22b4c-40d7-52f2-9f3d-8d1466b804bf', 11, null, 'A2', 3],
        'verbs-objects' => ['BasicGrammar\\WordOrder\\WordOrderVerbsObjectsTheorySeeder', 259, 48, '801cbd62-190e-5ce6-9feb-92719d7e1d9a', 9, null, 'A2', 3],
        'advanced-emphasis' => ['BasicGrammar\\WordOrder\\AdvancedWordOrderEmphasisTheorySeeder', 255, 48, '47388a9c-40d0-589e-8584-f46b3af68f13', 10, null, 'B2', 3],
        'inversion-basics' => ['BasicGrammar\\WordOrder\\InversionBasicsTheorySeeder', 288, 48, '3f8d3e8b-396b-5182-a71d-4c0609aaac4e', 8, 'B2', 'B2', 2],
        'negative-adverbial-inversion' => ['BasicGrammar\\WordOrder\\InversionAfterNegativeAdverbialsTheorySeeder', 295, 48, 'ecf7cd80-5239-5fad-8e1c-8eaf9f2aaba5', 8, 'C1', 'C1', 2],
        'fronting-emphasis' => ['BasicGrammar\\WordOrder\\AdvancedFrontingAndEmphasisTheorySeeder', 313, 48, 'f20bb8f5-2bd7-58c5-9910-c5e0c70d7f0a', 8, 'C2', 'C2', 2],
        'transformations' => ['SentenceTransformations\\SentenceTransformationsWordOrderEmphasisTheorySeeder', 153, 23, 'fc22a53a-808d-58b6-af97-a53a6a5708d1', 7, 'B2', 'B2', 2],
        'common-mistakes' => ['CommonMistakes\\CommonMistakesWordOrderTheorySeeder', 43, 9, '61fb0ffb-5f95-55d4-b50c-730f2714b6da', 7, 'A2', 'A2', 2],
    ];

    private static function root(): string { return dirname(__DIR__, 2); }

    /** Explicit hints for the ten retained manual editing controls, not their demonstrations. */
    public static function retainedRephraseHints(string $topic): array
    {
        return match ($topic) {
            'basic' => [1 => 'Збережи підмет на початку та постав прислівник частотності перед основною дією.',
                2 => 'Почни з підмета; після дії та додатка постав місце, потім час.'],
            'questions-negatives' => [1 => 'У запереченні про теперішню звичку особове закінчення має допоміжне дієслово; повна й скорочена форми дозволені.',
                2 => 'У запереченні про минулу подію основне дієслово повертається до початкової форми; скорочення дозволене.'],
            'adverbs-adverbials' => [1 => 'Постав прислівник способу після всього додатка, не розриваючи їхнього зв’язку з дієсловом.',
                2 => 'Збережи початкове заперечне слово для підсилення; допоміжне дієслово має передувати підмету.'],
            'verbs-objects' => [1 => 'Після модального дієслова вміння постав початкову форму без інфінітивної частки.',
                2 => 'Займенниковий додаток множини постав між дієсловом і відокремлюваною часткою.'],
            'advanced-emphasis' => [1 => 'Початковий прислівник малої частотності потребує допоміжного дієслова перед підметом; збережи теперішню звичку.',
                2 => 'Інвертуй першу частину з минулим допоміжним дієсловом; другу залиш у заданому нейтральному порядку.'],
            default => [],
        };
    }

    public static function digest(array $value): string
    {
        return hash('sha256', json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
    }

    /** Immutable public teaching data only; timestamps, runtime identity and learner data are absent. */
    public static function load(): array
    {
        static $verified = null;
        $file = self::root().'/'.self::SOURCE;
        if (!is_file($file) || is_link($file) || !hash_equals(self::SOURCE_SHA, hash_file('sha256', $file))) {
            throw new RuntimeException('Word Order native supplement bytes differ.');
        }
        if ($verified !== null) {
            foreach ($verified['targets'] as $target) {
                foreach (['definition', 'author'] as $kind) {
                    $path = self::root().'/'.$target[$kind];
                    if (!is_file($path) || is_link($path) || hash_file('sha256', $path) !== $target[$kind.'_sha256']) {
                        throw new RuntimeException('Word Order cached native source pin differs.');
                    }
                }
            }
            return $verified;
        }
        $data = json_decode(file_get_contents($file), true, flags: JSON_THROW_ON_ERROR);
        self::validate($data);
        return $verified = $data;
    }

    /** Validate all finite before/after bindings, not just a truthy opt-in flag. */
    public static function validate(array $data): void
    {
        if (($data['schema_version'] ?? null) !== 1 || ($data['package'] ?? null) !== 'word-order-native-practice-supplement'
            || ($data['status'] ?? null) !== 'frozen' || array_column($data['targets'] ?? [], 'topic') !== array_keys(self::TARGETS)) {
            throw new RuntimeException('Word Order native finite scope differs.');
        }
        foreach ($data['targets'] as $target) {
            $topic = $target['topic'];
            [$suffix, $page, $category, $uuid, $order, $level, $focus, $oldCount] = self::TARGETS[$topic];
            $owner = 'Database\\Seeders\\Page_V3\\'.$suffix;
            $definitionPath = 'database/seeders/Page_V3/'.str_replace('\\', '/', $suffix).'/definition.json';
            $authorPath = 'docs/content/word-order-quality/'.$topic.'.v1.json';
            if (($target['definition'] ?? null) !== $definitionPath || ($target['author'] ?? null) !== $authorPath
                || ($target['focus_level'] ?? null) !== $focus) { throw new RuntimeException('Word Order native source path differs.'); }
            foreach ([$definitionPath => $target['definition_sha256'] ?? '', $authorPath => $target['author_sha256'] ?? ''] as $relative => $sha) {
                $file = self::root().'/'.$relative;
                if (!is_file($file) || is_link($file) || preg_match('/^[a-f0-9]{64}$/D', $sha) !== 1
                    || !hash_equals($sha, hash_file('sha256', $file))) { throw new RuntimeException('Word Order native source pin differs.'); }
            }
            $definition = json_decode(file_get_contents(self::root().'/'.$definitionPath), true, flags: JSON_THROW_ON_ERROR);
            $stored = $definition['page']['blocks'][$order - 1] ?? null;
            $binding = ['seeder' => $owner, 'page_id' => $page, 'page_category_id' => $category, 'uuid' => $uuid,
                'locale' => 'uk', 'type' => 'practice-set', 'column' => 'left', 'sort_order' => $order,
                'level' => $level, 'heading' => null, 'css_class' => null,
                'body_sha256' => is_array($stored) ? hash('sha256', $stored['body']) : ''];
            if (($target['binding'] ?? null) !== $binding || ($stored['type'] ?? null) !== 'practice-set'
                || ($stored['column'] ?? null) !== 'left' || ($stored['level'] ?? null) !== $level) {
                throw new RuntimeException('Word Order native exact block binding differs.');
            }
            $before = $target['before_data'] ?? null; $after = $target['after_data'] ?? null;
            if (!is_array($before) || !is_array($after)
                || self::digest($before) !== ($target['before_data_sha256'] ?? null)
                || self::digest($after) !== ($target['after_data_sha256'] ?? null)
                || $before != json_decode($stored['body'], true, flags: JSON_THROW_ON_ERROR)
                || count($before['inputs'] ?? []) !== $oldCount || count($target['input_updates'] ?? []) !== $oldCount
                || count($after['inputs'] ?? []) !== $oldCount + 2 || count($after['selects'] ?? []) !== count($before['selects'] ?? []) + 2) {
                throw new RuntimeException('Word Order native data/count fingerprint differs.');
            }
            $allowed = ['inputs', 'selects', 'rephrase', 'word_order_practice_supplement'];
            $retainedSelects = $before['selects'];
            if ($topic === 'inversion-basics') {
                $retainedSelects[0]['label'] .= '<p>Гід рідко говорить так швидко.</p>';
            }
            if (array_diff_key($before, array_flip($allowed)) !== array_diff_key($after, array_flip($allowed))
                || array_slice($after['selects'], 0, count($before['selects'])) !== $retainedSelects
                || ($after['word_order_practice_supplement'] ?? null) !== ['revision' => 'v1', 'topic' => $topic]) {
                throw new RuntimeException('Word Order retained tasks, links or source metadata changed.');
            }
            $retainedRephrase = $before['rephrase'] ?? [];
            foreach (self::retainedRephraseHints($topic) as $index => $hint) {
                self::assertUk($hint);
                if (!isset($retainedRephrase[$index]['answer'])) { throw new RuntimeException('Word Order editing hint must own a graded task.'); }
                $retainedRephrase[$index]['verb_hint'] = $hint;
                $retainedRephrase[$index]['original'] .= ' Підказка: '.$hint;
            }
            if (($after['rephrase'] ?? []) !== $retainedRephrase) {
                throw new RuntimeException('Word Order retained editing answer/ID/context differs.');
            }
            foreach ($target['input_updates'] as $i => $plan) {
                $old = $before['inputs'][$i]; $new = $after['inputs'][$i];
                if (($plan['index'] ?? null) !== $i || implode(' ', $plan['ordered_groups']) !== $old['answer']
                    || $plan['shuffled_groups'] === $plan['ordered_groups'] || !self::sameMultiset($plan['ordered_groups'], $plan['shuffled_groups'])) {
                    throw new RuntimeException('Word Order retained token plan differs.');
                }
                self::assertGroups($plan['ordered_groups']); self::assertUk($plan['hint_uk']);
                $expected = $old; $expected['before'] = implode(' / ', $plan['shuffled_groups']); $expected['verb_hint'] = $plan['hint_uk'];
                $oldAfter = $old['after'] ?? '';
                $expected['after'] = $oldAfter !== '' ? $oldAfter.'<p>Підказка: '.$plan['hint_uk'].'</p>'
                    : $plan['uk_condition_if_missing'].' Підказка: '.$plan['hint_uk'];
                if (isset($old['m37_token_groups'])) { $expected['m37_token_groups'] = $plan['shuffled_groups']; }
                if ($new !== $expected || !str_contains(strip_tags($new['after']), $plan['hint_uk'])
                    || ($oldAfter === '' && preg_match('/[А-Яа-яІіЇїЄєҐґ]/u', $plan['uk_condition_if_missing']) !== 1)) {
                    throw new RuntimeException('Word Order retained answer/ID/alias or visible UK hint differs.');
                }
            }
            $author = WordOrderPracticeQuality::source($topic);
            foreach (['gap' => 'selects', 'compose' => 'inputs'] as $kind => $group) {
                $sourceRows = array_slice(array_values(array_filter($author['questions'], static fn ($row) => $row['kind'] === $kind && $row['level'] === $focus)), 0, 2);
                $plans = $target['added'][$group] ?? [];
                if (count($sourceRows) !== 2 || count($plans) !== 2) { throw new RuntimeException('Word Order new focus pair missing.'); }
                foreach ($sourceRows as $i => $source) {
                    $id = 'woq-native-'.$topic.'-'.($kind === 'gap' ? 'gap' : 'translate').'-'.($i + 1);
                    $plan = $plans[$i]; $index = ($group === 'inputs' ? $oldCount : count($before['selects'])) + $i;
                    $item = $after[$group][$index];
                    if (($plan['id'] ?? null) !== $id || ($plan['source_id'] ?? null) !== $source['id']
                        || ($item['id'] ?? null) !== $id || ($item['practice_kind'] ?? null) !== ($kind === 'gap' ? 'gap' : 'translation')
                        || ($item['verb_hint'] ?? null) !== $source['hint_uk']) { throw new RuntimeException('Word Order new source identity differs.'); }
                    self::assertUk($item['verb_hint']);
                    if ($kind === 'gap') {
                        $accepted = array_values(array_unique(array_merge([$source['answer']], $source['answer_synonyms'] ?? [])));
                        $synonymNote = ($source['answer_synonyms'] ?? []) === [] ? '' : '<p>Допустимі синоніми: '.htmlspecialchars(implode(' / ', $source['answer_synonyms']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'.</p>';
                        $label = '<p>'.htmlspecialchars($source['uk'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'</p><p lang="en">'.htmlspecialchars(str_replace('{a1}', '____', $source['question']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'</p><p>'.$source['hint_uk'].'</p>'.$synonymNote;
                        $expectedItem = ['id' => $id, 'practice_kind' => 'gap', 'label' => $label, 'options' => $source['options'],
                            'answer' => $source['answer'], 'accepted' => $accepted, 'verb_hint' => $source['hint_uk'],
                            'feedback' => array_fill_keys(array_map('mb_strtolower', $accepted), 'Правильно. '.$source['hint_uk'])];
                        if ($item['options'] !== $source['options'] || $item['answer'] !== $source['answer']
                            || $item['accepted'] !== array_values(array_unique(array_merge([$source['answer']], $source['answer_synonyms'] ?? [])))
                            || !str_contains($item['label'], str_replace('{a1}', '____', $source['question']))
                            || preg_match('/\{a[0-9]+\}/u', $item['label']) === 1 || $item !== $expectedItem) { throw new RuntimeException('Word Order new gap differs.'); }
                    } else {
                        self::assertGroups($plan['ordered_groups']);
                        $accepted = array_values(array_unique(array_merge([$source['target']], $source['accepted_targets'] ?? [])));
                        $expectedItem = ['id' => $id, 'practice_kind' => 'translation', 'before' => implode(' / ', $plan['shuffled_groups']),
                            'after' => $source['question'].' Підказка: '.$source['hint_uk'], 'verb_hint' => $source['hint_uk'],
                            'answer' => $source['target'], 'accepted' => $accepted,
                            'feedback' => array_fill_keys(array_map('mb_strtolower', $accepted), 'Правильно. '.$source['hint_uk'])];
                        if ($plan['ordered_groups'] !== $source['groups'] || $plan['ordered_groups'] === $plan['shuffled_groups']
                            || !self::sameMultiset($plan['ordered_groups'], $plan['shuffled_groups'])
                            || $item['before'] !== implode(' / ', $plan['shuffled_groups']) || $item['answer'] !== $source['target']
                            || $item['accepted'] !== array_values(array_unique(array_merge([$source['target']], $source['accepted_targets'] ?? [])))
                            || $item['after'] !== $source['question'].' Підказка: '.$source['hint_uk']
                            || str_contains($item['after'], $source['target']) || preg_match('/[A-Za-z]/u', $item['after']) === 1 || $item !== $expectedItem) {
                            throw new RuntimeException('Word Order new full translation differs.');
                        }
                    }
                }
            }
            foreach ($target['input_token_labels'] ?? [] as $index => $labels) {
                $item = $after['inputs'][$index] ?? null;
                if (!is_array($item) || !is_array($labels)) { throw new RuntimeException('Word Order token label owner missing.'); }
                $values = explode(' / ', $item['before']);
                foreach ($labels as $value => $label) {
                    if (!in_array($value, $values, true) || !is_string($label) || !str_starts_with($label, $value.' (')
                        || !str_ends_with($label, ')') || preg_match('/<[^>]+>|\{a[0-9]+\}/u', $label) === 1) {
                        throw new RuntimeException('Word Order token label must preserve canonical value.');
                    }
                }
            }
        }
    }

    private static function assertUk(string $value): void
    {
        if (trim($value) === '' || preg_match('/[A-Za-z]|<[^>]+>|\{a[0-9]+\}|&(?:#[0-9]+|[a-z]+);/iu', $value) === 1
            || preg_match('/[А-Яа-яІіЇїЄєҐґ]/u', $value) !== 1) { throw new RuntimeException('Word Order hint must be plain Ukrainian.'); }
    }

    private static function assertGroups(array $groups): void
    {
        if (!array_is_list($groups) || count($groups) < 2) { throw new RuntimeException('Word Order bank needs multiple ordered groups.'); }
        foreach ($groups as $group) {
            if (!is_string($group) || trim($group) !== $group || $group === '' || count(preg_split('/\s+/u', $group)) > 3
                || str_contains($group, '/') || preg_match('/<[^>]+>|\{a[0-9]+\}/u', $group) === 1) {
                throw new RuntimeException('Word Order group must contain one to three words.');
            }
        }
    }

    private static function sameMultiset(array $a, array $b): bool { sort($a); sort($b); return $a === $b; }

    private static function matches(object $block, array $target): bool
    {
        foreach ($target['binding'] as $field => $value) {
            if ($field === 'body_sha256') { continue; }
            if (($block->$field ?? null) !== $value) { return false; }
        }
        return is_string($block->body ?? null) && hash_equals($target['binding']['body_sha256'], hash('sha256', $block->body));
    }

    private static function entry(object $block, array $data, bool $after): ?array
    {
        if (($block->locale ?? null) !== 'uk' || ($block->type ?? null) !== 'practice-set') { return null; }
        $owners = array_map(static fn ($spec) => 'Database\\Seeders\\Page_V3\\'.$spec[0], self::TARGETS);
        if (!in_array($block->seeder ?? null, $owners, true)) { return null; }
        try {
            foreach (self::load()['targets'] as $target) {
                if (self::matches($block, $target) && $data === $target[$after ? 'after_data' : 'before_data']) { return $target; }
            }
        } catch (Throwable) { /* Stale/foreign/incomplete data keeps the complete original practice. */ }
        return null;
    }

    public static function project(object $block, array $data, bool $canonicalCaller): array
    {
        if (!$canonicalCaller) { return $data; }
        $entry = self::entry($block, $data, false);
        return $entry['after_data'] ?? $data;
    }

    /** Finite opt-in for existing token-focus suppression, never a payload-only marker. */
    public static function isProjected(object $block, array $data): bool
    {
        return self::entry($block, $data, true) !== null;
    }

    /** Restore only the already proven original data for the existing finite display guards. */
    public static function originalDataForDisplay(object $block, array $data): array
    {
        $entry = self::entry($block, $data, true);
        return $entry['before_data'] ?? $data;
    }

    /** Labels never replace token values or accepted answers. */
    public static function inputTokenLabels(object $block, array $data): array
    {
        $entry = self::entry($block, $data, true);
        return $entry['input_token_labels'] ?? [];
    }

    /**
     * The shared option table is case-insensitively unique. For this finite old
     * bank only, restore its published token case (e.g. medial when), without
     * changing a question, answer value beyond case, revision or storage key.
     */
    public static function projectWidget(object $question, ?string $blockUuid, array $payload, bool $canonicalCaller = false): array
    {
        $spec = self::TARGETS['negative-adverbial-inversion'];
        $bank = 'Database\\Seeders\\V3\\Polyglot\\PolyglotInversionAfterAdverbialsC1LessonSeeder';
        if (!$canonicalCaller || app()->getLocale() !== 'uk' || $blockUuid !== $spec[3]
            || ($question->seeder ?? null) !== $bank || (string) ($question->type ?? '') !== '4'
            || ($question->level ?? null) !== 'C1' || ($payload['type'] ?? null) !== '4'
            || ($payload['level'] ?? null) !== 'C1' || ($payload['id'] ?? null) !== ($question->id ?? null)) { return $payload; }
        try {
            $path = self::root().'/'.self::WIDGET_CASE_SOURCE;
            if (!is_file($path) || is_link($path) || !hash_equals(self::WIDGET_CASE_SHA, hash_file('sha256', $path))) { return $payload; }
            $source = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
            if (($source['seeder']['class'] ?? null) !== $bank || ($source['defaults']['default_locale'] ?? null) !== 'uk'
                || count($source['questions'] ?? []) !== 48) { return $payload; }
            $uuidResolver = new \App\Support\Database\QuestionUuidResolver;
            $rows = array_values(array_filter($source['questions'], static fn ($row) =>
                $uuidResolver->toPersistent((string) ($row['uuid'] ?? '')) === ($question->uuid ?? null)));
            if (count($rows) !== 1) { return $payload; }
            $row = $rows[0]; $canonical = array_values($row['answers'] ?? []); $markers = array_keys($row['answers'] ?? []);
            if (($row['question'] ?? null) !== ($question->question ?? null) || ($payload['question'] ?? null) !== $row['question']
                || ($row['level'] ?? null) !== 'C1' || (string) ($row['type'] ?? '') !== '4'
                || $canonical !== ($row['tokens_correct'] ?? null) || count($canonical) < 2
                || !is_array($payload['answers'] ?? null) || !is_array($payload['correct_tokens'] ?? null)
                || !is_array($payload['options'] ?? null) || count($payload['answers']) !== count($canonical)
                || count($payload['correct_tokens']) !== count($canonical) || count($payload['options']) < count($canonical)) { return $payload; }
            // Do not trigger lazy relation queries from a render-only projection.
            if (method_exists($question, 'relationLoaded') && !$question->relationLoaded('answers')) { return $payload; }
            $loadedAnswers = collect($question->answers ?? [])->sortBy('marker', SORT_NATURAL)->values();
            if ($loadedAnswers->count() !== count($canonical)) { return $payload; }
            foreach ($canonical as $index => $token) {
                $answer = $payload['answers'][$index]; $loaded = $loadedAnswers[$index];
                if (!is_string($token) || ($answer['marker'] ?? null) !== $markers[$index]
                    || ($loaded->marker ?? null) !== $markers[$index]
                    || !is_string($loaded->option->option ?? null)
                    || !is_string($answer['correct'] ?? null) || !is_string($payload['correct_tokens'][$index])
                    || !is_string($payload['options'][$index])) { return $payload; }
                foreach ([$loaded->option->option, $answer['correct'], $payload['correct_tokens'][$index], $payload['options'][$index]] as $value) {
                    if (mb_strtolower($value) !== mb_strtolower($token)) { return $payload; }
                }
            }
            foreach ($canonical as $index => $token) {
                $payload['answers'][$index]['correct'] = $token;
                $payload['correct_tokens'][$index] = $token;
                $payload['options'][$index] = $token;
            }
            return $payload;
        } catch (Throwable) { return $payload; }
    }
}
