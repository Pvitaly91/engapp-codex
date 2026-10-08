<?php

namespace Tests\Unit;

use App\Support\M43AuthoredTenseUsagePackage as Package;
use App\Support\M43NativeHtml;
use DOMDocument;
use DOMXPath;
use PHPUnit\Framework\TestCase;

/** Presentation changes may add UI, but never alter escaped author material or stored sources. */
class M431NativePresentationTest extends TestCase
{
    private function xpath(string $html): DOMXPath
    {
        $dom = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        $dom->loadHTML('<html><head><meta charset="UTF-8"></head><body>'.$html.'</body></html>', LIBXML_NONET);
        libxml_clear_errors(); libxml_use_internal_errors($previous);
        return new DOMXPath($dom);
    }

    private function learnerText(DOMXPath $xp): string
    {
        foreach (iterator_to_array($xp->query('//*[@data-theory-ui]')) as $node) {
            $node->parentNode->removeChild($node);
        }
        return $xp->query('//body')->item(0)->textContent;
    }

    public function test_examples_keep_languages_notes_and_literal_potentially_executable_text(): void
    {
        $example = [
            'en' => 'She said "<script>alert(1)</script>" & <img src=x onerror=alert(2)>.',
            'uk' => 'Вона сказала «<svg onload=alert(3)>» — апостроф: \' та &.',
            'note_uk' => 'Окрема примітка: <a href="javascript:alert(4)">текст</a>.',
        ];
        $xp = $this->xpath(M43NativeHtml::examples([$example]));
        self::assertSame(0, $xp->query('//script | //img | //svg | //a | //*[@onerror or @onload]')->length);
        self::assertSame(['en', 'uk', 'uk'], array_map(fn ($node) => $node->getAttribute('lang'),
            iterator_to_array($xp->query('//p'))));
        self::assertSame([$example['en'], $example['uk'], $example['note_uk']], array_map(fn ($node) => $node->textContent,
            iterator_to_array($xp->query('//p'))));
        self::assertSame(1, $xp->query('//*[@data-theory-ui and @aria-hidden="true"]')->length);
        self::assertSame(1, $xp->query('//p[contains(@class,"m43-example-note")]')->length);
    }

    public function test_all_six_stored_details_and_eighteen_answer_keys_keep_inner_bytes_and_sources(): void
    {
        $root = dirname(__DIR__, 2);
        $paths = [Package::MASTER_PATH, Package::MAPPING_PATH, Package::BEFORE, Package::SOURCE];
        $beforeHashes = array_map(fn ($path) => hash_file('sha256', $root.'/'.$path), $paths);
        [$before, $package] = Package::load($root);
        Package::validate($before, $package, $root);
        $details = 0; $answers = 0; $examples = 0;
        foreach ($package['targets'] as $target) {
            foreach ($target['after']['page']['blocks'] as $block) {
                $data = json_decode($block['body'], true, flags: JSON_THROW_ON_ERROR);
                $fragments = [];
                foreach ($data['point_plan'] ?? [] as $point) {
                    if (isset($point['detail_html'])) { $fragments[] = $point['detail_html']; $details++; }
                }
                foreach ($data['author_self_check']['answers'] ?? [] as $answer) {
                    $fragments[] = $answer; $answers++;
                }
                foreach ($fragments as $html) {
                    $decorated = M43NativeHtml::decorateStoredExamples($html);
                    self::assertSame($decorated, M43NativeHtml::decorateStoredExamples($decorated), 'Decoration is applied once.');
                    preg_match_all('/<div class="theory-example">(.*?)<\/div>/s', $html, $matches);
                    foreach ($matches[1] as $inner) { self::assertStringContainsString($inner, $decorated); $examples++; }
                    $original = $this->xpath($html); $rendered = $this->xpath($decorated);
                    self::assertSame(count($matches[1]), $rendered->query('//*[@data-theory-ui and @aria-hidden="true"]')->length);
                    self::assertSame($this->learnerText($original), $this->learnerText($rendered));
                    self::assertSame(array_map(fn ($node) => [$node->getAttribute('lang'), $node->textContent],
                        iterator_to_array($original->query('//*[@lang]'))),
                        array_map(fn ($node) => [$node->getAttribute('lang'), $node->textContent],
                            iterator_to_array($rendered->query('//*[@lang]'))));
                }
            }
        }
        self::assertSame([6, 18], [$details, $answers]);
        self::assertGreaterThan(24, $examples);
        self::assertSame($beforeHashes, array_map(fn ($path) => hash_file('sha256', $root.'/'.$path), $paths));
    }

    public function test_other_native_html_is_not_decorated(): void
    {
        $html = '<div class="other-example"><p lang="en">Leave this text alone.</p></div>';
        self::assertSame($html, M43NativeHtml::decorateStoredExamples($html));
    }

    public function test_optional_form_description_tone_preserves_escaped_paragraphs_and_default_callers(): void
    {
        $paragraphs = ['Пояснення з <script>alert(1)</script> і &.', 'Другий абзац: \' та «лапки».'];
        $default = M43NativeHtml::paragraphs($paragraphs);
        self::assertSame($default, M43NativeHtml::paragraphs($paragraphs, false));
        $plain = $this->xpath($default); $muted = $this->xpath(M43NativeHtml::paragraphs($paragraphs, true));
        self::assertSame($this->learnerText($plain), $this->learnerText($muted));
        self::assertSame(0, $muted->query('//script')->length);
        self::assertSame(2, $muted->query('//p[@lang="uk" and @class="text-muted-foreground"]')->length);
        self::assertSame(0, $plain->query('//p[@class]')->length);
    }

    public function test_all_eighteen_prompt_headings_keep_author_inner_bytes_paragraphs_and_languages(): void
    {
        [, $package] = Package::load(dirname(__DIR__, 2));
        $prompts = 0;
        foreach ($package['targets'] as $target) {
            foreach ($target['after']['page']['blocks'] as $block) {
                $data = json_decode($block['body'], true, flags: JSON_THROW_ON_ERROR);
                foreach ($data['author_self_check']['prompts'] ?? [] as $sourceIndex => $html) {
                    $prompts++;
                    $number = $sourceIndex + 1;
                    $decorated = M43NativeHtml::decoratePracticePrompt($html, $number);
                    self::assertSame($decorated, M43NativeHtml::decoratePracticePrompt($decorated, $number));
                    preg_match('/<h4>(.*?)<\/h4>/s', $html, $title);
                    self::assertStringContainsString($title[1], $decorated);
                    $original = $this->xpath($html); $rendered = $this->xpath($decorated);
                    self::assertSame(1, $rendered->query('//h4/span[@data-theory-ui and @aria-hidden="true"]')->length);
                    self::assertSame((string) $number, $rendered->query('//h4/span[@data-theory-ui]')->item(0)->textContent);
                    self::assertSame($this->learnerText($original), $this->learnerText($rendered));
                    self::assertSame(1, $rendered->query('//h4')->length);
                    self::assertSame(array_map(fn ($node) => [$node->getAttribute('lang'), $node->textContent],
                        iterator_to_array($original->query('//p | //*[@lang]'))),
                        array_map(fn ($node) => [$node->getAttribute('lang'), $node->textContent],
                            iterator_to_array($rendered->query('//p | //*[@lang]'))));
                }
            }
        }
        self::assertSame(18, $prompts);
    }
}
