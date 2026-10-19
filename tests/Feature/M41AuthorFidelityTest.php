<?php

namespace Tests\Feature;

use App\Http\Controllers\PageController;
use App\Support\M26DetailPackage;
use DOMDocument;
use DOMXPath;
use PHPUnit\Framework\TestCase;

/** Read-only: independent author arrays, accepted native slots and finite UI. */
class M41AuthorFidelityTest extends TestCase
{
    private const MASTER_SHA = '9020cf977e903c76f93c7165bbe402567b5fbf40a98a8139dc6751b223b34553';

    private function root(): string { return dirname(__DIR__, 2); }

    private function read(string $path): array
    {
        return json_decode(file_get_contents($this->root().'/'.$path), true, flags: JSON_THROW_ON_ERROR);
    }

    private function payloads(): array
    {
        return [$this->read('docs/content/m41-authored-tense-comparisons.v1.0.1.json'),
            $this->read('database/content-patches/m41-authored-tense-comparisons-before.json'),
            $this->read('database/content-patches/m41-authored-tense-comparisons.v1.0.1.json')];
    }

    private function plain(string $html): string
    {
        $html = preg_replace('~</?(?:p|li|ul|ol|div|td|th|tr|table|thead|tbody|br|h[1-6])\b[^>]*>~i', ' ', $html);
        return trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    }

    private function xpath(string $html): DOMXPath
    {
        $dom = new DOMDocument; libxml_use_internal_errors(true);
        $dom->loadHTML('<meta charset="UTF-8">'.$html, LIBXML_NONET); libxml_clear_errors();
        return new DOMXPath($dom);
    }

    public function test_exact_approved_revision_bytes_and_the_single_corrected_author_paragraph(): void
    {
        [$master, $before, $projection] = $this->payloads();
        $bytes = file_get_contents($this->root().'/docs/content/m41-authored-tense-comparisons.v1.0.1.json');
        self::assertSame(self::MASTER_SHA, hash('sha256', $bytes));
        self::assertStringNotContainsString("\r", $bytes); self::assertStringEndsWith("\n", $bytes);
        self::assertSame('1.0.1', $master['version']);
        self::assertSame('author_revision_ready_for_local_implementation', $master['status']);
        self::assertSame('ab61310a81f2389353c264fe23266025fe49ffd6', $before['base_sha']);
        self::assertSame(self::MASTER_SHA, $before['author_master_source']['sha256']);
        self::assertSame($bytes, $before['author_master_source']['bytes']);
        self::assertSame(self::MASTER_SHA, $projection['master_sha256']);
        foreach (['author_correction_source' => ['docs/content/m41-author-correction.v1.0.1.json', 'efe1d4ed3c691b8108e168d7dc182937fb9667ab96f192f2375d055c97137f89'],
            'author_approval_source' => ['docs/content/m41-codex-approval-and-correction.md', '1c74999e7f50ea3b7f1b3d363a8bd42d96f092c334d486893b78273072119e40']] as $field => [$path, $sha]) {
            self::assertSame($path, $before[$field]['path']); self::assertSame($sha, $before[$field]['sha256']);
            self::assertSame($sha, hash('sha256', $before[$field]['bytes']));
            self::assertSame($before[$field]['bytes'], file_get_contents($this->root().'/'.$path));
        }
        $correction = $this->read('docs/content/m41-author-correction.v1.0.1.json'); $oldBytes = $bytes;
        self::assertSame(['/version', '/status', '/lessons/2/sections/2/points/1/paragraphs_uk/1'], array_column($correction['changes'], 'json_pointer'));
        foreach ($correction['changes'] as $change) {
            $from = json_encode($change['after'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
            $to = json_encode($change['before'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
            self::assertSame(1, substr_count($oldBytes, $from)); $oldBytes = str_replace($from, $to, $oldBytes);
        }
        self::assertSame('a9569a61f37643951e7342de66dc7f3967951d4d7187c106a0f8703cf3653dcf', hash('sha256', $oldBytes));
        self::assertSame('У прикладі нижче форму надіслали лише один раз: спочатку повідомляємо про результат, а потім уточнюємо час надсилання.',
            $master['lessons'][2]['sections'][2]['points'][1]['paragraphs_uk'][1]);
        self::assertSame([['en' => 'I have sent the form. I sent it at nine this morning.',
            'uk' => 'Я надіслав форму. Я надіслав її сьогодні о дев’ятій.']], $master['lessons'][2]['sections'][2]['points'][1]['examples']);
        self::assertCount(3, $master['lessons']); self::assertCount(3, $before['targets']); self::assertCount(3, $projection['targets']);
    }

    public function test_all_native_identities_slots_columns_levels_tags_and_navigation_are_preserved(): void
    {
        [$master, $before, $projection] = $this->payloads();
        foreach ($projection['targets'] as $i => $target) {
            $original = $before['targets'][$i]['before']; $after = $target['after']; $lesson = $master['lessons'][$i];
            self::assertSame($lesson['identity'], $target['identity']); self::assertSame($lesson['definition_path'], $target['path']);
            self::assertSame($lesson['baseline_git_blob_sha1'], $before['targets'][$i]['source_git_blob']);
            $baselineBytes = json_encode($original, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
            self::assertSame($lesson['baseline_git_blob_sha1'], sha1('blob '.strlen($baselineBytes)."\0".$baselineBytes));
            $restored = $after; $restored['page']['blocks'] = $original['page']['blocks'];
            $restored['page']['subtitle_html'] = $original['page']['subtitle_html'];
            $restored['page']['subtitle_text'] = $original['page']['subtitle_text'];
            self::assertSame($original, $restored, 'Only approved learner bodies/subtitles and new authored slots may differ.');
            self::assertSame($lesson['subtitle'], $after['page']['subtitle_text']);
            self::assertSame($original['page']['title'].' — '.$lesson['subtitle'], $this->plain($after['page']['subtitle_html']));
            self::assertSame($original['page']['title'], $this->xpath($after['page']['subtitle_html'])->query('//strong')->item(0)->textContent);
            self::assertCount(9, $after['page']['blocks']);
            $oldNav = json_decode($original['page']['blocks'][$target['navigation_slot']]['body'], true, flags: JSON_THROW_ON_ERROR);
            $newNav = json_decode($after['page']['blocks'][$target['navigation_slot']]['body'], true, flags: JSON_THROW_ON_ERROR);
            self::assertSame($oldNav['items'], array_slice($newNav['items'], 0, count($oldNav['items'])));
            self::assertSame($target['navigation_additions'], array_slice($newNav['items'], count($oldNav['items'])));
            $restoredNav = $newNav; $restoredNav['items'] = $oldNav['items']; self::assertSame($oldNav, $restoredNav);
            $expectedAdditions = [];
            foreach ($lesson['related'] as $related) {
                if (in_array($related['path'], array_column($oldNav['items'], 'url'), true)) { continue; }
                $expectedAdditions[] = ['label' => $related['label'], 'url' => $related['path'], 'current' => false];
                self::assertSame(200, $projection['related_routes']['verified'][$related['path']]['http_status']);
                self::assertSame(0, $projection['related_routes']['verified'][$related['path']]['redirects']);
            }
            self::assertSame($expectedAdditions, $target['navigation_additions']);
            foreach ($original['page']['blocks'] as $slot => $config) {
                $newConfig = $after['page']['blocks'][$slot]; $newConfig['type'] = $config['type']; $newConfig['body'] = $config['body'];
                self::assertSame($config, $newConfig, 'All pre-existing non-body/type configuration remains exact.');
                self::assertSame(M26DetailPackage::uuid($target['identity'], $config, $slot + 1),
                    M26DetailPackage::uuid($target['identity'], $after['page']['blocks'][$slot], $slot + 1));
            }
            $hero = json_decode($after['page']['blocks'][0]['body'], true, flags: JSON_THROW_ON_ERROR);
            $oldHero = json_decode($original['page']['blocks'][0]['body'], true, flags: JSON_THROW_ON_ERROR);
            self::assertSame($oldHero['level'], $hero['level']); self::assertSame($lesson['hero'], $hero['author_hero']);
            self::assertSame($after, $this->read($target['path']));
        }
        self::assertSame('A2', $projection['targets'][2]['after']['page']['blocks'][0]['level']);
        self::assertSame('A2–B1', json_decode($projection['targets'][2]['after']['page']['blocks'][0]['body'], true)['level']);
    }

    public function test_actual_controller_title_extraction_preserves_all_three_runtime_h1_identities(): void
    {
        [$master, $before, $projection] = $this->payloads();
        // The real controller is inspected without constructing services or
        // opening any connection. A bare Page.title assertion missed this bug.
        $controller = (new \ReflectionClass(PageController::class))->newInstanceWithoutConstructor();
        $extract = new \ReflectionMethod($controller, 'extractLocalizedTitleFromSubtitle');
        foreach ($projection['targets'] as $i => $target) {
            $original = $before['targets'][$i]['before']; $after = $target['after'];
            self::assertSame($original['page']['title'], $extract->invoke($controller, $original['page']['subtitle_html']));
            self::assertSame($original['page']['title'], $extract->invoke($controller, $after['page']['subtitle_html']),
                'Actual controller-derived H1 must remain the approved lesson title.');
            self::assertSame($original['page']['title'], $after['page']['title']);
            self::assertSame($master['lessons'][$i]['subtitle'], $after['page']['subtitle_text']);
            $hero = json_decode($after['page']['blocks'][0]['body'], true, flags: JSON_THROW_ON_ERROR);
            self::assertSame($master['lessons'][$i]['subtitle'], $this->plain($hero['intro']));
        }
        self::assertSame('Що сталося', $extract->invoke($controller, '<p>'.$master['lessons'][0]['subtitle'].'</p>'),
            'Regression fixture documents why the plain draft subtitle was unsafe.');
    }

    public function test_all_thirty_five_basic_points_fourteen_explicit_details_and_three_tables_remain_exact(): void
    {
        [$master, , $projection] = $this->payloads(); $points = 0; $details = []; $tables = 0;
        foreach ($projection['targets'] as $i => $target) {
            $ownerDetails = 0;
            foreach ($master['lessons'][$i]['sections'] as $j => $section) {
                $body = json_decode($target['after']['page']['blocks'][$target['section_slots'][$j]]['body'], true, flags: JSON_THROW_ON_ERROR);
                self::assertSame($section, $body['author_section']); self::assertSame($section['title'], $body['title']);
                self::assertSame(self::MASTER_SHA, $body['m41_v1']['master_sha256']);
                if (isset($section['table'])) {
                    $tables++; $xp = $this->xpath($body['intro']);
                    self::assertSame($section['table']['columns'], array_map(fn ($node) => $node->textContent, iterator_to_array($xp->query('//thead//th'))));
                    foreach (iterator_to_array($xp->query('//tbody/tr')) as $rowIndex => $row) {
                        foreach (iterator_to_array($row->childNodes) as $cellIndex => $cell) {
                            $expected = $section['table']['rows'][$rowIndex][$cellIndex];
                            self::assertSame(str_replace("\n", '', $expected), $cell->textContent);
                        }
                    }
                }
                foreach ($section['points'] as $k => $point) {
                    $points++; $native = $body['sections'][$k]; $plan = $body['point_plan'][$k];
                    self::assertSame($point['title'], $native['label']); self::assertSame($point['id'], $plan['id']);
                    self::assertSame($point['paragraphs_uk'], array_map(fn ($node) => $node->textContent,
                        iterator_to_array($this->xpath($native['description'])->query('//body/p'))));
                    self::assertSame(array_map(fn ($example) => ['en' => $example['en'], 'ua' => $example['uk']], $point['examples']), $native['examples']);
                    if (isset($point['detail'])) {
                        $ownerDetails++; self::assertSame('point_detail', $plan['decision']);
                        self::assertSame($point['detail'], $plan['detail']); self::assertSame($native['note'], $plan['detail_html']);
                        $xp = $this->xpath($native['note']);
                        self::assertSame($point['detail']['title'], $xp->query('//h4')->item(0)->textContent);
                        self::assertSame($point['detail']['paragraphs_uk'], array_map(fn ($node) => $node->textContent, iterator_to_array($xp->query('//body/p'))));
                        foreach ($point['detail']['examples'] as $e => $example) {
                            self::assertSame($example['en'], $xp->query('//p[@lang="en"]')->item($e)->textContent);
                            self::assertSame($example['uk'], $xp->query('//p[@lang="uk"]')->item($e)->textContent);
                        }
                    } else {
                        self::assertSame('visible_basic', $plan['decision']); self::assertArrayNotHasKey('note', $native);
                    }
                    self::assertNotEmpty($plan['reason']);
                }
            }
            $details[] = $ownerDetails;
        }
        self::assertSame(35, $points); self::assertSame([4, 5, 5], $details); self::assertSame(3, $tables);
    }

    public function test_all_eighteen_tasks_thirty_two_controls_and_finite_keys_tokens_translations_are_exact(): void
    {
        [$master, , $projection] = $this->payloads(); $taskCount = 0; $controlCount = 0; $manualCount = 0; $acceptedCount = 0; $tokenCount = 0;
        foreach ($projection['targets'] as $i => $target) {
            $body = json_decode($target['after']['page']['blocks'][$target['practice_slot']]['body'], true, flags: JSON_THROW_ON_ERROR);
            self::assertSame($master['lessons'][$i]['practice'], $body['author_practice']);
            self::assertCount(6, $body['cases']); self::assertCount(6, $body['author_self_check']['prompts']); self::assertCount(6, $body['author_self_check']['answers']);
            self::assertSame([$target['bank']['seeder_class']], $body['linked_practice']['seeder_classes']);
            self::assertSame([(string) $target['bank']['question_type']], $body['linked_practice']['question_types']);
            self::assertSame([72, 72, 24][$i], $target['bank']['question_count']);
            self::assertSame(64, strlen($target['bank']['question_ids_sha256']));
            self::assertArrayNotHasKey('question_ids', $target['bank']); self::assertArrayNotHasKey('test_url', $target['bank']);
            foreach ($master['lessons'][$i]['practice'] as $j => $task) {
                $taskCount++; $case = $body['cases'][$j]; $prompt = $body['author_self_check']['prompts'][$j];
                self::assertSame($task['id'], $case['id']); self::assertSame($task['source_index'], $case['source_index']);
                self::assertSame('all_required_controls', $case['scoring']);
                $xp = $this->xpath($prompt); self::assertSame($task['title'], $xp->query('//h4')->item(0)->textContent);
                $expectedParagraphs = [$task['prompt_uk']]; if ($task['context_uk'] !== '') { $expectedParagraphs[] = $task['context_uk']; }
                self::assertSame($expectedParagraphs, array_map(fn ($node) => $node->textContent, iterator_to_array($xp->query('//body/p'))));
                foreach ($task['controls'] as $k => $author) {
                    $controlCount++; $control = $case['controls'][$k];
                    self::assertSame($author['id'], $control['id']); self::assertSame($author['kind'], $control['kind']);
                    self::assertSame($author['label_uk'], $control['label']); self::assertTrue($control['required']);
                    self::assertSame($author['options'] ?? [], $control['options']);
                    self::assertSame($author['stimulus_en'] ?? null, $control['stimulus_en'] ?? null);
                    if ($author['kind'] === 'manual') {
                        $manualCount++; $acceptedCount += count($control['accepted']); $tokenCount += count($control['tokens']);
                        self::assertSame($author['canonical_answer'], $control['answer']);
                        self::assertSame($author['accepted_answers'], $control['accepted']); self::assertSame($author['tokens'], $control['tokens']);
                        self::assertSame($control['answer'], implode(' ', $control['tokens']));
                    } else { self::assertSame($author['correct_value'], $control['answer']); }
                }
                $xp = $this->xpath($body['author_self_check']['answers'][$j]);
                self::assertSame($task['feedback']['paragraphs_uk'], array_map(fn ($node) => $node->textContent, iterator_to_array($xp->query('//body/p'))));
                foreach ($task['feedback']['answer_examples'] as $e => $example) {
                    self::assertSame($example['en'], $xp->query('//p[@lang="en"]')->item($e)->textContent);
                    self::assertSame($example['uk'], $xp->query('//p[@lang="uk"]')->item($e)->textContent);
                }
            }
        }
        self::assertSame([18, 32, 15, 20, 55], [$taskCount, $controlCount, $manualCount, $acceptedCount, $tokenCount]);
    }
}
