<?php

namespace Tests\Feature;

use App\Models\TextBlock;
use App\Services\M32ContentPatch;
use App\Support\M26DetailPackage;
use App\Support\M32FormalEnglishPackage as Package;
use DOMDocument;
use DOMXPath;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class M32FormalEnglishPackageTest extends TestCase
{
    use RebuildsComposeTestSchema;

    protected function setUp(): void
    {
        parent::setUp(); $this->rebuildComposeTestSchema(); app()->setLocale('uk'); $this->withoutVite();
    }

    private function bag(string $html): array
    {
        $text = html_entity_decode(strip_tags(str_replace(['<br>', '</p>', '</h4>', '</td>', '</th>', '</li>'], ' ', $html)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        preg_match_all('/[\p{L}\p{N}]+(?:[’\x27-][\p{L}\p{N}]+)*/u', $text, $matches);
        $bag = array_count_values($matches[0]); ksort($bag); return $bag;
    }

    public function test_exact_source_owner_metadata_hero_and_every_author_word_are_preserved(): void
    {
        [$before, $package] = Package::load(); Package::validate($before, $package);
        self::assertSame(M32ContentPatch::NAMES, array_column($package['targets'], 'identity'));
        foreach ($package['targets'] as $i => $target) {
            self::assertSame(['formal-english'], $target['ancestry']);
            self::assertSame($target['after'], json_decode(file_get_contents(base_path($target['path'])), true));
            $old = $before['targets'][$i]['before'];
            $body = preg_replace('~<section id="self-check-.*?</section>~s', '', $old['page']['blocks'][1]['body']);
            $out = '';
            foreach (array_slice($target['after']['page']['blocks'], 1) as $block) {
                $data = json_decode($block['body'], true, flags: JSON_THROW_ON_ERROR);
                if ($block['type'] === 'practice-set') { continue; }
                $out .= $data['title'].' '.($data['intro'] ?? '').' '.($data['outro'] ?? '').' ';
                foreach ($data['sections'] ?? [] as $point) { $out .= $point['description'].' '; }
                foreach ($data['items'] ?? [] as $item) { $out .= $item.' '; }
                foreach ($data['headers'] ?? [] as $header) { $out .= $header.' '; }
                foreach ($data['rows'] ?? [] as $row) { $out .= implode(' ', $row['cells']).' '; }
            }
            self::assertSame($this->bag($body), $this->bag($out), 'No author words lost, invented or repeated.');
            $restored = $target['after']; $restored['page']['blocks'] = $old['page']['blocks'];
            self::assertSame($old, $restored);
            self::assertSame($old['page']['blocks'][0], $target['after']['page']['blocks'][0]);
            self::assertCount(8, $target['plans']);
        }
    }

    private function practice(array $target): array
    {
        $blocks = array_values(array_filter($target['after']['page']['blocks'], fn ($block) => $block['type'] === 'practice-set'));
        self::assertCount(1, $blocks);
        return json_decode($blocks[0]['body'], true, flags: JSON_THROW_ON_ERROR);
    }

    public function test_original_six_prompts_and_explanations_have_exact_copy_and_unique_interactive_ownership(): void
    {
        [$before, $package] = Package::load();
        $banks = ['Database\\Seeders\\V3\\Polyglot\\PolyglotFormalRegisterAndNominalisationBasicsB2LessonSeeder',
            'Database\\Seeders\\V3\\Polyglot\\PolyglotNominalisationFormalRegisterC1LessonSeeder',
            'Database\\Seeders\\V3\\Polyglot\\PolyglotRegisterToneAndParaphraseC1LessonSeeder'];
        foreach ($package['targets'] as $i => $target) {
            $data = $this->practice($target); $source = $before['targets'][$i]['before']['page']['blocks'][1]['body'];
            $selfCheck = $data['author_self_check'];
            self::assertSame('Ключ і пояснення', $selfCheck['title']);
            foreach (['prompts' => 'data-self-checks', 'answers' => 'data-self-check-answers'] as $field => $attribute) {
                preg_match('~<ol '.$attribute.'>(.*?)</ol>~s', $source, $list);
                preg_match_all('~<li>(.*?)</li>~s', $list[1], $items);
                self::assertSame($items[1], $selfCheck[$field]); self::assertCount(6, $selfCheck[$field]);
            }
            $indices = [];
            foreach (['selects', 'choices', 'inputs'] as $group) {
                self::assertCount([[0, 3, 3], [0, 0, 6], [0, 3, 3]][$i][array_search($group, ['selects', 'choices', 'inputs'], true)], $data[$group]);
                foreach ($data[$group] as $item) {
                    self::assertNotEmpty($item['answer']);
                    self::assertSame($selfCheck['prompts'][$item['source_index'] - 1], $item['context']);
                    $indices[] = $item['source_index'];
                }
            }
            sort($indices); self::assertSame(range(1, 6), $indices);
            self::assertCount(1, $data['linked_practice']['seeder_classes']);
            self::assertSame(['4'], $data['linked_practice']['question_types']);
            self::assertSame([$banks[$i]], $data['linked_practice']['seeder_classes']);
        }
        $b2 = $this->practice($package['targets'][0]);
        self::assertSame(['a', 'b', 'c'], $b2['choices'][2]['options']);
        self::assertSame(['a', 'b'], $b2['choices'][2]['accepted']);
        self::assertCount(2, $b2['inputs'][1]['accepted']);
        self::assertTrue($b2['inputs'][1]['punctuation_sensitive']);
        $nominalisation = $this->practice($package['targets'][1]);
        self::assertCount(6, $nominalisation['inputs']);
        self::assertSame('The committee evaluated the two designs on Tuesday. This evaluation revealed a missing label. The committee plans to add the label on Friday.',
            $nominalisation['inputs'][5]['answer']);
        $tone = $this->practice($package['targets'][2]);
        self::assertCount(2, $tone['inputs'][2]['accepted']);
        self::assertSame('If the hall is unavailable, the organiser may move only the Saturday workshop online. Friday’s session will still take place in person.',
            $tone['inputs'][2]['answer']);
    }

    public function test_initial_native_html_contains_complete_basic_keys_no_empty_details_and_unique_ids(): void
    {
        [, $package] = Package::load();
        foreach ($package['targets'] as $i => $target) {
            $html = '';
            foreach (array_slice($target['after']['page']['blocks'], 1, null, true) as $j => $config) {
                $block = new TextBlock;
                $block->forceFill(['id' => 3000 + $i * 100 + $j, 'uuid' => M26DetailPackage::uuid($target['identity'], $config, $j + 1),
                    'seeder' => $target['identity'], 'locale' => 'uk', 'sort_order' => $j + 1, 'type' => $config['type'],
                    'body' => $config['body'], 'level' => $config['level'] ?? null]);
                $block->setRelation('tags', collect()); $block->setRelation('page', null);
                $data = json_decode($config['body'], true, flags: JSON_THROW_ON_ERROR);
                $projection = Package::presentation($block, $data);
                self::assertNotNull($projection);
                $expectedPoints = array_filter($target['plans'][$j - 1]['points'], fn ($p) => $p['detail'] !== '');
                self::assertCount(count($expectedPoints), $projection['points']);
                $fragment = view('theory.partials.content-block', ['block' => $block, 'practiceQuestions' => collect()])->render();
                foreach ($projection['data']['sections'] ?? [] as $point) { self::assertStringContainsString($point['description'], $fragment); }
                foreach ($expectedPoints as $point) { self::assertStringContainsString($point['detail'], $fragment); }
                if ($config['type'] === 'practice-set') {
                    self::assertStringContainsString('data-m32-self-check-no-js', $fragment);
                    foreach ($data['author_self_check']['answers'] as $answer) { self::assertStringContainsString($answer, $fragment); }
                    foreach ($data['author_self_check']['prompts'] as $prompt) { self::assertStringContainsString($prompt, $fragment); }
                }
                $html .= $fragment;
            }
            self::assertSame([0, 1, 0][$i], substr_count($html, 'data-theory-native-extension'));
            self::assertStringNotContainsString('theory-section-toggle-arrow', $html);
            $dom = new DOMDocument; libxml_use_internal_errors(true);
            $dom->loadHTML('<meta charset="UTF-8">'.$html); libxml_clear_errors(); $xpath = new DOMXPath($dom);
            $ids = [];
            foreach ($xpath->query('//*[@id]') as $element) {
                self::assertNotContains($element->getAttribute('id'), $ids); $ids[] = $element->getAttribute('id');
            }
            self::assertSame(6, $xpath->query('//*[@data-m32-author-prompt]')->length);
        }
    }

    public function test_only_explicit_meaningful_author_analyses_are_disclosures(): void
    {
        [, $package] = Package::load(); $actual = [];
        foreach ($package['targets'] as $target) {
            $retained = [];
            foreach ($target['plans'] as $plan) {
                foreach ($plan['points'] as $point => $fragment) {
                    if ($fragment['detail'] === '') { continue; }
                    $retained[] = [$plan['key'], $plan['source_section'], $point];
                    self::assertStringContainsString('The archivists compared the two catalogues on Monday.', $fragment['basic']);
                    self::assertStringContainsString('The performance of a comparison of the two catalogues by the archivists took place on Monday.', $fragment['basic']);
                    self::assertStringContainsString('У понеділок архівісти порівняли два каталоги.', $fragment['basic']);
                    self::assertStringContainsString('Виконання порівняння двох каталогів архівістами відбулося в понеділок.', $fragment['basic']);
                }
            }
            $actual[] = $retained;
        }
        self::assertSame([[], [['m32-nominalisation-section-5', 5, 0]], []], $actual);
    }

    public function test_all_short_candidates_remain_basic_by_explicit_editorial_decision(): void
    {
        [, $package] = Package::load(); $short = 0;
        foreach ($package['targets'] as $target) {
            self::assertCount(6, $target['detail_quality_audit']);
            foreach ($target['detail_quality_audit'] as $candidate) {
                self::assertNotEmpty($candidate['reason']); self::assertNotEmpty($candidate['candidate_html']);
                if ($candidate['detail_words'] >= 30) { continue; }
                $short++; self::assertSame('visible_basic', $candidate['decision']);
            }
        }
        self::assertSame(8, $short);
        self::assertSame('source-final-list', $package['targets'][0]['detail_quality_audit'][3]['point']);
    }

    public static function semanticMutations(): array
    {
        return array_map(fn ($name) => [$name], ['formal-better', 'i-we', 'approve', 'noun-phrase', 'ongoing', 'may',
            'some', 'not-all', 'deadline', 'should', 'condition', 'cause', 'agent', 'invented-agent',
            'attribution', 'translation', 'neighbour', 'anchor', 'answer']);
    }

    #[DataProvider('semanticMutations')]
    public function test_semantic_mutations_fail_closed(string $kind): void
    {
        [$before, $package] = Package::load();
        $pairs = [
            'formal-better' => ['Професійний текст не зобов’язаний звучати урочисто.', 'Професійний текст зобов’язаний звучати урочисто.'],
            'i-we' => ['Короткі слова, активний стан і', 'Короткі слова, активний стан без'],
            'approve' => ['approve → approval', 'approve → poster'],
            'noun-phrase' => ['Не кожна група з іменником', 'Кожна група з іменником'],
            'ongoing' => ['is currently in progress', 'is complete'],
            'may' => ['The panel may approve', 'The panel will approve'],
            'some' => ['Some guests may arrive late', 'All guests may arrive late'],
            'not-all' => ['Not all applicants can attend', 'No applicants can attend'],
            'deadline' => ['by 4 p.m. on Wednesday', 'at 4 p.m. on Wednesday'],
            'should' => ['Visitors should send comments', 'Visitors must send comments'],
            'condition' => ['only if the editor approves it', ''],
            'cause' => ['shorter after the signs were replaced', 'shorter because the signs were replaced'],
            'agent' => ['by our designer', ''],
            'invented-agent' => ['It was announced yesterday', 'The manager announced yesterday'],
            'attribution' => ['Перефразування зовнішнього матеріалу не скасовує посилання на джерело.', ''],
            'translation' => ['У понеділок архівісти порівняли два каталоги.', ''],
        ];
        if (isset($pairs[$kind])) {
            $raw = Package::json($package); [$from, $to] = $pairs[$kind]; self::assertStringContainsString($from, $raw);
            $raw = substr_replace($raw, $to, strpos($raw, $from), strlen($from));
            $package = json_decode($raw, true, flags: JSON_THROW_ON_ERROR);
        } elseif ($kind === 'neighbour') {
            $package['targets'][0]['plans'][4]['points'][0]['detail'] = $package['targets'][1]['plans'][4]['points'][0]['detail'];
        } elseif ($kind === 'anchor') {
            $package['targets'][0]['plans'][1]['key'] = $package['targets'][0]['plans'][0]['key'];
        } else {
            foreach ($package['targets'][0]['after']['page']['blocks'] as &$block) {
                if ($block['type'] !== 'practice-set') { continue; }
                $data = json_decode($block['body'], true); unset($data['choices'][0]['answer']); $block['body'] = Package::json($data); break;
            }
        }
        $this->expectException(RuntimeException::class); Package::validate($before, $package);
    }

    public function test_wrong_owner_uuid_order_locale_or_stored_text_keeps_complete_fallback(): void
    {
        [, $package] = Package::load(); $target = $package['targets'][1]; $config = $target['after']['page']['blocks'][5];
        foreach (['owner', 'uuid', 'order', 'locale', 'body'] as $kind) {
            $block = new TextBlock;
            $block->forceFill(['id' => 3001, 'uuid' => M26DetailPackage::uuid($target['identity'], $config, 6),
                'seeder' => $target['identity'], 'locale' => 'uk', 'sort_order' => 6, 'type' => $config['type'], 'body' => $config['body']]);
            $data = json_decode($config['body'], true);
            if ($kind === 'owner') { $block->seeder = 'Foreign'; }
            elseif ($kind === 'uuid') { $block->uuid = 'foreign'; }
            elseif ($kind === 'order') { $block->sort_order = 99; }
            elseif ($kind === 'locale') { $block->locale = 'pl'; }
            else { $data['sections'][0]['description'] .= ' Additional stored material.'; }
            $block->body = Package::json($data); $block->setRelation('tags', collect()); $block->setRelation('page', null);
            self::assertNull(Package::presentation($block, $data));
            $html = view('theory.partials.content-block', ['block' => $block, 'practiceQuestions' => collect()])->render();
            foreach ($data['sections'] as $point) { self::assertStringContainsString($point['description'], $html); }
            self::assertStringNotContainsString('data-theory-native-extension', $html);
        }
    }
}
