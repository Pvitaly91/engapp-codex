<?php

namespace Tests\Feature;

use App\Support\ComposeTokenCase;
use App\Support\M82CommonMistakesLevelRevision as Package;
use PHPUnit\Framework\TestCase;

/** Finite, independent pre-apply editorial literals; not an automatic paraphrase classifier. */
class M82CommonMistakesLevelRevisionSemanticFixturesTest extends TestCase
{
    private const FIXTURE = 'docs/content/m82-semantic-fixtures.v1.0.2.json';
    private const FIXTURE_SHA = '0bfb1bbb9a45281d99f8c1833d3f52da32aee1a71bf7defedcbb77080ef35911';

    private const REVIEWED_NAVIGATION_URLS = [
        4 => [
            '/theory/basic-grammar/verb-to-be/verb-to-be-present',
            '/theory/basic-grammar/verb-to-be/there-is-there-are',
            '/theory/some-any/theory-some-any-things',
            '/theory/prykmetniky-ta-pryslinknyky/comparative-vs-superlative',
        ],
        5 => [
            '/theory/tenses/present-perfect/present-perfect-forms',
            '/theory/conditionals/first-conditional',
            '/theory/reported-speech/reported-statements',
            '/theory/conditionals/second-conditional',
        ],
    ];

    private function fixtures(): array
    {
        $path = dirname(__DIR__, 2).'/'.self::FIXTURE;
        self::assertFileExists($path);
        self::assertFalse(is_link($path));
        $raw = file_get_contents($path);
        self::assertSame(self::FIXTURE_SHA, hash('sha256', $raw));
        self::assertStringNotContainsString("\r", $raw);
        self::assertSame("\n", substr($raw, -1));
        self::assertNotSame("\n\n", substr($raw, -2));
        $f = json_decode($raw, true, flags: JSON_THROW_ON_ERROR);
        self::assertSame(1, $f['schema_version']);
        self::assertSame('b8fecb29492038dc01d374955b61c500c718fa4c', $f['base_sha']);
        self::assertSame('1.0.1', $f['navigation_revision']['version']);
        self::assertSame('e56d99642f0e69a7e0940e1725be0384e79a80c9b3421798e8af957ac143d16f', $f['navigation_revision']['prior_fixture_sha256']);
        self::assertCount(6, $f['owners']);
        self::assertCount(36, $f['tasks']);
        self::assertCount(22, $f['teaching_literals']);
        self::assertCount(32, $f['mutations']);

        return $f;
    }

    private function lessons(): array
    {
        $release = Package::load(dirname(__DIR__, 2));
        Package::validate($release[0], $release[1], $release[2], $release[3]);

        return $release[2]['lessons'];
    }

    private static function value(array $lessons, array $path): array
    {
        $value = $lessons;
        foreach ($path as $key) {
            if (!is_array($value) || !array_key_exists($key, $value)) {
                return [false, null];
            }
            $value = $value[$key];
        }

        return [true, $value];
    }

    /** Exact finite gate, so mutations do not pass by retaining one convenient substring. */
    private function semanticProblems(array $lessons, array $f): array
    {
        $errors = [];
        if (count($lessons) !== 6) { $errors[] = 'six-owner-count'; }
        $allowedBlockKeys = ['key', 'existing_source_index', 'type', 'column', 'level', 'uuid_key', 'data'];
        foreach ($f['owners'] as $i => $expected) {
            $lesson = $lessons[$i] ?? null;
            if (!is_array($lesson)) { $errors[] = 'missing-owner-'.$i; continue; }
            foreach (['identity', 'slug', 'local_url', 'raw_title', 'preserved_h1'] as $field) {
                if (($lesson[$field] ?? null) !== $expected[$field]) { $errors[] = $i.':'.$field; }
            }
            if (($lesson['locale'] ?? null) !== 'uk'
                || ($lesson['intended_own_practice_level'] ?? null) !== $expected['level']
                || count($lesson['details'] ?? []) !== $expected['detail_count']) {
                $errors[] = $i.':locale-level-details';
            }
            $blocks = $lesson['blocks'] ?? [];
            $oldCount = count($expected['continuing_types']);
            if (count($blocks) !== $oldCount + 1
                || array_column(array_slice($blocks, 0, $oldCount), 'type') !== $expected['continuing_types']
                || array_column(array_slice($blocks, 0, $oldCount), 'column') !== $expected['continuing_columns']) {
                $errors[] = $i.':native-functions-and-columns';
            }
            foreach ($blocks as $block) {
                if (!is_array($block) || array_diff(array_keys($block), $allowedBlockKeys) !== []) {
                    $errors[] = $i.':unknown-block-refactor';
                }
            }
            $nav = $blocks[$expected['navigation_slot']]['data']['items'] ?? null;
            if ($nav !== $expected['navigation_items']) { $errors[] = $i.':exact-navigation'; }
            $practice = $blocks[$expected['practice_source_index']] ?? [];
            if (($practice['type'] ?? null) !== 'practice-set'
                || ($practice['column'] ?? null) !== 'left'
                || ($practice['level'] ?? null) !== $expected['level']
                || ($practice['data']['linked_practice']['seeder_classes'] ?? null) !== $expected['linked_bank']) {
                $errors[] = $i.':practice-level-and-theme';
            }
        }
        foreach ($f['tasks'] as $expected) {
            $owner = $expected['owner_index']; $slot = $f['owners'][$owner]['practice_source_index'];
            [, $task] = self::value($lessons, [$owner, 'blocks', $slot, 'data', $expected['group'], $expected['index']]);
            if (!is_array($task)) { $errors[] = $expected['id'].':missing-task'; continue; }
            foreach (['id', 'answer', 'accepted'] as $field) {
                if (($task[$field] ?? null) !== $expected[$field]) { $errors[] = $expected['id'].':'.$field; }
            }
            if (($task[$expected['visible_field']] ?? null) !== $expected['visible_literal']
                || ($task['feedback'] ?? null) !== $expected['feedback_literal']) {
                $errors[] = $expected['id'].':complete-visible-criterion-feedback';
            }
            if (isset($expected['bank_literal']) && ($task['before'] ?? null) !== $expected['bank_literal']) {
                $errors[] = $expected['id'].':token-bank';
            }
            if (isset($expected['options_literal']) && ($task['options'] ?? null) !== $expected['options_literal']) {
                $errors[] = $expected['id'].':options';
            }
        }
        foreach ($f['teaching_literals'] as $expected) {
            [$found, $value] = self::value($lessons, $expected['path']);
            if (!$found || $value !== $expected['value']) { $errors[] = $expected['id']; }
        }

        return $errors;
    }

    private function task(array $lessons, string $id): array
    {
        foreach ($lessons as $lesson) {
            foreach ($lesson['blocks'] as $block) {
                if ($block['type'] !== 'practice-set') { continue; }
                foreach (['selects', 'choices', 'inputs'] as $group) {
                    foreach ($block['data'][$group] as $task) {
                        if ($task['id'] === $id) { return $task; }
                    }
                }
            }
        }
        self::fail('Reviewed task missing: '.$id);
    }

    public function test_all_thirty_six_literal_task_contracts_and_complete_learning_fields_match_reviewed_expectations(): void
    {
        $f = $this->fixtures(); $lessons = $this->lessons();
        self::assertSame(Package::OWNERS, array_column($f['owners'], 'identity'));
        self::assertSame([], $this->semanticProblems($lessons, $f));
        $ids = [];
        foreach ($f['tasks'] as $expected) {
            $task = $this->task($lessons, $expected['id']);
            self::assertArrayNotHasKey($task['id'], $ids); $ids[$task['id']] = true;
            self::assertSame($expected['answer'], $task['answer']);
            self::assertSame($expected['accepted'], $task['accepted']);
            $keys = array_map(Package::comparisonKey(...), $task['accepted']);
            foreach ($expected['rejected'] as $wrong) {
                self::assertNotContains(Package::comparisonKey($wrong), $keys, $task['id'].' finite semantic counterexample');
            }
        }
        self::assertCount(36, $ids);
        self::assertSame(44, array_sum(array_map(static fn (array $t): int => count($t['accepted']), $f['tasks'])));
    }

    public function test_present_perfect_preserves_gone_reference_been_in_duration_finished_event_scope_and_variant_boundary(): void
    {
        $lessons = $this->lessons(); $l = $lessons[0];
        self::assertSame([
            ['en' => 'Nora has been to Paris twice.', 'ua' => 'Нора двічі побувала в Парижі.'],
            ['en' => 'Nora has gone to Paris; she is not at home.', 'ua' => 'Нора вирушила до Парижа; її немає вдома.'],
            ['en' => 'Nora has been in Paris for two days.', 'ua' => 'Нора вже два дні перебуває в Парижі.'],
        ], $l['blocks'][1]['data']['sections'][1]['examples']);
        self::assertSame([
            ['en' => 'We have lived here for three years.', 'ua' => 'Ми живемо тут уже три роки.'],
            ['en' => 'We have lived here since 2022.', 'ua' => 'Ми живемо тут із 2022 року.'],
        ], $l['blocks'][1]['data']['sections'][2]['examples']);
        self::assertSame('I have read the email that you sent yesterday.', $l['blocks'][2]['data']['sections'][1]['examples'][0]['en']);
        self::assertSame('My sister has been to Paris three times.', $l['blocks'][3]['data']['items'][1]['right_en']);
        self::assertSame('Nora has gone to Paris; she is not at home.', $l['blocks'][1]['data']['sections'][1]['examples'][1]['en']);
        self::assertSame('Залишити без змін: yesterday стосується sent, не have read.',
            $this->task($lessons, 'm82-present-perfect-meaning-already-correct-scope')['answer']);
        self::assertSame('Have you finished already?', $l['blocks'][2]['data']['sections'][2]['examples'][1]['en']);
    }

    public function test_countability_retains_exact_one_mass_agreement_quantity_servings_and_contextual_sufficiency(): void
    {
        $lessons = $this->lessons(); $l = $lessons[1];
        self::assertSame('I need one piece of advice.', $l['blocks'][3]['data']['items'][0]['right_en']);
        self::assertSame('She gave me a lot of information.', $l['blocks'][3]['data']['items'][2]['right_en']);
        self::assertSame('Вона дала мені багато інформації.', $l['blocks'][3]['data']['items'][2]['right_ua']);
        self::assertSame([
            ['en' => 'This advice is useful.', 'ua' => 'Ця порада корисна.'],
            ['en' => 'These two pieces of advice are useful.', 'ua' => 'Ці дві поради корисні.'],
            ['en' => 'How many pieces of advice do you need?', 'ua' => 'Скільки окремих порад тобі потрібно?'],
        ], $l['blocks'][2]['data']['sections'][1]['examples']);
        self::assertSame('Залишити: coffees тут означає окремі порції.',
            $this->task($lessons, 'm82-countability-meaning-correct-servings')['answer']);
        self::assertSame('Ні: три менше за потрібні п’ять; a few не гарантує достатності.',
            $this->task($lessons, 'm82-countability-meaning-few-not-sufficient')['answer']);
        self::assertSame([], $l['details']);
    }

    public function test_prepositions_keep_natural_motion_variants_arrival_late_and_nonempty_absence(): void
    {
        $lessons = $this->lessons(); $l = $lessons[2];
        self::assertSame([
            ['en' => 'She walked in the room.', 'ua' => 'Вона ходила кімнатою.'],
            ['en' => 'She walked into the room.', 'ua' => 'Вона увійшла до кімнати.'],
            ['en' => 'He jumped in the pool. He jumped into the pool.', 'ua' => 'Він стрибнув у басейн. Він стрибнув у басейн.'],
        ], $l['blocks'][1]['data']['sections'][3]['examples']);
        self::assertSame('We arrived at the airport late.', $l['blocks'][3]['data']['items'][2]['right_en']);
        self::assertSame('Ми пізно прибули до аеропорту.', $l['blocks'][3]['data']['items'][2]['right_ua']);
        self::assertSame('Без прийменника', $this->task($lessons, 'm82-prepositions-common-mistakes-form-next-monday')['answer']);
        self::assertSame('Ні: з jump тут нормативні in та into; початкове речення вже правильне.',
            $this->task($lessons, 'm82-prepositions-common-mistakes-meaning-correct-jump')['answer']);
        self::assertSame('Ні: названо пункт призначення й поточну поїздку, не факт прибуття.',
            $this->task($lessons, 'm82-prepositions-common-mistakes-meaning-destination-not-arrival')['answer']);
    }

    public function test_b1_b2_diagnosis_keeps_ordinary_second_mixed_modal_force_actor_and_only_scope(): void
    {
        $lessons = $this->lessons(); $l = $lessons[3];
        self::assertSame([
            ['en' => 'If I knew the answer, I would tell you.', 'ua' => 'Якби я знав відповідь, я б тобі сказав.'],
            ['en' => 'If I had known the answer yesterday, I would have told you.', 'ua' => 'Якби я знав відповідь учора, я б тобі сказав.'],
            ['en' => 'If I had saved the address, I would know where to go now.', 'ua' => 'Якби я зберіг адресу, я б зараз знав, куди йти.'],
        ], $l['blocks'][1]['data']['sections'][0]['examples']);
        self::assertSame('У третьому прикладі адресу раніше не збережено, тому тепер бракує знання. Для бажаної іншої ситуації I wish I knew the answer. — Шкода, що я не знаю відповіді. Для жалю про минуле I wish I had saved the address. — Шкода, що я не зберіг адресу.',
            $l['blocks'][1]['data']['sections'][0]['note']);
        self::assertSame('She must have gone home.', $l['blocks'][3]['data']['items'][1]['right_en']);
        self::assertSame('Only Marta knew the code.', $l['blocks'][1]['data']['sections'][3]['examples'][2]['en']);
        self::assertSame('I rarely see such clear feedback.', $l['blocks'][1]['data']['sections'][3]['examples'][0]['en']);
        self::assertSame('Despite the fact that Danylo was absent, Olena finished the report. — Попри те, що Данило був відсутній, Олена завершила звіт.',
            $this->task($lessons, 'm82-b1-b2-grammar-traps-meaning-linker-actor')['answer']);
        self::assertSame('Only after the call did Marta know the code.', $l['details'][0]['fragment']['data']['sections'][0]['examples'][1]['en']);
    }

    public function test_a1_mixed_revision_retains_eleven_groups_native_four_and_broad_task_functions(): void
    {
        $f = $this->fixtures(); $lessons = $this->lessons(); $l = $lessons[4];
        self::assertSame(['be','there is/are','have got','can','Present Simple/Continuous','Past Simple','will','articles','some/any','much/many/a lot of','comparatives/superlatives'], $f['source_groups']['a1']);
        self::assertCount(11, $l['blocks'][1]['data']['items']);
        self::assertSame(['hero','summary-list','forms-grid','navigation-chips'], array_column(array_slice($l['blocks'], 0, 4), 'type'));
        self::assertSame(['header','left','left','footer'], array_column(array_slice($l['blocks'], 0, 4), 'column'));
        self::assertSame('http://gramlyze.loc/theory/mixed-revision/a1-mixed-revision', $l['local_url']);
        self::assertSame('much', $this->task($lessons, 'm82-a1-revision-quantity-milk')['answer']);
        self::assertSame('has', $this->task($lessons, 'm82-a1-revision-have-got-question')['answer']);
        self::assertSame('A', $this->task($lessons, 'm82-a1-revision-routine-now')['answer']);
        self::assertSame('B', $this->task($lessons, 'm82-a1-revision-comparison-context')['answer']);
        self::assertSame('Yesterday, we went to the park.', $this->task($lessons, 'm82-a1-revision-past-event')['answer']);
        self::assertSame('I will buy some apples tomorrow.', $this->task($lessons, 'm82-a1-revision-future-purchase')['answer']);
        self::assertSame($f['owners'][4]['navigation_items'], $l['blocks'][3]['data']['items']);
        self::assertSame([], $l['details']);
    }

    public function test_a2_mixed_revision_retains_twelve_groups_reported_role_passive_agent_and_modal_negation(): void
    {
        $f = $this->fixtures(); $lessons = $this->lessons(); $l = $lessons[5];
        self::assertSame(['Present Perfect/Past Simple','time expressions','First/Second Conditional','be going to','should/ought to/must/have to','gerund/infinitive','Past Continuous','relative clauses','passive','reported speech','used to','question tags'], $f['source_groups']['a2']);
        self::assertCount(12, $l['blocks'][1]['data']['items']);
        self::assertSame(['hero','summary-list','forms-grid','navigation-chips'], array_column(array_slice($l['blocks'], 0, 4), 'type'));
        self::assertSame('http://gramlyze.loc/theory/mixed-revision/a2-mixed-revision', $l['local_url']);
        self::assertSame("can't she", $this->task($lessons, 'm82-a2-revision-modal-tag')['answer']);
        self::assertSame('B', $this->task($lessons, 'm82-a2-revision-optional-not-forbidden')['answer']);
        self::assertSame(['Olia said that she was tired.', 'Olia said she was tired.'],
            $this->task($lessons, 'm82-a2-revision-reported-speaker')['accepted']);
        self::assertSame(['The windows are cleaned by Ivan every Friday.', 'The windows are cleaned every Friday by Ivan.'],
            $this->task($lessons, 'm82-a2-revision-passive-agent')['accepted']);
        self::assertSame('МОДЕЛЬ ПІСЛЯ ДІЄСЛОВА', $l['blocks'][2]['data']['items'][2]['label']);
        self::assertSame($f['owners'][5]['navigation_items'], $l['blocks'][3]['data']['items']);
        self::assertSame([], $l['details']);
    }

    public function test_eight_approved_footer_url_data_fields_are_literal_and_do_not_change_native_navigation(): void
    {
        $f = $this->fixtures(); $lessons = $this->lessons(); $reviewed = [];
        foreach (self::REVIEWED_NAVIGATION_URLS as $i => $urls) {
            $items = $lessons[$i]['blocks'][3]['data']['items'];
            self::assertCount(5, $items);
            foreach ($urls as $j => $url) {
                self::assertSame($url, $items[$j]['url']); self::assertFalse($items[$j]['current']);
                self::assertSame(['label', 'current', 'url'], array_keys($items[$j]));
                self::assertMatchesRegularExpression('~\A/theory/[a-z0-9/-]+\z~', $url);
                $reviewed[] = ['owner' => $lessons[$i]['identity'], 'source_index' => 3,
                    'item_index' => $j, 'label' => $items[$j]['label'], 'current' => false, 'url' => $url];
                foreach (['missing-url', 'hash-url', 'foreign-url', 'changed-label', 'changed-current'] as $case) {
                    $changed = $lessons; $item =& $changed[$i]['blocks'][3]['data']['items'][$j];
                    if ($case === 'missing-url') { unset($item['url']); }
                    if ($case === 'hash-url') { $item['url'] = '#'; }
                    if ($case === 'foreign-url') { $item['url'] = '/theory/basic-grammar/foreign'; }
                    if ($case === 'changed-label') { $item['label'] .= ' foreign'; }
                    if ($case === 'changed-current') { $item['current'] = true; }
                    unset($item);
                    self::assertNotSame([], $this->semanticProblems($changed, $f), $i.':'.$j.':'.$case);
                }
            }
            self::assertTrue($items[4]['current']); self::assertArrayNotHasKey('url', $items[4]);
            self::assertSame(['label', 'current'], array_keys($items[4]));
        }
        self::assertCount(8, $reviewed);
        $mapping = json_decode(file_get_contents(dirname(__DIR__, 2).'/'.$f['navigation_revision']['reviewed_mapping']), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame($reviewed, array_map(static fn ($row) => array_intersect_key($row, array_flip(['owner', 'source_index', 'item_index', 'label', 'current', 'url'])), $mapping['targets']));
        self::assertFalse($mapping['verification']['native_view_or_resolver_changed']);
        self::assertFalse($mapping['verification']['current_fifth_items_gain_url']);
        $native = file_get_contents(dirname(__DIR__, 2).'/'.$f['navigation_revision']['native_view']);
        self::assertSame($f['navigation_revision']['native_view_lf_sha256'], hash('sha256', str_replace("\r\n", "\n", $native)));
        self::assertSame([], $this->semanticProblems($lessons, $f));
    }

    public function test_thirty_two_finite_semantic_mutations_are_rejected_without_claiming_generic_language_classification(): void
    {
        $f = $this->fixtures(); $lessons = $this->lessons();
        self::assertSame([], $this->semanticProblems($lessons, $f));
        foreach ($f['mutations'] as $mutation) {
            $changed = $lessons; $target =& $changed;
            foreach (array_slice($mutation['path'], 0, -1) as $key) {
                self::assertIsArray($target); self::assertArrayHasKey($key, $target);
                $target =& $target[$key];
            }
            $target[$mutation['path'][array_key_last($mutation['path'])]] = $mutation['replacement'];
            unset($target);
            self::assertNotSame([], $this->semanticProblems($changed, $f), $mutation['id'].' must fail the independent finite acceptance gate: '.$mutation['why']);
            self::assertSame([], $this->semanticProblems($lessons, $f), 'Mutation probe never mutates original fixture/model.');
        }
    }

    public function test_visible_criteria_plain_feedback_aliases_and_full_token_multiplicity_are_literal(): void
    {
        $f = $this->fixtures(); $lessons = $this->lessons(); $full = 0; $accepted = 0;
        foreach ($f['tasks'] as $expected) {
            $task = $this->task($lessons, $expected['id']);
            $visible = $task[$expected['visible_field']];
            self::assertMatchesRegularExpression('/[\x{0400}-\x{04ff}]/u', $visible);
            self::assertDoesNotMatchRegularExpression('/\{a[0-9]+\}|&(?:#[0-9]+|#x[0-9a-f]+|[a-z]+);/iu', $visible);
            foreach (['prompt','hint','placeholder','strict','punctuation_sensitive'] as $key) { self::assertArrayNotHasKey($key, $task); }
            foreach (array_unique(array_merge($task['accepted'], $task['options'] ?? [])) as $value) {
                self::assertNotSame('', Package::comparisonKey($value));
                $key = mb_strtolower(trim($value)); self::assertArrayHasKey($key, $task['feedback']);
                self::assertMatchesRegularExpression('/[\x{0400}-\x{04ff}]/u', $task['feedback'][$key]);
                self::assertStringContainsString('—', $task['feedback'][$key]);
                self::assertDoesNotMatchRegularExpression('/<(?:\/?[a-z]|[!?])|&(?:#[0-9]+|#x[0-9a-f]+|[a-z]+);/iu', $task['feedback'][$key]);
            }
            $keys = array_map(Package::comparisonKey(...), $task['accepted']);
            foreach (['', ' ', "\t\n", '—'] as $empty) { self::assertNotContains(Package::comparisonKey($empty), $keys); }
            $accepted += count($task['accepted']);
            if ($expected['group'] === 'inputs') {
                ++$full; $chunks = preg_split('/\s*\/\s*/u', $task['before']);
                foreach ($chunks as $chunk) { self::assertLessThanOrEqual(3, count(preg_split('/\s+/u', trim($chunk)))); }
                self::assertNotSame($task['answer'], implode(' ', $chunks));
                $bank = preg_split('/\s+/u', implode(' ', $chunks)); $answer = preg_split('/\s+/u', $task['answer']);
                sort($bank); sort($answer); self::assertSame($answer, $bank, $task['id'].' exact tokens/multiplicity');
            }
        }
        self::assertSame(12, $full); self::assertSame(44, $accepted);
        self::assertSame(['If','I','had','known','the','address',',','I','would','have','gone','there','sooner','.'],
            ComposeTokenCase::normalize(['if','I','had','known','the','address',',','I','would','have','gone','there','sooner','.']));
        self::assertSame(['Although','it','was','raining',',','we','continued','the','tour','.'],
            ComposeTokenCase::normalize(['although','it','was','raining',',','We','continued','the','tour','.']));
    }

    public function test_theory_widget_does_not_rewrite_course_final_blueprints_pools_or_progress_sources(): void
    {
        $f = $this->fixtures(); $root = dirname(__DIR__, 2);
        $path = $root.'/'.$f['course_preservation']['manifest'];
        self::assertFalse(is_link($path)); self::assertFileExists($path);
        $raw = file_get_contents($path);
        self::assertSame($f['course_preservation']['sha256'], hash('sha256', $raw));
        $manifest = json_decode($raw, true, flags: JSON_THROW_ON_ERROR);
        self::assertSame($f['base_sha'], $manifest['base_sha']);
        self::assertSame('course', $manifest['scope']);
        self::assertCount(101, $manifest['paths']);
        foreach ($f['course_preservation']['critical_paths'] as $critical) { self::assertContains($critical, $manifest['paths']); }
        $hash = hash_init('sha256'); $present = 0;
        foreach ($manifest['paths'] as $relative) {
            self::assertMatchesRegularExpression('~^(?:app|database|public/js|resources|routes)/[A-Za-z0-9_./-]+$~D', $relative);
            self::assertStringNotContainsString('..', $relative);
            $file = $root.'/'.$relative; self::assertFalse(is_link($file));
            if (!is_file($file)) { $record = ['exists' => false]; }
            else {
                ++$present; $bytes = file_get_contents($file);
                $record = ['exists' => true, 'bytes' => strlen($bytes), 'raw_sha256' => hash('sha256', $bytes),
                    'lf_sha256' => hash('sha256', str_replace("\r\n", "\n", $bytes)),
                    'crlf' => substr_count($bytes, "\r\n"), 'lf' => substr_count($bytes, "\n")];
            }
            hash_update($hash, json_encode([$relative, $record], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)."\n");
        }
        $actual = hash_final($hash);
        self::assertSame(101, $present);
        self::assertContains($actual, array_column($manifest['profiles'], 'files_sha256'),
            'Actual full source bytes match one independently captured ROOT/WT course profile, not a simulated AFTER.');
    }
}
