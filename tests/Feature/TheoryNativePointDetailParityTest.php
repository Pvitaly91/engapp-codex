<?php

namespace Tests\Feature;

use App\Support\TheoryComponents;
use App\Support\TheoryPointDetailAdapter;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use Illuminate\Support\HtmlString;
use Tests\TestCase;

class TheoryNativePointDetailParityTest extends TestCase
{
    private function xpath(string $html): DOMXPath
    {
        $document = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body>'.$html.'</body></html>', LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        return new DOMXPath($document);
    }

    /** Preserve all hierarchy/styles/roles. Only canonical markers and added language hints differ. */
    private function comparableDom(string $html): array
    {
        $walk = function (DOMNode $node) use (&$walk): mixed {
            if ($node->nodeType === XML_TEXT_NODE) {
                $text = trim(preg_replace('/\s+/u', ' ', $node->textContent));
                return $text === '' ? null : ['text' => $text];
            }
            if (!$node instanceof DOMElement) { return null; }
            $attributes = [];
            foreach ($node->attributes as $attribute) {
                if ($attribute->name === 'data-theory-component' || $attribute->name === 'lang') { continue; }
                $attributes[$attribute->name] = $attribute->name === 'class'
                    ? trim(preg_replace('/\s+/u', ' ', $attribute->value)) : $attribute->value;
            }
            ksort($attributes);
            $children = [];
            foreach ($node->childNodes as $child) {
                $value = $walk($child);
                if ($value !== null) { $children[] = $value; }
            }
            return ['tag' => $node->tagName, 'attributes' => $attributes, 'children' => $children];
        };
        return $walk($this->xpath($html)->query('//body')->item(0));
    }

    public function test_all_native_fragment_types_keep_reference_hierarchy_typography_tone_and_spacing(): void
    {
        $example = ['en' => 'They had been waiting.', 'ua' => 'Вони чекали.'];
        $fixtures = [
            ['intro', 'Підмет — це <strong>той, про кого</strong> говоримо.'],
            ['warning', 'Не пропускай <strong>been</strong>.'],
            ['summary-list', 'Спершу відбувався процес; потім настала інша подія.'],
            ['forms-grid', ['label' => 'Скорочення', 'title' => 'had → ’d', 'subtitle' => 'They’d been practising. — Вони репетирували.']],
            ['comparison-table', $example + ['note' => 'Процес <strong>до</strong> іншої події.']],
            ['usage-panels', ['label' => 'Тривалість до події', 'description' => 'Рахуємо час <strong>до</strong> минулого моменту.', 'examples' => [$example], 'note' => 'Часовий зв’язок задає контекст.']],
            ['usage-panels', ['description' => 'Окремий приклад.', 'examples' => [$example]]],
            ['supplement', ['label' => 'Контекст', 'description' => 'Написання <не HTML> залишається текстом.', 'examples' => [$example], 'note' => 'Форма <em>had been</em> не змінюється.']],
            ['supplement', ['description' => 'Додаткове пояснення.', 'examples' => [$example]]],
        ];
        foreach ($fixtures as $index => [$type, $value]) {
            $fragment = ['id' => 'native-detail-'.$index, 'type' => $type, 'value' => $value];
            $original = view('courses.compatibility.theory.point-detail-fragment', compact('fragment'))->render();
            $canonical = TheoryComponents::html(TheoryPointDetailAdapter::fragment($fragment));
            self::assertSame($this->comparableDom($original), $this->comparableDom($canonical), $type.' keeps reference DOM/classes, not only inherited appearance');
            $xpath = $this->xpath($canonical);
            self::assertSame(1, $xpath->query('//section[@id="native-detail-'.$index.'"]')->length);
            self::assertSame(0, $xpath->query('//p//p|//p//section|//p//div|//details')->length);
        }
    }

    public function test_fragment_semantics_are_generic_allowlisted_and_preserve_default_author_rendering(): void
    {
        $node = ['kind' => 'fragment', 'title' => 'Заголовок', 'body_html' => new HtmlString('<p lang="uk">Авторський абзац.</p>')];
        $default = TheoryComponents::html($node);
        $xpath = $this->xpath($default);
        self::assertSame('theory-point-fragment text-sm leading-relaxed', $xpath->query('//*[@data-theory-component="fragment"]')->item(0)->getAttribute('class'));
        self::assertSame('font-bold', $xpath->query('//h4')->item(0)->getAttribute('class'));
        self::assertSame(1, $xpath->query('//p[@lang="uk"]')->length);
        $invalid = TheoryComponents::html($node + ['tag' => 'script onclick="alert(1)"', 'body_role' => 'script', 'body_tone' => 'style=color:red', 'content_layout' => 'onclick']);
        self::assertSame($this->comparableDom($default), $this->comparableDom($invalid));
        self::assertSame(0, $this->xpath($invalid)->query('//script|//*[@onclick or @style]')->length);
        $paragraph = TheoryComponents::html(['kind' => 'paragraph', 'html' => '<strong>Текст</strong>', 'tone' => 'muted']);
        self::assertStringContainsString('&lt;strong&gt;', $paragraph);
        self::assertSame('text-sm text-muted-foreground leading-relaxed', $this->xpath($paragraph)->query('//p')->item(0)->getAttribute('class'));
    }
}
