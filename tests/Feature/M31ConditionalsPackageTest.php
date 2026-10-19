<?php

namespace Tests\Feature;

use App\Models\TextBlock;
use App\Services\M31ContentPatch;
use App\Support\M26DetailPackage;
use App\Support\M31ConditionalsPackage as Package;
use DOMDocument;
use DOMXPath;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class M31ConditionalsPackageTest extends TestCase
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
        self::assertSame(M31ContentPatch::NAMES, array_column($package['targets'], 'identity'));
        foreach ($package['targets'] as $i => $target) {
            self::assertSame(['conditionals'], $target['ancestry']);
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
        $banks = ['Database\\Seeders\\V3\\Polyglot\\PolyglotConditionalsWithUnlessProvidedAsLongAsB2LessonSeeder',
            'Database\\Seeders\\V3\\Polyglot\\PolyglotAdvancedConditionalsC1LessonSeeder',
            'Database\\Seeders\\V3\\Polyglot\\PolyglotConditionalAlternativesAndNuanceC2LessonSeeder'];
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
                self::assertCount(2, $data[$group]);
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
        $c2 = $this->practice($package['targets'][2]);
        self::assertTrue($c2['inputs'][0]['punctuation_sensitive']);
        self::assertSame('Should you need a printed copy, contact the librarian. Were the ferry to stop running, we would stay on the island. Had the courier arrived earlier, we could have sent the sample.', $c2['inputs'][0]['answer']);
        self::assertSame('Assuming the grant is confirmed, we might open on Monday. We may open on condition that the director gives written approval; otherwise, we will postpone the opening.', $c2['inputs'][1]['answer']);
        self::assertCount(4, $c2['inputs'][1]['accepted']);
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
                    self::assertStringContainsString('data-m31-self-check-no-js', $fragment);
                    foreach ($data['author_self_check']['answers'] as $answer) { self::assertStringContainsString($answer, $fragment); }
                    foreach ($data['author_self_check']['prompts'] as $prompt) { self::assertStringContainsString($prompt, $fragment); }
                }
                $html .= $fragment;
            }
            self::assertSame([0, 1, 1][$i], substr_count($html, 'data-theory-native-extension'));
            self::assertStringNotContainsString('theory-section-toggle-arrow', $html);
            $dom = new DOMDocument; libxml_use_internal_errors(true);
            $dom->loadHTML('<meta charset="UTF-8">'.$html); libxml_clear_errors(); $xpath = new DOMXPath($dom);
            $ids = [];
            foreach ($xpath->query('//*[@id]') as $element) {
                self::assertNotContains($element->getAttribute('id'), $ids); $ids[] = $element->getAttribute('id');
            }
            self::assertSame(6, $xpath->query('//*[@data-m31-author-prompt]')->length);
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
                    self::assertStringContainsString('<blockquote>', $fragment['basic']);
                    self::assertStringContainsString('<strong>Переклад:</strong>', $fragment['basic']);
                }
            }
            $actual[] = $retained;
        }
        self::assertSame([[], [['m31-c1-section-5', 5, 0]], [['m31-c2-section-6', 6, 0]]], $actual);
    }

    public static function semanticMutations(): array
    {
        return array_map(fn ($name) => [$name], ['unless-polarity', 'if-not-unless', 'as-long-as', 'future-form',
            'past-present', 'mixed-direction', 'might', 'could', 'assumption', 'otherwise', 'but-for', 'inversion',
            'negation-order', 'question', 'translation', 'neighbour', 'anchor', 'answer']);
    }

    #[DataProvider('semanticMutations')]
    public function test_semantic_mutations_fail_closed(string $kind): void
    {
        [$before, $package] = Package::load();
        $pairs = [
            'unless-polarity' => ['Unless you confirm the booking', 'Unless you do not confirm the booking'],
            'if-not-unless' => ['If Olena hadn’t given us a map', 'Unless Olena had given us a map'],
            'as-long-as' => ['<strong>Умова дозволу:</strong>', '<strong>Тривалість:</strong>'],
            'future-form' => ['Provided that Lena arrives by six', 'Provided that Lena will arrive by six'],
            'past-present' => ['she would be living by the sea now', 'she would have lived by the sea last year'],
            'mixed-direction' => ['If Oleh spoke Italian', 'If Oleh had spoken Italian yesterday'],
            'might' => ['she might have postponed the exhibition', 'she would have postponed the exhibition'],
            'could' => ['we could open the archive now', 'we would open the archive now'],
            'assumption' => ['Assuming the hall is free', 'On condition that the hall is free'],
            'otherwise' => ['If you do not keep the receipt', 'If you do not arrive on time'],
            'but-for' => ['The desk was bare but for a blue folder.', 'If it had not been for a blue folder, the desk would be bare.'],
            'inversion' => ['Were the bridge to close', 'Were the bridge close'],
            'negation-order' => ['Had the guide not checked the tide', 'Had not the guide checked the tide'],
            'question' => ['Had Lena not checked the address,', 'Hadn’t Lena checked the address?'],
            'translation' => ['Можеш відтворити діаграму за умови, що вкажеш її автора.', ''],
        ];
        if (isset($pairs[$kind])) {
            $raw = Package::json($package); [$from, $to] = $pairs[$kind]; self::assertStringContainsString($from, $raw);
            $raw = substr_replace($raw, $to, strpos($raw, $from), strlen($from));
            $package = json_decode($raw, true, flags: JSON_THROW_ON_ERROR);
        } elseif ($kind === 'neighbour') {
            $package['targets'][1]['plans'][4]['points'][0]['detail'] = $package['targets'][2]['plans'][5]['points'][0]['detail'];
        } elseif ($kind === 'anchor') {
            $package['targets'][0]['plans'][1]['key'] = $package['targets'][0]['plans'][0]['key'];
        } else {
            foreach ($package['targets'][0]['after']['page']['blocks'] as &$block) {
                if ($block['type'] !== 'practice-set') { continue; }
                $data = json_decode($block['body'], true); unset($data['selects'][0]['answer']); $block['body'] = Package::json($data); break;
            }
        }
        $this->expectException(RuntimeException::class); Package::validate($before, $package);
    }

    public function test_wrong_owner_uuid_order_locale_or_stored_text_keeps_complete_fallback(): void
    {
        [, $package] = Package::load(); $target = $package['targets'][0]; $config = $target['after']['page']['blocks'][1];
        foreach (['owner', 'uuid', 'order', 'locale', 'body'] as $kind) {
            $block = new TextBlock;
            $block->forceFill(['id' => 3001, 'uuid' => M26DetailPackage::uuid($target['identity'], $config, 2),
                'seeder' => $target['identity'], 'locale' => 'uk', 'sort_order' => 2, 'type' => $config['type'], 'body' => $config['body']]);
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
