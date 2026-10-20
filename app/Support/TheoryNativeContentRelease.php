<?php

namespace App\Support;

use App\Models\Page;
use App\Models\TextBlock;
use Illuminate\Database\Eloquent\Collection;
use RuntimeException;

/**
 * Data-only boundary for reviewed native releases. Existing M49 serialisation
 * and canonical components are reused; a release cannot choose a view/style.
 * Historical packages retain their original implementation and frozen bytes.
 */
final class TheoryNativeContentRelease
{
    public function __construct(private readonly array $profile) {}

    private function fail(string $message): never
    {
        throw new RuntimeException($this->profile['label'].': '.$message);
    }

    public function load(?string $root = null): array
    {
        $root ??= base_path(); $out = [];
        foreach ($this->profile['artifacts'] as $path => $sha) {
            if (!is_string($sha) || preg_match('/^[a-f0-9]{64}$/D', $sha) !== 1
                || !is_file($root.'/'.$path) || is_link($root.'/'.$path)) {
                $this->fail('release is not reviewed/frozen: '.$path);
            }
            $bytes = file_get_contents($root.'/'.$path);
            if (!is_string($bytes) || !hash_equals($sha, hash('sha256', $bytes))) {
                $this->fail('immutable source differs: '.$path);
            }
            $out[] = json_decode($bytes, true, flags: JSON_THROW_ON_ERROR);
        }
        return $out;
    }

    /** Pure projection: only the unchanged accepted serializer owns HTML. */
    public function definition(array $lesson, array $before): array
    {
        $after = M49AdviceModalsPackage::definition($lesson, $this->projectedBefore($before));
        $rename = function (array $value) use (&$rename): array {
            $out = [];
            foreach ($value as $key => $child) {
                $key = match ($key) {
                    'm49_native_v1' => $this->profile['marker'],
                    'm49_detail_key' => $this->profile['detail_marker'],
                    default => $key,
                };
                $out[$key] = is_array($child) ? $rename($child) : $child;
            }
            return $out;
        };
        foreach ($after['page']['blocks'] as &$block) {
            $block['body'] = M26DetailPackage::json($rename(json_decode($block['body'], true, flags: JSON_THROW_ON_ERROR)));
            if (isset($block['uuid_key']) && str_starts_with($block['uuid_key'], 'm49:')) {
                $block['uuid_key'] = $this->profile['uuid_prefix'].substr($block['uuid_key'], 4);
            }
        }
        unset($block);
        return $after;
    }

    /** An opt-in, independently captured missing subtitle never becomes an empty old row. */
    public function subtitleWasAbsent(array $before): bool
    {
        $owners = array_column($this->profile['bindings'], 'identity');
        $index = array_search($before['seeder']['class'] ?? null, $owners, true);
        if ($index === false) { $this->fail('subtitle projection has a foreign owner.'); }
        $binding = $this->profile['bindings'][$index]; $capture = $binding['before_subtitle'] ?? [];
        if (($capture['mode'] ?? null) !== 'captured-absent') {
            if (array_key_exists('continuing_uuids', $binding)) { $this->fail('continuing UUID pins require a captured missing subtitle.'); }
            return false;
        }
        if (array_keys($capture) !== ['mode', 'source_html_present', 'source_text_present', 'page_text', 'subtitle_rows',
                'raw_page_title', 'extracted_title', 'h1']
            || $capture['source_html_present'] !== false || $capture['source_text_present'] !== false
            || $capture['page_text'] !== null || $capture['subtitle_rows'] !== 0 || $capture['extracted_title'] !== null
            || $capture['raw_page_title'] !== ($before['page']['title'] ?? null)
            || $capture['h1'] !== ($binding['rendered_title'] ?? null)
            || array_key_exists('subtitle_html', $before['page']) || array_key_exists('subtitle_text', $before['page'])
            || ($before['content_type'] ?? null) !== 'page' || ($before['type'] ?? null) !== 'theory'
            || ($before['slug'] ?? null) !== $binding['slug'] || ($before['page']['locale'] ?? null) !== 'uk'
            || ($before['page']['category']['slug'] ?? null) !== end($binding['ancestry'])) {
            $this->fail('independently captured absent subtitle/source identity differs.');
        }
        $pins = $binding['continuing_uuids'] ?? null; $blocks = $before['page']['blocks'] ?? [];
        if (!is_array($pins) || !array_is_list($pins) || $pins === [] || count($pins) !== count($blocks)
            || count($pins) !== count($binding['continuing_levels'])) { $this->fail('captured continuing UUID vector differs.'); }
        foreach ($pins as $slot => $uuid) {
            if (!is_string($uuid) || preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/D', $uuid) !== 1
                || $uuid !== M26DetailPackage::uuid($binding['identity'], $blocks[$slot], $slot)) {
                $this->fail('captured zero-counter continuing UUID differs.');
            }
        }
        if (count(array_unique($pins)) !== count($pins)) { $this->fail('captured continuing UUID repeats.'); }
        return true;
    }

    /** Explicit finite metadata transitions; the independent BEFORE remains unchanged. */
    public function projectedBefore(array $before): array
    {
        $owners = array_column($this->profile['bindings'], 'identity');
        $index = array_search($before['seeder']['class'] ?? null, $owners, true);
        if ($index === false) { $this->fail('metadata projection has a foreign owner.'); }
        $binding = $this->profile['bindings'][$index]; $projected = $before;
        if ($this->subtitleWasAbsent($before)) {
            foreach ($binding['continuing_uuids'] as $slot => $uuid) { $projected['page']['blocks'][$slot]['uuid'] = $uuid; }
        }
        $preserved = array_key_exists('preserved_native_metadata', $binding) ? $binding['preserved_native_metadata'] : [];
        if (!is_array($preserved)) { $this->fail('finite preserved native metadata declaration differs.'); }
        foreach ($preserved as $slot => $metadata) {
            if (!is_int($slot) || $slot < 0 || !isset($binding['continuing_metadata'][$slot])
                || !is_array($metadata) || array_keys($metadata) !== ['column', 'level']
                || !in_array($metadata['column'], ['left', 'right'], true) || $metadata['level'] !== null) {
                $this->fail('finite preserved native metadata slot/column/null level differs.');
            }
        }
        foreach ($binding['continuing_metadata'] ?? [] as $slot => $transition) {
            if (!is_int($slot) || $slot < 0 || !isset($before['page']['blocks'][$slot])
                || array_keys($transition) !== ['column', 'before', 'after']
                || array_keys($transition['before']) !== ['type', 'heading', 'level']
                || array_keys($transition['after']) !== ['type', 'heading', 'level']) {
                $this->fail('finite continuing metadata declaration differs.');
            }
            $old = $before['page']['blocks'][$slot];
            $preserve = $preserved[$slot] ?? null;
            if (($old['column'] ?? null) !== $transition['column']
                || ($preserve === null && $transition['column'] !== 'left')
                || ($preserve !== null && ($transition['column'] !== $preserve['column']
                    || ($old['type'] ?? null) !== 'box' || ($old['level'] ?? null) !== null
                    || $transition['before']['level'] !== null || $transition['after']['level'] !== null))) {
                $this->fail('finite metadata transition column differs.');
            }
            foreach ($transition['before'] as $field => $value) {
                if (($old[$field] ?? null) !== $value) { $this->fail('independent metadata transition BEFORE differs.'); }
            }
            foreach ($transition['after'] as $field => $value) {
                if ($value === null) { unset($projected['page']['blocks'][$slot][$field]); }
                else { $projected['page']['blocks'][$slot][$field] = $value; }
            }
            $new = $projected['page']['blocks'][$slot];
            if (!in_array($new['type'], ['forms-grid', 'usage-panels', 'comparison-table', 'mistakes-grid', 'summary-list'], true)
                || ($preserve === null ? !in_array($new['level'] ?? null, ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'], true)
                    : ($new['level'] ?? null) !== null)
                || M26DetailPackage::uuid($binding['identity'], $old, $this->subtitleWasAbsent($before) ? $slot : $slot + 1)
                    !== M26DetailPackage::uuid($binding['identity'], $new, $slot + 1)) {
                $this->fail('finite metadata transition changes identity or unsupported native type/level.');
            }
        }
        return $projected;
    }

    public function validate(array $before, array $patch, array $master, ?array $contract = null): void
    {
        $contract ??= $this->load()[3]; $bindings = $this->profile['bindings'];
        $owners = array_column($bindings, 'identity');
        foreach ([$before, $patch, $master] as $artifact) {
            if (($artifact['schema_version'] ?? null) !== 1 || ($artifact['base_sha'] ?? null) !== $this->profile['base_sha']) {
                $this->fail('schema/base differs.');
            }
        }
        if (($master['version'] ?? null) !== $this->profile['version'] || ($contract['schema_version'] ?? null) !== 1
            || array_column($before['targets'] ?? [], 'identity') !== $owners
            || array_column($patch['targets'] ?? [], 'identity') !== $owners
            || array_column($master['lessons'] ?? [], 'identity') !== $owners
            || array_column($contract['targets'] ?? [], 'identity') !== $owners) {
            $this->fail('exact independent owner/version/bank scope differs.');
        }
        $taskIds = []; $detailKeys = [];
        foreach ($master['lessons'] as $i => $lesson) {
            $binding = $bindings[$i]; $old = $before['targets'][$i];
            $target = $patch['targets'][$i]; $declaration = $contract['targets'][$i];
            $path = TheorySentenceBuilderAcceptance::definitionPath($binding['identity']);
            $pageBinding = ['id' => $binding['page_id'], 'page_category_id' => $binding['category_id']];
            if ($binding['page_id'] < 1 || $binding['category_id'] < 1 || ($old['path'] ?? null) !== $path
                || ($old['before']['seeder']['class'] ?? null) !== $binding['identity']
                || ($old['before']['content_type'] ?? null) !== 'page'
                || ($old['before']['slug'] ?? null) !== $binding['slug'] || ($old['before']['type'] ?? null) !== 'theory'
                || ($old['before']['page']['locale'] ?? null) !== 'uk'
                || ($old['before']['page']['category']['slug'] ?? null) !== end($binding['ancestry'])
                || ($lesson['locale'] ?? null) !== 'uk' || ($lesson['slug'] ?? null) !== $binding['slug']
                || ($lesson['technical_block_level'] ?? null) !== $binding['practice_level']
                || ($lesson['display_level'] ?? null) !== $binding['display_level']
                || ($lesson['blocks'][0]['data']['level'] ?? null) !== $binding['display_level']
                || ($target['path'] ?? null) !== $path || ($target['slug'] ?? null) !== $binding['slug']
                || ($target['ancestry'] ?? null) !== $binding['ancestry'] || ($target['page_binding'] ?? null) !== $pageBinding
                || ($target['local_url'] ?? null) !== $binding['local_url'] || ($declaration['definition'] ?? null) !== $path
                || ($declaration['local_url'] ?? null) !== $binding['local_url']
                || ($declaration['level'] ?? null) !== $binding['practice_level']
                || ($target['after'] ?? null) !== $this->definition($lesson, $old['before'])
                || ($target['block_keys'] ?? null) !== array_column($lesson['blocks'], 'key')) {
                $this->fail('owner/category/locale/display or exact projection differs.');
            }
            $subtitleAbsent = $this->subtitleWasAbsent($old['before']);
            if ($subtitleAbsent) {
                $metadata = array_values(array_filter($master['editorial_review']['pre_apply_metadata'] ?? [],
                    static fn ($record) => ($record['identity'] ?? null) === $binding['identity']));
                if (count($metadata) !== 1) { $this->fail('independent absent subtitle metadata is missing.'); }
                foreach (['source_html_present', 'source_text_present', 'page_text', 'subtitle_rows', 'extracted_title', 'h1'] as $field) {
                    if (!array_key_exists($field, $metadata[0]) || $metadata[0][$field] !== $binding['before_subtitle'][$field]) {
                        $this->fail('independent absent subtitle metadata differs.');
                    }
                }
            }
            $subtitles = $subtitleAbsent ? [$lesson['subtitle_html'] ?? '']
                : [$old['before']['page']['subtitle_html'], $lesson['subtitle_html'] ?? ''];
            if (array_key_exists('after_subtitle', $binding)) {
                $this->assertPlainFallbackSubtitle($old['before'], $binding, $lesson['subtitle_html'] ?? '', $master);
                $subtitles = []; // Only this exact reviewed missing-subtitle owner opts out of a strong title.
            }
            foreach ($subtitles as $phase => $subtitle) {
                if (!$subtitleAbsent && $phase === 0 && isset($binding['before_subtitle'])) {
                    $capture = $binding['before_subtitle'];
                    $metadata = array_values(array_filter($master['editorial_review']['pre_apply_metadata'] ?? [],
                        static fn ($record) => ($record['identity'] ?? null) === $binding['identity']));
                    if (($capture['mode'] ?? null) !== 'captured-plain'
                        || preg_match('/^[a-f0-9]{64}$/D', $capture['sha256'] ?? '') !== 1
                        || !hash_equals($capture['sha256'], hash('sha256', $subtitle))
                        || ($capture['raw_page_title'] ?? null) !== $old['before']['page']['title']
                        || ($capture['extracted_title'] ?? null) !== $binding['rendered_title']
                        || ($capture['h1'] ?? null) !== $binding['rendered_title']
                        || count($metadata) !== 1 || ($metadata[0]['h1'] ?? null) !== $binding['rendered_title']) {
                        $this->fail('independently captured plain subtitle/title differs.');
                    }
                    continue;
                }
                if (preg_match('/^\s*<p>\s*<strong>(.*?)<\/strong>/su', $subtitle, $title) !== 1
                    || html_entity_decode(strip_tags($title[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8') !== $binding['rendered_title']) {
                    $this->fail('supported subtitle changed the independently captured display title.');
                }
            }
            $count = count($old['before']['page']['blocks']);
            $appended = $binding['appended_blocks'] ?? null; $addedCount = $appended === null ? 1 : count($appended);
            $projectedBefore = $this->projectedBefore($old['before']);
            if (($addedCount < 1 && !$this->existingPractice($binding, $old['before'])) || $count !== count($binding['continuing_levels'])
                || count($lesson['blocks']) !== $count + $addedCount
                || array_column($lesson['blocks'], 'existing_source_index') !== [...range(0, $count - 1), ...array_fill(0, $addedCount, null)]
                || count(array_unique(array_column($lesson['blocks'], 'key'))) !== $count + $addedCount) {
                $this->fail('actual continuing slots/order or appended practice differs.');
            }
            if ($appended !== null) {
                foreach ($appended as $offset => $config) {
                    $source = $lesson['blocks'][$count + $offset];
                    if (array_keys($config) !== ['key', 'type', 'column', 'level']
                        || ['key' => $source['key'] ?? null, 'type' => $source['type'] ?? null,
                            'column' => $source['column'] ?? null, 'level' => $source['level'] ?? null] !== $config
                        || preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/D', $config['key']) !== 1
                        || !in_array($config['type'], ['forms-grid', 'usage-panels', 'comparison-table', 'mistakes-grid',
                            'summary-list', 'navigation-chips', 'practice-set'], true)
                        || ($config['type'] === 'practice-set') !== ($offset === $addedCount - 1)
                        || ($config['type'] === 'navigation-chips' ? ($config['column'] !== 'footer' || $config['level'] !== null)
                            : ($config['column'] !== 'left' || $config['level'] !== $binding['practice_level']))) {
                        $this->fail('finite appended teaching/practice metadata differs.');
                    }
                }
            }
            foreach ($binding['component_overrides'] ?? [] as $slot => $component) {
                if (!is_int($slot) || $slot < 0 || $slot >= $count || $component !== 'usage-panels') {
                    $this->fail('finite semantic component slot/type differs.');
                }
            }
            $protected = $target['after']; $protected['page']['blocks'] = $old['before']['page']['blocks'];
            foreach (['subtitle_html', 'subtitle_text'] as $field) {
                if (array_key_exists($field, $old['before']['page'])) { $protected['page'][$field] = $old['before']['page'][$field]; }
                else { unset($protected['page'][$field]); }
            }
            if ($protected !== $old['before']) { $this->fail('protected raw title/slug/category/tags metadata changed.'); }
            foreach ($lesson['blocks'] as $slot => $block) {
                $override = $binding['component_overrides'][$slot] ?? null;
                if ($override !== null && ($override !== 'usage-panels' || $block['type'] !== 'mistakes-grid'
                    || ($block['semantic_component'] ?? null) !== $override
                    || !is_array($block['data']['sections'] ?? null) || $block['data']['sections'] === []
                    || array_key_exists('items', $block['data']) || $this->hasCorrectionFields($block['data']))) {
                    $this->fail('finite neutral component projection differs.');
                }
                if ($override === null && array_key_exists('semantic_component', $block)) {
                    $this->fail('unregistered semantic component projection.');
                }
                $expectedLevel = $slot < $count ? ($projectedBefore['page']['blocks'][$slot]['level'] ?? null)
                    : ($appended === null ? $binding['practice_level'] : $appended[$slot - $count]['level']);
                if (($block['level'] ?? null) !== $expectedLevel) { $this->fail('exact per-slot technical CEFR differs.'); }
                if ($slot < $count) {
                    $oldConfig = $projectedBefore['page']['blocks'][$slot]; $newConfig = $target['after']['page']['blocks'][$slot];
                    if (($old['before']['page']['blocks'][$slot]['level'] ?? null) !== $binding['continuing_levels'][$slot]) {
                        $this->fail('independent BEFORE level vector differs.');
                    }
                    unset($oldConfig['body'], $newConfig['body']);
                    if ($oldConfig !== $newConfig) { $this->fail('continuing metadata/UUID/order changed.'); }
                }
                $this->assertSections($block['data']['sections'] ?? []);
                if ($block['type'] === 'navigation-chips') {
                    foreach ($block['data']['items'] ?? [] as $item) {
                        if (empty($item['current']) && (!is_string($item['url'] ?? null)
                            || preg_match('~^/theory/[a-z0-9-]+(?:/[a-z0-9-]+)*$~D', $item['url']) !== 1)) {
                            $this->fail('navigation needs an explicit supported local destination.');
                        }
                    }
                }
                if ($block['type'] === 'mistakes-grid' && $override === null) {
                    foreach ($block['data']['items'] ?? [] as $item) {
                        foreach (['wrong_en', 'wrong_ua', 'right_en', 'right_ua'] as $field) {
                            if (!is_string($item[$field] ?? null) || trim($item[$field]) === '') {
                                $this->fail('paired correction translation is missing.');
                            }
                        }
                    }
                }
            }
            $practices = array_values(array_filter($lesson['blocks'], static fn ($block) => $block['type'] === 'practice-set'));
            if (count($practices) !== 1) { $this->fail('exactly one appended native practice-set is required.'); }
            $practice = $practices[0];
            if ($practice['type'] !== 'practice-set' || $practice['column'] !== 'left') {
                $this->fail('one appended native practice-set is required.');
            }
            $this->assertPractice($practice['data'], $declaration['bank_seeders'] ?? [], $taskIds, $binding['practice_contract'] ?? null);
            $pointOwners = []; $byKey = array_column($lesson['blocks'], null, 'key');
            foreach ($lesson['details'] as $detail) {
                $owner = ($detail['owner_block_key'] ?? '').':'.($detail['owner_pointer'] ?? '');
                $ownerBlock = $byKey[$detail['owner_block_key'] ?? ''] ?? null;
                $field = ['forms-grid' => 'items', 'usage-panels' => 'sections'][$ownerBlock['type'] ?? ''] ?? null;
                $pointer = explode('/', $detail['owner_pointer'] ?? '');
                if (!is_string($detail['key'] ?? null) || $detail['key'] === '' || isset($detailKeys[$detail['key']])
                    || isset($pointOwners[$owner]) || count($pointer) !== 3 || $pointer[1] !== $field || !ctype_digit($pointer[2])
                    || !isset($ownerBlock['data'][$field][(int) $pointer[2]])
                    || ($detail['fragment']['type'] ?? null) !== 'usage-panels' || empty($detail['fragment']['data']['sections'])) {
                    $this->fail('duplicate/incomplete semantic detail owner.');
                }
                $detailKeys[$detail['key']] = true; $pointOwners[$owner] = true;
                $this->assertSections($detail['fragment']['data']['sections']);
                foreach ($detail['fragment']['data']['sections'] as $section) {
                    if (empty($section['description']) || empty($section['examples'])) { $this->fail('meaningful detail is incomplete.'); }
                }
            }
            $this->assertLearningData($lesson['blocks']); $this->assertLearningData($lesson['details']);
        }
    }

    /** A finite plain AFTER paragraph preserves a captured raw-title fallback, never a new title heuristic. */
    private function assertPlainFallbackSubtitle(array $before, array $binding, string $subtitle, array $master): void
    {
        $after = $binding['after_subtitle'];
        if (!$this->subtitleWasAbsent($before) || !is_array($after)
            || array_keys($after) !== ['mode', 'sha256', 'extracted_title', 'h1']
            || $after['mode'] !== 'plain-fallback' || !is_string($after['sha256'])
            || preg_match('/^[a-f0-9]{64}$/D', $after['sha256']) !== 1
            || !hash_equals($after['sha256'], hash('sha256', $subtitle))
            || $after['extracted_title'] !== null || $after['h1'] !== $before['page']['title']
            || $after['h1'] !== $binding['rendered_title']
            || preg_match('/^<p>([^<>]+)<\/p>$/suD', $subtitle, $paragraph) !== 1) {
            $this->fail('exact plain fallback subtitle declaration/body differs.');
        }
        $plain = html_entity_decode($paragraph[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if (str_contains($plain, '<') || str_contains($plain, '>') || mb_strlen(trim($plain)) <= 110
            || preg_match('/[\x{0400}-\x{04FF}]/u', $plain) !== 1) {
            $this->fail('plain fallback subtitle must be one meaningful non-heading Ukrainian paragraph.');
        }
        $metadata = array_values(array_filter($master['editorial_review']['post_apply_metadata'] ?? [],
            static fn ($record) => ($record['identity'] ?? null) === $binding['identity']));
        if (count($metadata) !== 1 || $metadata[0] !== ['identity' => $binding['identity']] + $after) {
            $this->fail('explicit plain fallback subtitle metadata proof differs.');
        }
    }

    private function hasCorrectionFields(array $data): bool
    {
        foreach ($data as $key => $value) {
            if (in_array($key, ['wrong', 'right', 'wrong_en', 'wrong_ua', 'right_en', 'right_ua'], true)
                || (is_array($value) && $this->hasCorrectionFields($value))) { return true; }
        }
        return false;
    }

    /** A continuing practice keeps its captured UUID, slot and technical level. */
    public function existingPractice(array $binding, array $before): bool
    {
        $pin = $binding['existing_practice'] ?? null;
        if ($pin === null) { return false; }
        $slot = $pin['source_index'] ?? null; $source = is_int($slot) ? ($before['page']['blocks'][$slot] ?? null) : null;
        if (!is_array($pin) || array_keys($pin) !== ['source_index', 'uuid', 'level']
            || ($binding['appended_blocks'] ?? null) !== [] || !is_array($source)
            || ($source['type'] ?? null) !== 'practice-set' || ($source['column'] ?? null) !== 'left'
            || ($source['level'] ?? null) !== $pin['level'] || $pin['level'] !== $binding['practice_level']
            || $pin['uuid'] !== M26DetailPackage::uuid($binding['identity'], $source, $slot + 1)
            || count(array_filter($before['page']['blocks'], static fn ($block) => $block['type'] === 'practice-set')) !== 1) {
            $this->fail('captured continuing practice identity/level differs.');
        }
        return true;
    }

    private function assertPractice(array $data, array $banks, array &$ids, ?array $contract = null): void
    {
        $counts = ['selects'=>2, 'choices'=>2, 'inputs'=>2]; $sentences = [0, 1];
        if ($contract !== null) {
            if (array_keys($contract) !== ['counts', 'sentence_input_indices']
                || array_keys($contract['counts'] ?? []) !== array_keys($counts)
                || array_filter($contract['counts'], static fn ($n) => !is_int($n) || $n < 2)
                || !is_array($contract['sentence_input_indices']) || count($contract['sentence_input_indices']) < 2
                || count(array_unique($contract['sentence_input_indices'])) !== count($contract['sentence_input_indices'])) {
                $this->fail('finite retained-practice contract differs.');
            }
            $counts = $contract['counts']; $sentences = $contract['sentence_input_indices'];
            foreach ($sentences as $index) {
                if (!is_int($index) || $index < 0 || $index >= $counts['inputs']) { $this->fail('sentence input index differs.'); }
            }
        }
        if ($banks === [] || count(array_unique($banks)) !== count($banks)
            || count($data['selects'] ?? []) !== $counts['selects'] || count($data['choices'] ?? []) !== $counts['choices'] || count($data['inputs'] ?? []) !== $counts['inputs']
            || ($contract === null && array_column([...$data['selects'], ...$data['choices'], ...$data['inputs']], 'source_index') !== range(1, 6))
            || ($data['linked_practice']['source'] ?? null) !== 'theory_links'
            || ($data['linked_practice']['question_types'] ?? null) !== ['4']
            || ($data['linked_practice']['seeder_classes'] ?? null) !== $banks) {
            $this->fail('own 2/2/2 groups or independent thematic banks differ.');
        }
        foreach ($banks as $bank) {
            if (!is_string($bank) || preg_match('/^Database\\\\Seeders\\\\V3\\\\[A-Za-z0-9_]+\\\\[A-Za-z0-9_]+$/D', $bank) !== 1) {
                $this->fail('declared bank identity is invalid.');
            }
        }
        foreach (['selects', 'choices', 'inputs'] as $group) {
            foreach ($data[$group] as $task) {
                $id = $task['id'] ?? null;
                if (($id === null && $contract === null) || ($id !== null && (!is_string($id) || $id === '' || isset($ids[$id])))
                    || !in_array($task['answer'] ?? null, $task['accepted'] ?? [], true)) { $this->fail('task ID/canonical answer differs.'); }
                if ($id !== null) { $ids[$id] = true; }
                foreach ($task['options'] ?? ($group === 'choices' ? ($data['choice_options'] ?? ['a', 'b']) : $task['accepted']) as $option) {
                    if (!is_string($option) || !is_string($task['feedback'][mb_strtolower(trim($option))] ?? null)) {
                        $this->fail('supported per-option feedback is missing.');
                    }
                }
            }
        }
        foreach ($sentences as $index) {
            $input = $data['inputs'][$index];
            if (!is_string($input['before'] ?? null) || !str_contains($input['before'], '/')
                || !is_string($input['id'] ?? null) || $input['id'] === ''
                || !is_string($input['after'] ?? null) || preg_match('/[\x{0400}-\x{04FF}]/u', $input['after']) !== 1) {
                $this->fail('visible Ukrainian input condition or native bank is missing.');
            }
        }
    }

    private function assertSections(array $sections): void
    {
        foreach ($sections as $section) {
            if (empty($section['label']) || !in_array($section['color'] ?? null, ['blue', 'emerald', 'sky', 'amber', 'rose', 'slate'], true)) {
                $this->fail('unnamed native point or unsupported semantic accent.');
            }
            foreach ($section['examples'] ?? [] as $example) {
                if (empty($example['en']) || empty($example['ua'])) { $this->fail('bilingual example is missing.'); }
            }
        }
    }

    private function assertLearningData(array $values): void
    {
        foreach ($values as $key => $value) {
            if (is_string($key) && in_array(strtolower($key), ['view', 'native_view', 'stylesheet', 'css_class', 'class', 'style'], true)) {
                $this->fail('learning data cannot select presentation.');
            }
            if (is_array($value)) { $this->assertLearningData($value); }
            elseif (is_string($value) && preg_match('~<[^>]*\s(?:class|style|on[a-z]+)\s*=|<(?:script|style|iframe)\b~i', $value)) {
                $this->fail('learning data contains executable/styled markup.');
            }
        }
    }

    private function binding(object $block, array $data): ?array
    {
        $owners = array_column($this->profile['bindings'], 'identity');
        if (($block->locale ?? null) !== 'uk' || !in_array($block->seeder ?? null, $owners, true)
            || !isset($data[$this->profile['marker']])) { return null; }
        try {
            [$before, $patch, $master, $contract] = $this->load(); $this->validate($before, $patch, $master, $contract);
            $i = array_search($block->seeder, $owners, true); $target = $patch['targets'][$i];
            if (($block->page_id ?? null) !== $target['page_binding']['id']
                || ($block->page_category_id ?? null) !== $target['page_binding']['page_category_id']) { return null; }
            foreach ($target['after']['page']['blocks'] as $slot => $source) {
                if (($block->uuid ?? null) !== M26DetailPackage::uuid($target['identity'], $source, $slot + 1)) { continue; }
                foreach (['type', 'column', 'heading', 'level', 'css_class'] as $field) {
                    if (($block->$field ?? null) !== ($source[$field] ?? null)) { return null; }
                }
                if ((int) ($block->sort_order ?? -1) !== $slot + 1 || ($block->body ?? null) !== $source['body']
                    || $data !== json_decode($source['body'], true, flags: JSON_THROW_ON_ERROR)) { return null; }
                return ['target' => $target, 'lesson' => $master['lessons'][$i], 'source' => $source, 'slot' => $slot];
            }
        } catch (\Throwable) { /* Full stored fallback stays available on any stale/foreign source. */ }
        return null;
    }

    private function design(array $binding): array
    {
        $owner = array_search($binding['target']['identity'], array_column($this->profile['bindings'], 'identity'), true);
        $override = $this->profile['bindings'][$owner]['component_overrides'][$binding['slot']] ?? null;
        return ['package' => $this->profile['design'], 'identity' => $binding['target']['identity'], 'locale' => 'uk',
            'uuid' => M26DetailPackage::uuid($binding['target']['identity'], $binding['source'], $binding['slot'] + 1),
            'source_index' => $binding['slot'], 'body_sha256' => hash('sha256', $binding['source']['body']),
            'component' => $override ?? $binding['source']['type']]
            + ($override === null ? [] : ['stored_component' => $binding['source']['type']]);
    }

    public function presentation(object $block, array $data): ?array
    {
        if (app()->getLocale() !== 'uk') { return null; }
        $binding = $this->binding($block, $data); if ($binding === null) { return null; } $points = [];
        foreach ($binding['lesson']['details'] as $detail) {
            if ($detail['owner_block_key'] !== $data[$this->profile['marker']]['key']) { continue; }
            $index = (int) explode('/', $detail['owner_pointer'])[2];
            $points[$index] = ['key' => $detail['key'], 'title' => $detail['title'], 'fragments' => array_map(
                static fn ($section, $n) => ['id' => $detail['key'].'-detail-'.$n, 'type' => 'usage-panels', 'value' => $section],
                $detail['fragment']['data']['sections'], array_keys($detail['fragment']['data']['sections']))];
        }
        return ['data' => $binding['lesson']['blocks'][$binding['slot']]['data'] + [$this->profile['marker'] => $data[$this->profile['marker']]],
            'points' => $points, 'legacy_section' => null, 'legacy_practice_id' => null, 'm42_native_design' => $this->design($binding)];
    }

    public function validDesign(?array $design): bool
    {
        if (($design['package'] ?? null) !== $this->profile['design']) { return false; }
        try {
            [$before, $patch, $master, $contract] = $this->load(); $this->validate($before, $patch, $master, $contract);
            foreach ($patch['targets'] as $target) {
                foreach ($target['after']['page']['blocks'] as $slot => $source) {
                    if ($design === $this->design(compact('target', 'source', 'slot'))) { return true; }
                }
            }
        } catch (\Throwable) {}
        return false;
    }

    public function pointPanels(?array $design): ?array
    {
        if (!$this->validDesign($design) || $design['component'] !== 'usage-panels') { return null; }
        [, , $master] = $this->load();
        $i = array_search($design['identity'], array_column($this->profile['bindings'], 'identity'), true); $points = [];
        foreach ($master['lessons'][$i]['blocks'][$design['source_index']]['data']['sections'] as $n => $section) {
            $points[$n] = ['label' => $section['label'], 'accent' => $section['color'],
                'description_sha256' => [hash('sha256', $section['description'])]];
        }
        return ['points_by_index' => $points];
    }

    public function correction(array $item, ?array $design): ?array
    {
        if (!$this->validDesign($design) || $design['component'] !== 'mistakes-grid') { return null; }
        [, $patch] = $this->load(); $i = array_search($design['identity'], array_column($this->profile['bindings'], 'identity'), true);
        $items = json_decode($patch['targets'][$i]['after']['page']['blocks'][$design['source_index']]['body'], true)['items'];
        if (!in_array($item, $items, true)) { return null; }
        return ['wrong_en' => TheoryInlineHtml::render($item['wrong_en']), 'wrong_uk' => TheoryInlineHtml::render($item['wrong_ua']),
            'right_en' => TheoryInlineHtml::render($item['right_en']), 'right_uk' => TheoryInlineHtml::render($item['right_ua']),
            'hint_html' => TheoryInlineHtml::render($item['note'] ?? '')];
    }

    private function exactSubtitle(object $block, array $target, array $old): bool
    {
        $source = ['type' => 'subtitle', 'column' => 'header', 'heading' => null, 'css_class' => null,
            'level' => $old['page']['subtitle_level'] ?? null, 'uuid_key' => $old['page']['subtitle_uuid_key'] ?? 'subtitle'];
        $expected = ['uuid' => M26DetailPackage::uuid($target['identity'], $source, 0), 'page_id' => $target['page_binding']['id'],
            'page_category_id' => $target['page_binding']['page_category_id'], 'seeder' => $target['identity'], 'locale' => 'uk',
            'sort_order' => 0, 'type' => 'subtitle', 'column' => 'header', 'heading' => null, 'css_class' => null,
            'level' => $source['level'], 'body' => $target['after']['page']['subtitle_html']];
        foreach ($expected as $field => $value) { if (($block->$field ?? null) !== $value) { return false; } }
        return true;
    }

    private function cloneBody(object $block, string $body): object
    {
        $copy = clone $block;
        if (method_exists($copy, 'setRawAttributes') && method_exists($copy, 'getAttributes')) {
            $copy->setRawAttributes(array_replace($copy->getAttributes(), ['body' => $body]), true);
        } else { $copy->body = $body; }
        return $copy;
    }

    private function cloneConfig(object $block, array $config): object
    {
        $copy = clone $block; $fields = ['body' => $config['body']];
        foreach (['type', 'column', 'heading', 'level', 'css_class'] as $field) { $fields[$field] = $config[$field] ?? null; }
        if (method_exists($copy, 'setRawAttributes') && method_exists($copy, 'getAttributes')) {
            $copy->setRawAttributes(array_replace($copy->getAttributes(), $fields), true);
        } else { foreach ($fields as $field => $value) { $copy->$field = $value; } }
        return $copy;
    }

    public function preserveExcludedTextBlock(object $block, string $caller): ?object
    {
        $owners = array_column($this->profile['bindings'], 'identity');
        if ($caller !== 'standalone_test' || ($block->locale ?? null) !== 'uk'
            || !in_array($block->seeder ?? null, $owners, true)) { return $block; }
        try {
            [$before, $patch, $master, $contract] = $this->load(); $this->validate($before, $patch, $master, $contract);
            $i = array_search($block->seeder, $owners, true); $target = $patch['targets'][$i]; $old = $before['targets'][$i]['before'];
            if (($block->page_id ?? null) !== $target['page_binding']['id']
                || ($block->page_category_id ?? null) !== $target['page_binding']['page_category_id']) { return $block; }
            if ($block->type === 'subtitle') {
                if (!$this->exactSubtitle($block, $target, $old)) { return $block; }
                return $this->subtitleWasAbsent($old) ? null : $this->cloneBody($block, $old['page']['subtitle_html']);
            }
            $binding = $this->binding($block, json_decode((string) $block->body, true, flags: JSON_THROW_ON_ERROR));
            if ($binding === null) { return $block; }
            if ($binding['slot'] >= count($old['page']['blocks'])) { return null; }
            return $this->cloneConfig($block, $old['page']['blocks'][$binding['slot']]);
        } catch (\Throwable) { return $block; }
    }

    public function preserveCoursePage(?Page $page): ?Page
    {
        $owners = array_column($this->profile['bindings'], 'identity');
        if ($page === null || !in_array($page->getRawOriginal('seeder'), $owners, true) || !$page->relationLoaded('textBlocks')) { return $page; }
        try {
            [$before, $patch, $master, $contract] = $this->load(); $this->validate($before, $patch, $master, $contract);
            $i = array_search($page->getRawOriginal('seeder'), $owners, true); $target = $patch['targets'][$i];
            $old = $before['targets'][$i]['before']; $blocks = $page->getRelation('textBlocks');
            $expected = ['id' => $target['page_binding']['id'], 'page_category_id' => $target['page_binding']['page_category_id'],
                'seeder' => $target['identity'], 'slug' => $target['slug'], 'title' => $old['page']['title'], 'type' => 'theory',
                'text' => $target['after']['page']['subtitle_text']];
            foreach ($expected as $field => $value) {
                if ($page->getRawOriginal($field) !== $value || $page->getAttribute($field) !== $value) { return $page; }
            }
            $uk = $blocks->where('locale', 'uk')->values();
            if ($uk->count() !== count($target['after']['page']['blocks']) + 1) { return $page; }
            foreach ($target['after']['page']['blocks'] as $slot => $source) {
                $matches = $uk->where('uuid', M26DetailPackage::uuid($target['identity'], $source, $slot + 1))->values();
                if ($matches->count() !== 1 || !$matches[0] instanceof TextBlock
                    || $this->binding($matches[0], json_decode($matches[0]->body, true, flags: JSON_THROW_ON_ERROR)) === null) { return $page; }
            }
            $subtitles = $uk->where('type', 'subtitle')->values();
            if ($subtitles->count() !== 1 || !$this->exactSubtitle($subtitles[0], $target, $old)) { return $page; }
            $subtitleAbsent = $this->subtitleWasAbsent($old); $projected = $this->projectedBefore($old);
            $copy = clone $page; $copy->setRawAttributes(array_replace($page->getAttributes(), ['text' => $subtitleAbsent ? null : $old['page']['subtitle_text']]), true);
            $restored = $blocks->reject(static fn ($block) => $block->locale === 'uk')->values();
            if (!$subtitleAbsent) { $restored->push($this->cloneBody($subtitles[0], $old['page']['subtitle_html'])); }
            foreach ($old['page']['blocks'] as $slot => $source) {
                $restored->push($this->cloneConfig($uk->firstWhere('uuid', M26DetailPackage::uuid($target['identity'], $projected['page']['blocks'][$slot], $slot + 1)), $source));
            }
            $copy->setRelation('textBlocks', new Collection($restored->sortBy('sort_order')->values()->all()));
            return $copy;
        } catch (\Throwable) { return $page; }
    }
}
