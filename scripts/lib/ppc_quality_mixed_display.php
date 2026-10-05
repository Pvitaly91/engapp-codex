<?php

declare(strict_types=1);

/**
 * Source-only presentation metadata for the exact 288 authored Mixed questions.
 * English gap stems and all assessment data stay in their canonical definitions.
 * These finite editorial moves are materialized into a package; runtime never
 * guesses sentence boundaries, removes generic prefixes or truncates by length.
 */
function ppcQualityMixedPresentationMoves(): array
{
    return [
        'pastpc-forms-v3-a1-12' => [
            'uk' => ['suffix', '; тут описано стан', 'Тут описано стан.'],
            'pl' => ['suffix', '; opisano tu stan', 'Opisano tu stan.'],
        ],
        'pastpc-forms-v3-a2-12' => [
            'uk' => ['suffix', '; володіння — це стан', 'Володіння — це стан.'],
            'pl' => ['suffix', '; posiadanie jest stanem', 'Posiadanie jest stanem.'],
        ],
        'pastpc-forms-v3-b2-12' => [
            'uk' => ['suffix', '; тут описано думку, а не діяльність', 'Тут описано думку, а не діяльність.'],
            'pl' => ['suffix', '; opisano tu opinię, nie czynność', 'Opisano tu opinię, nie czynność.'],
        ],
        'pastpc-forms-v3-c1-10' => [
            'uk' => ['suffix', '; речення підкреслює тривалу практику', 'Речення підкреслює тривалу практику.'],
            'pl' => ['suffix', '; zdanie podkreśla długotrwałą praktykę', 'Zdanie podkreśla długotrwałą praktykę.'],
        ],
        'pastpc-forms-v3-c1-12' => [
            'uk' => ['suffix', '; йдеться про юридичне володіння', 'Йдеться про юридичне володіння.'],
            'pl' => ['suffix', '; chodzi o własność prawną', 'Chodzi o własność prawną.'],
        ],
        'pastpc-forms-v3-c2-12' => [
            'uk' => ['suffix', '; твердження стосується завершених результатів', 'Твердження стосується завершених результатів.'],
            'pl' => ['suffix', '; twierdzenie dotyczy ukończonych wyników', 'Twierdzenie dotyczy ukończonych wyników.'],
        ],
        'pastpc-time-expressions-v3-a2-07' => [
            'uk' => ['prefix', 'Нас цікавить загальна тривалість, а не початок: ', 'Нас цікавить загальна тривалість, а не початок.'],
            'pl' => ['prefix', 'Chcemy poznać łączny czas trwania, nie początek: ', 'Chcemy poznać łączny czas trwania, nie początek.'],
        ],
        'pastpc-time-expressions-v3-b1-07' => [
            'uk' => ['prefix', 'Гід хоче знати минулу тривалість, а не календарну дату: ', 'Гід хоче знати минулу тривалість, а не календарну дату.'],
            'pl' => ['prefix', 'Przewodnik chce poznać czas trwania, nie datę: ', 'Przewodnik chce poznać czas trwania, nie datę.'],
        ],
        'pastpc-time-expressions-v3-b2-07' => [
            'uk' => ['prefix', 'Аудиту потрібна тривалість, а не дата початку: ', 'Аудиту потрібна тривалість, а не дата початку.'],
            'pl' => ['prefix', 'Audyt potrzebuje czasu trwania, nie daty rozpoczęcia: ', 'Audyt potrzebuje czasu trwania, nie daty rozpoczęcia.'],
        ],
        'pastpc-time-expressions-v3-c1-07' => [
            'uk' => ['prefix', 'Під час розслідування запитують про загальний минулий проміжок незалежно від перерв: ', 'Під час розслідування запитують про загальний минулий проміжок незалежно від перерв.'],
            'pl' => ['prefix', 'W dochodzeniu pytają o cały miniony okres niezależnie od przerw: ', 'W dochodzeniu pytają o cały miniony okres niezależnie od przerw.'],
        ],
        'pastpc-time-expressions-v3-c2-07' => [
            'uk' => ['prefix', 'Трибуналу потрібен загальний минулий проміжок, а не дата першої заяви: ', 'Трибуналу потрібен загальний минулий проміжок, а не дата першої заяви.'],
            'pl' => ['prefix', 'Trybunał potrzebuje całego minionego okresu, nie daty pierwszej deklaracji: ', 'Trybunał potrzebuje całego minionego okresu, nie daty pierwszej deklaracji.'],
        ],
        'pastpc-time-expressions-v3-b2-12' => [
            'uk' => ['question-suffix', ', а не як довго тривала кожна зупинка', 'Питання стосується частоти, а не тривалості кожної зупинки.'],
            'pl' => ['question-suffix', ', a nie jak długo trwało każde zgaśnięcie', 'Pytanie dotyczy częstotliwości, nie czasu trwania każdego zgaśnięcia.'],
        ],
        'pastpc-time-expressions-v3-c1-12' => [
            'uk' => ['prefix', 'Аудит запитує про частоту перемикань, а не загальну тривалість: ', 'Аудит запитує про частоту перемикань, а не загальну тривалість.'],
            'pl' => ['prefix', 'Audyt pyta o częstotliwość przełączeń, nie łączny czas trwania: ', 'Audyt pyta o częstotliwość przełączeń, nie łączny czas trwania.'],
        ],
        'pastpc-time-expressions-v3-c2-12' => [
            'uk' => ['prefix', 'Питання стосується повторюваності, а не тривалості: ', 'Питання стосується повторюваності, а не тривалості.'],
            'pl' => ['prefix', 'Chodzi o powtarzalność, nie czas trwania: ', 'Chodzi o powtarzalność, nie czas trwania.'],
        ],
    ];
}

function ppcQualityMixedPresentationSourcePaths(): array
{
    $paths = ['scripts/lib/ppc_quality_mixed_display.php'];
    foreach (['', '_negatives', '_questions', '_time'] as $suffix) {
        $paths[] = 'scripts/lib/ppc_quality_mixed_sources'.$suffix.'.php';
    }
    foreach (['Forms', 'Negatives', 'Questions', 'TimeExpressions'] as $family) {
        $paths[] = 'database/seeders/V3/Tenses/PastPerfectContinuous/PastPerfectContinuous'.$family.'AllLevelsV3Seeder/definition.json';
    }
    return $paths;
}

function ppcQualityMixedPresentationRows(string $root, array $inventory): array
{
    $sources = require $root.'/scripts/lib/ppc_quality_mixed_sources.php';
    $moves = ppcQualityMixedPresentationMoves();
    $directions = [
        'uk' => 'Відтвори речення англійською, зберігаючи наведений порядок частин, підмети та позиції часових обставин. Заповни пропуски за навчальною підказкою: ',
        'pl' => 'Odtwórz zdanie po angielsku, zachowując podaną kolejność części, podmioty i pozycje określeń czasu. Uzupełnij luki według wskazówki: ',
        'en' => 'Reconstruct the sentence in the displayed clause order. Keep the stated subjects and time-phrase positions; fill each blank using its learning hint.',
    ];
    $enSourcePrefix = 'Reconstruct the sentence in the displayed clause order. Keep the stated subjects and time-phrase positions; fill each blank using its learning hint: ';
    $rows = []; $seen = []; $definitions = [];
    foreach ($inventory['questions'] as $record) {
        if ($record['bank_kind'] !== 'Mixed') { continue; }
        $uuid = $record['editorial_uuid'];
        if (isset($seen[$uuid]) || $record['type'] !== 0 || preg_match('/-(a[12]|b[12]|c[12])-(\d{2})$/D', $uuid, $slot) !== 1) {
            throw new RuntimeException('Unexpected finite Mixed presentation identity: '.$uuid);
        }
        $seen[$uuid] = true;
        $path = $record['definition_path'];
        $definitions[$path] ??= json_decode(file_get_contents($root.'/'.$path), true, flags: JSON_THROW_ON_ERROR);
        $matches = array_values(array_filter($definitions[$path]['questions'], static fn (array $q): bool => $q['uuid'] === $uuid));
        if (count($matches) !== 1 || $matches[0]['question'] !== $record['question_template']) {
            throw new RuntimeException('Canonical Mixed stem differs: '.$uuid);
        }
        $question = $matches[0];
        $source = $sources[$record['bank_family']][$record['level']][(int) $slot[2] - 1] ?? null;
        $stem = preg_replace('/\{a\d+\}/', '____', $question['question']);
        if (!is_array($source) || $stem === $question['question'] || str_contains($stem, '{')) {
            throw new RuntimeException('Missing authored Mixed source or blank stem: '.$uuid);
        }
        $locales = [];
        foreach (['uk', 'en', 'pl'] as $locale) {
            $expected = $question['localizations'][$locale]['source_text'];
            $raw = $locale === 'en' ? $stem : $source[$locale === 'uk' ? 0 : 1];
            if ($expected !== $record['source_condition'][$locale]
                || $expected !== ($locale === 'en' ? $enSourcePrefix.$stem : $raw)) {
                throw new RuntimeException('Expected canonical Mixed locale source differs: '.$uuid.'/'.$locale);
            }
            $display = $raw;
            $instructions = $directions[$locale].($locale === 'en' ? '' : $stem);
            if (isset($moves[$uuid][$locale])) {
                [$operation, $fragment, $explanation] = $moves[$uuid][$locale];
                if ($operation === 'prefix') {
                    if (!str_starts_with($raw, $fragment)) { throw new RuntimeException('Authored display prefix differs: '.$uuid.'/'.$locale); }
                    $display = substr($raw, strlen($fragment));
                    $display = mb_strtoupper(mb_substr($display, 0, 1)).mb_substr($display, 1);
                } else {
                    $punctuation = $operation === 'question-suffix' ? '?' : '.';
                    if (!str_ends_with($raw, $fragment.$punctuation)) { throw new RuntimeException('Authored display suffix differs: '.$uuid.'/'.$locale); }
                    $display = substr($raw, 0, -strlen($fragment.$punctuation)).$punctuation;
                }
                $instructions .= "\n".$explanation;
            }
            $locales[$locale] = ['expected_source' => $expected, 'display_source' => $display, 'instructions' => $instructions];
        }
        $rows[] = ['seeder_class' => $record['seeder_class'], 'editorial_uuid' => $uuid,
            'persistent_uuid' => $record['persistent_uuid'], 'locales' => $locales];
    }
    if (count($rows) !== 288 || array_diff(array_keys($moves), array_keys($seen)) !== []) {
        throw new RuntimeException('Expected exact 288 Mixed presentation rows and 14 authored moves');
    }
    return $rows;
}
