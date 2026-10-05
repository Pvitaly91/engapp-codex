<?php

namespace Tests\Feature;

use App\Models\TextBlock;
use App\Support\M26DetailPackage;
use App\Support\M26InteractivePractice;
use App\Support\PastPerfectContinuousPracticeQuality as Quality;
use App\Support\TextBlock\TextBlockUuidGenerator;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Tests\Support\IsolatedTestEnvironment;
use Tests\TestCase;

class PastPerfectContinuousStaticPracticeQualityTest extends TestCase
{
    protected function setUp(): void { parent::setUp(); app()->setLocale('uk'); $this->withoutVite(); }

    private function block(array $target, string $locale, array $data): TextBlock
    {
        $owner = $locale === 'uk' ? $target['identity']
            : 'Database\\Seeders\\Page_V3\\Localizations\\'.ucfirst($locale).'\\'
                .str_replace('TheorySeeder', 'TheoryLocalizationSeeder', basename(str_replace('\\', '/', $target['identity'])));
        $block = new TextBlock;
        $block->forceFill(['id' => 107, 'uuid' => TextBlockUuidGenerator::generateWithKey($target['identity'].'::'.$locale, $target['uuid_key']),
            'seeder' => $owner, 'locale' => $locale, 'type' => 'practice-set', 'sort_order' => $target['sort_order'],
            'body' => M26DetailPackage::json($data), 'level' => 'A2–B1']);
        $block->setRelation('tags', collect());
        return $block;
    }

    private function wordBag(string $text): array
    {
        $words = preg_split('/\s+/u', trim(preg_replace('/[^\p{L}\p{N}]+/u', ' ', mb_strtolower($text))), flags: PREG_SPLIT_NO_EMPTY);
        $bag = array_count_values($words); ksort($bag); return $bag;
    }

    public function test_next_version_changes_only_practice_and_preserves_frozen_author_packages(): void
    {
        [$master, $before] = M26DetailPackage::load();
        $newTargets = Quality::load()['targets'];
        foreach ($master['targets'] as $target) {
            $base = $before['definitions'][$target['identity']];
            $old = M26InteractivePractice::definition($target, $base);
            $new = Quality::definition($target, $base);
            self::assertSame($new, json_decode(File::get(base_path($target['definition_path'])), true, flags: JSON_THROW_ON_ERROR));
            if (!isset($target['practice_insert'])) { self::assertSame($old, $new); continue; }
            $root = $target['source_content_root']; $i = count($old[$root]['blocks']) - 1;
            $new[$root]['blocks'][$i]['body'] = $old[$root]['blocks'][$i]['body'];
            self::assertSame($old, $new, 'All theory, progressive_v2, UUID keys, levels, tags and ordering stay exact.');
        }
        self::assertCount(4, $newTargets);
        self::assertSame(M26InteractivePractice::SOURCE_SHA, hash_file('sha256', base_path(M26InteractivePractice::SOURCE)));
        self::assertSame(M26DetailPackage::MASTER_SHA, hash_file('sha256', base_path(M26DetailPackage::MASTER)));
    }

    public function test_all_twenty_four_tasks_in_three_locales_have_synced_answers_tokens_and_visible_conditions(): void
    {
        $counts = []; $unique = [];
        foreach (Quality::load()['targets'] as $target) {
            foreach (['uk', 'en', 'pl'] as $locale) {
                $data = $target['body_data'][$locale]; $counts[$locale] ??= 0;
                foreach (['selects', 'choices', 'inputs'] as $group) {
                    foreach ($data[$group] as $item) {
                        self::assertNotSame('', trim($item['prompt']));
                        if ($locale !== 'uk') { self::assertDoesNotMatchRegularExpression('/[А-Яа-яІіЇїЄєҐґ]/u', M26DetailPackage::json($item)); }
                        $counts[$locale]++;
                        if ($locale === 'uk') {
                            $fingerprint = $group.'|'.preg_replace('/\s+/u', ' ', mb_strtolower(($item['label'] ?? $item['before']).'|'.$item['answer']));
                            self::assertNotContains($fingerprint, $unique); $unique[] = $fingerprint;
                        }
                    }
                }
                foreach ($data['selects'] as $item) { self::assertContains($item['answer'], $data['options']); }
                foreach ($data['choices'] as $item) { self::assertContains($item['answer'], $data['choice_options']); }
                foreach ($data['inputs'] as $item) {
                    self::assertSame($this->wordBag($item['answer']), $this->wordBag(str_replace('/', ' ', $item['before'])));
                    self::assertStringContainsString($locale === 'uk' ? 'Почни' : ($locale === 'en' ? 'Begin' : 'Zacznij'), $item['prompt']);
                    if (isset($item['accepted'])) { self::assertContains($item['answer'], $item['accepted']); }
                }
                if ($locale === 'uk') { continue; }
                $path = str_replace('definition.json', 'localizations/'.$locale.'.json', $target['definition_path']);
                $loc = json_decode(File::get(base_path($path)), true, flags: JSON_THROW_ON_ERROR);
                $practice = array_values(array_filter($loc['blocks'], fn ($b) => ($b['uuid_key'] ?? null) === $target['uuid_key']));
                self::assertCount(1, $practice); self::assertSame($data, json_decode($practice[0]['body'], true, flags: JSON_THROW_ON_ERROR));
            }
        }
        self::assertSame(['uk' => 24, 'en' => 24, 'pl' => 24], $counts);
        $anna = Quality::load()['targets'][0]['body_data']['uk']['inputs'][1];
        self::assertStringContainsString('Анна знала Лева', $anna['prompt']);
        self::assertSame('By 2020, Anna had known Lev for five years.', $anna['answer']);
    }

    public function test_projection_matches_exact_owned_old_and_new_bodies_and_all_localized_rows_only(): void
    {
        $old = M26InteractivePractice::load();
        foreach (Quality::load()['targets'] as $i => $target) {
            $legacy = $old['targets'][$i]['practice']['body_data']; $block = $this->block($target, 'uk', $legacy);
            foreach (['uk', 'en', 'pl', 'ua'] as $locale) {
                self::assertSame($target['body_data'][$locale === 'ua' ? 'uk' : $locale], Quality::presentation($block, $legacy, $locale));
            }
            foreach (['uk', 'en', 'pl'] as $locale) {
                $data = $target['body_data'][$locale]; $block = $this->block($target, $locale, $data);
                self::assertSame($data, Quality::presentation($block, $data, $locale));
                foreach (['seeder' => 'foreign', 'uuid' => 'foreign', 'sort_order' => 999, 'type' => 'box', 'locale' => 'de'] as $field => $value) {
                    $bad = clone $block; $bad->$field = $value;
                    self::assertNull(Quality::presentation($bad, $data, $locale), 'Foreign '.$field.' cannot choose a projection.');
                }
                $bad = $data; $bad['inputs'][0]['answer'] .= ' changed';
                self::assertNull(Quality::presentation($block, $bad, $locale));
                $bad = $data; unset($bad['inputs'][0]['prompt']);
                self::assertNull(Quality::presentation($block, $bad, $locale));
                self::assertNull(Quality::presentation($block, $data, 'de'));
            }
        }
    }

    public function test_renderer_exposes_each_condition_before_its_word_bank_on_all_four_pages_and_languages(): void
    {
        foreach (Quality::load()['targets'] as $target) {
            foreach (['uk', 'en', 'pl'] as $locale) {
                app()->setLocale($locale); $data = $target['body_data'][$locale]; $block = $this->block($target, $locale, $data);
                $html = view('theory.partials.content-block', ['block' => $block, 'practiceQuestions' => collect()])->render();
                $dom = new \DOMDocument; $old = libxml_use_internal_errors(true);
                try { $dom->loadHTML('<meta charset="UTF-8">'.$html, LIBXML_NONET); }
                finally { libxml_clear_errors(); libxml_use_internal_errors($old); }
                $xpath = new \DOMXPath($dom);
                foreach ($data['inputs'] as $i => $item) {
                    $bank = $xpath->query('//template[@x-for="token in inputTokenBank('.$i.')"]')->item(0);
                    self::assertNotNull($bank);
                    $row = $bank->parentNode->parentNode->parentNode;
                    self::assertStringContainsString($item['prompt'], preg_replace('/\s+/u', ' ', $row->textContent));
                }
                if ($locale !== 'uk') { self::assertStringNotContainsString('Склади англійське речення за українським', $html); }
            }
        }
    }

    public function test_modified_next_version_package_fails_exact_sha_without_changing_frozen_sources(): void
    {
        $root = storage_path('app/ppc-practice-fixture-'.bin2hex(random_bytes(8)));
        IsolatedTestEnvironment::assertOwnedPath(dirname($root)); File::makeDirectory($root, 0700);
        foreach ([M26DetailPackage::MASTER, M26DetailPackage::BEFORE, M26InteractivePractice::SOURCE, Quality::SOURCE] as $path) {
            File::makeDirectory(dirname($root.'/'.$path), 0700, true, true); File::copy(base_path($path), $root.'/'.$path);
        }
        self::assertSame(Quality::load(), Quality::load($root)); File::append($root.'/'.Quality::SOURCE, ' ');
        $this->expectException(RuntimeException::class); Quality::load($root);
    }
}
