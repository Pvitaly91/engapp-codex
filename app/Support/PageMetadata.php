<?php

namespace App\Support;

/** Pure Ukrainian SEO copy from already resolved public context; never queries or mutates models. */
final class PageMetadata
{
    private const SECTIONS = [
        'Forms and Use' => 'форми та вживання',
        'Questions and Short Answers' => 'питання та короткі відповіді',
        'Time Expressions' => 'часові маркери',
        'Adverbs of Frequency' => 'прислівники частоти',
        'Present Forms' => 'форми теперішнього часу',
        'Past Forms' => 'форми минулого часу',
        'Common Mistakes' => 'типові помилки',
        'Choosing the Right Form' => 'вибір правильної форми',
        'Negatives' => 'заперечення',
        'Questions' => 'питальні речення',
        'Forms' => 'форми',
        'Future' => 'майбутній час',
    ];

    public static function plain(?string $value): string
    {
        // Collapse one legacy double-encoding layer, then decode once. Never emit raw markup.
        $value = preg_replace('/&amp;(?=(?:[a-z][a-z0-9]+|#\d+|#x[0-9a-f]+);)/i', '&', $value ?? '');
        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = preg_replace('~<(script|style|iframe|object|svg|template)\b[^>]*>.*?(?:</\1\s*>|$)~isu', ' ', $value);
        $value = preg_replace('~<(?:br\s*/?|/p|/div|/li)>~iu', ' ', $value);
        $value = trim(preg_replace('/[\s\x{00a0}]+/u', ' ', strip_tags($value)));

        return preg_match('/^(?:public|frontend)\.[a-z0-9_.]+$/i', $value) ? '' : $value;
    }

    public static function topic(string $name, string $category = ''): string
    {
        $name = self::plain($name);
        $category = self::plain($category);
        foreach (self::SECTIONS as $english => $ukrainian) {
            if (strcasecmp($name, $english) === 0) {
                return ($category !== '' ? $category.': ' : '').$ukrainian;
            }
            if (preg_match('/^(.+?)\s*:\s*'.preg_quote($english, '/').'$/ui', $name, $match)
                || preg_match('/^((?:Present|Past|Future) (?:Simple|Continuous|Perfect(?: Continuous)?)|Verb to Be)\s+'.preg_quote($english, '/').'$/ui', $name, $match)) {
                return $match[1].': '.$ukrainian;
            }
        }

        return $name;
    }

    public static function title(string $subject, string $kind): string
    {
        $subject = trim(preg_replace('/\bGramlyze\b/ui', '', self::plain($subject)), " \t\n\r|—–-");
        $kind = in_array($kind, ['правила', 'курс'], true) && preg_match('/\b'.preg_quote($kind, '/').'\b/ui', $subject) ? '' : $kind;

        return $subject.($kind !== '' ? ' — '.$kind : '').' | Gramlyze';
    }

    private static function summary(string $intro): string
    {
        $intro = self::plain($intro);
        if ($intro === '' || ! preg_match('/[а-яіїєґ]/ui', $intro)) {
            return '';
        }
        // Prefer a complete first sentence; never cut a word, formula or unfinished list to fit N characters.
        if (preg_match('/^(.+?[.!?])(?:\s+[\p{Lu}]|$)/u', $intro, $match)) {
            return $match[1];
        }

        return rtrim($intro, '.').'.';
    }

    public static function theory(string $name, string $category = '', string $intro = ''): array
    {
        $topic = self::topic($name, $category);
        $topic = $topic !== '' ? $topic : (self::plain($category) ?: 'Англійська граматика');
        $summary = self::summary($intro);
        $description = $summary !== ''
            ? (str_starts_with(mb_strtolower($summary), mb_strtolower($topic)) ? $summary : $topic.'. '.$summary)
            : 'Пояснення теми «'.$topic.'» в англійській граматиці.';

        return ['title' => self::title($topic, 'правила'), 'description' => $description];
    }

    public static function test(string $name, array $breadcrumbs = []): array
    {
        // Existing controller breadcrumbs are based on the resolved Page, never the URL's query or question order.
        $last = count($breadcrumbs) > 2 ? $breadcrumbs[array_key_last($breadcrumbs)] : [];
        $category = count($breadcrumbs) > 3 ? $breadcrumbs[count($breadcrumbs) - 2]['label'] ?? '' : '';
        $linkedName = self::plain($last['label'] ?? '');
        $name = $linkedName !== '' ? $linkedName : preg_replace('/\s*\((?:Mixed A1[-–]C2|All levels)\)\s*$/ui', '', $name);
        $topic = self::topic($name, $category);
        $topic = $topic !== '' ? $topic : 'Англійська граматика';
        $practice = match (true) {
            str_contains($topic, ': заперечення') => 'Тренуйте утворення заперечних речень і перевіряйте свої відповіді.',
            str_contains($topic, ': питання'), str_contains($topic, ': питальні') => 'Тренуйте побудову питальних речень і перевіряйте свої відповіді.',
            str_contains($topic, ': часові маркери') => 'Тренуйте вживання часових виразів у реченнях і перевіряйте свої відповіді.',
            str_contains($topic, ': форми') => 'Тренуйте граматичні форми та їх уживання в реченнях.',
            default => 'Виконайте граматичні завдання за цією темою й перевірте свої відповіді.',
        };

        return ['title' => self::title($topic, 'тест'), 'description' => 'Тест «'.$topic.'». '.$practice];
    }

    public static function category(string $name, string $intro = '', array $lessonNames = []): array
    {
        $topic = self::plain($name) ?: 'Англійська граматика';
        $summary = self::summary($intro);
        $lessons = array_values(array_unique(array_filter(array_map(self::plain(...), $lessonNames))));
        // The caller supplies the category's stable ordered lessons, not the random related-page widget.
        $description = $summary !== '' ? (str_starts_with(mb_strtolower($summary), mb_strtolower($topic)) ? $summary : $topic.'. '.$summary)
            : ($lessons !== [] ? 'Матеріали розділу «'.$topic.'»: '.implode('; ', array_slice($lessons, 0, 3)).'.'
                : 'Тема «'.$topic.'»: оберіть підрозділ для вивчення англійської граматики.');

        return ['title' => self::title($topic, 'теми й правила'), 'description' => $description];
    }

    public static function course(array $course): array
    {
        $name = self::plain($course['name'] ?? '') ?: 'Курс англійської';
        $description = match ($course['slug'] ?? '') {
            'english-grammar-theory' => $name.' — послідовне вивчення граматики за сторінками теорії: уроки за темами, пов’язані тести та прогрес на цьому пристрої.',
            'theory-driven' => $name.' — навчання за темами теорії зі змішаними тестами та покроковим відкриттям уроків.',
            default => $name.' — курс побудови англійських речень із послідовними уроками та практичними завданнями.',
        };

        return ['title' => self::title($name, 'курс'), 'description' => $description];
    }
}
