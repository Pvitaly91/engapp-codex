<?php

namespace Tests\Feature;

use App\Models\TextBlock;
use App\Services\M30ContentPatch;
use App\Support\M26DetailPackage;
use App\Support\M30ParticipleClausesPackage as Package;
use DOMDocument;
use DOMXPath;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class M30ParticipleClausesPackageTest extends TestCase
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
        self::assertSame(M30ContentPatch::NAMES, array_column($package['targets'], 'identity'));
        foreach ($package['targets'] as $i => $target) {
            self::assertSame(['clauses-and-linking-words'], $target['ancestry']);
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
        $banks = ['Database\\Seeders\\V3\\Polyglot\\PolyglotParticipleClausesBasicsB2LessonSeeder',
            'Database\\Seeders\\V3\\Polyglot\\PolyglotParticipleClausesC1LessonSeeder',
            'Database\\Seeders\\V3\\Polyglot\\PolyglotAdvancedParticipleAndAbsoluteClausesC2LessonSeeder'];
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
        self::assertSame('The lights having been switched off, the guard locked the hall.', $c2['inputs'][0]['answer']);
        self::assertSame('After the rehearsal had ended, the instruments were packed. The players were tired. The conductor thanked everyone.', $c2['inputs'][1]['answer']);
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
                self::assertNotNull($projection); self::assertSame([], $projection['points']);
                $fragment = view('theory.partials.content-block', ['block' => $block, 'practiceQuestions' => collect()])->render();
                foreach ($data['sections'] ?? [] as $point) { self::assertStringContainsString($point['description'], $fragment); }
                if ($config['type'] === 'practice-set') {
                    self::assertStringContainsString('data-m30-self-check-no-js', $fragment);
                    foreach ($data['author_self_check']['answers'] as $answer) { self::assertStringContainsString($answer, $fragment); }
                    foreach ($data['author_self_check']['prompts'] as $prompt) { self::assertStringContainsString($prompt, $fragment); }
                }
                $html .= $fragment;
            }
            self::assertStringNotContainsString('data-theory-native-extension', $html);
            self::assertStringNotContainsString('theory-section-toggle-arrow', $html);
            $dom = new DOMDocument; libxml_use_internal_errors(true);
            $dom->loadHTML('<meta charset="UTF-8">'.$html); libxml_clear_errors(); $xpath = new DOMXPath($dom);
            $ids = [];
            foreach ($xpath->query('//*[@id]') as $element) {
                self::assertNotContains($element->getAttribute('id'), $ids); $ids[] = $element->getAttribute('id');
            }
            self::assertSame(6, $xpath->query('//*[@data-m30-author-prompt]')->length);
        }
    }

    public static function semanticMutations(): array
    {
        return array_map(fn ($name) => [$name], ['ing-v3', 'active-passive', 'subject-object', 'invent-reader', 'not', 'might',
            'lexical-having', 'perfect-having', 'absolute-subject', 'comma', 'with', 'comma-splice', 'translation', 'neighbour', 'anchor', 'answer']);
    }

    #[DataProvider('semanticMutations')]
    public function test_semantic_mutations_fail_closed(string $kind): void
    {
        [$before, $package] = Package::load();
        $pairs = ['ing-v3' => ['printed', 'printing'], 'active-passive' => ['Having been inspected', 'Having inspected'],
            'subject-object' => ['the bus', 'we'], 'invent-reader' => ['the parcel fell', 'the courier read'],
            'not' => ['Not having', 'Having'], 'might' => ['might', 'must'], 'lexical-having' => ['Having a spare key', 'Having found the spare key'],
            'perfect-having' => ['Having checked', 'Having checking'], 'absolute-subject' => ['The passengers waiting', 'Waiting'],
            'comma' => ['The lights having been switched off, the guard locked the hall.', 'The lights, having been switched off the guard locked the hall.'],
            'with' => ['with a cracked lid', 'a cracked lid'], 'comma-splice' => ['The wind was rising, we secured the boat.', 'The wind rising we secured the boat.'],
            'translation' => ['Жінка зі скрипкою в руках', '']];
        if (isset($pairs[$kind])) {
            $raw = Package::json($package); [$from, $to] = $pairs[$kind]; self::assertStringContainsString($from, $raw);
            $raw = substr_replace($raw, $to, strpos($raw, $from), strlen($from));
            $package = json_decode($raw, true, flags: JSON_THROW_ON_ERROR);
        } elseif ($kind === 'neighbour') {
            [$package['targets'][0]['plans'][0]['points'][0]['basic'], $package['targets'][0]['plans'][0]['points'][1]['basic']] =
                [$package['targets'][0]['plans'][0]['points'][1]['basic'], $package['targets'][0]['plans'][0]['points'][0]['basic']];
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
