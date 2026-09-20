<?php

declare(strict_types=1);

require __DIR__.'/../../vendor/autoload.php';

$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

/**
 * Generate an auditable, local inventory of canonical answers for which the
 * shared answer matcher accepts a contracted/full equivalent. It does not
 * alter questions, tests, snapshots, or the database.
 */
$contractionPattern = "(^|[^A-Za-z])([A-Za-z]+(n't|'m|'re|'ve|'ll|'s|'d|'d've|n't've)|am[[:space:]]+not|are[[:space:]]+not|is[[:space:]]+not|was[[:space:]]+not|were[[:space:]]+not|have[[:space:]]+not|has[[:space:]]+not|had[[:space:]]+not|will[[:space:]]+not|would[[:space:]]+not|shall[[:space:]]+not|should[[:space:]]+not|could[[:space:]]+not|can[[:space:]]+not|do[[:space:]]+not|does[[:space:]]+not|did[[:space:]]+not|might[[:space:]]+not|must[[:space:]]+not|may[[:space:]]+not|need[[:space:]]+not|dare[[:space:]]+not|ought[[:space:]]+not|let[[:space:]]+us)([^A-Za-z]|$)";

$tests = DB::table('tests')->select('slug', 'questions')->get()->map(static fn ($test) => [
    'slug' => $test->slug,
    'questions' => json_decode($test->questions ?: '[]', true) ?: [],
]);

$rows = DB::table('questions as q')
    ->join('question_answers as a', 'a.question_id', '=', 'q.id')
    ->leftJoin('question_options as o', 'a.option_id', '=', 'o.id')
    ->leftJoin('categories as c', 'q.category_id', '=', 'c.id')
    ->select(['q.id', 'q.question', 'q.seeder', 'a.marker', 'o.option as answer', 'c.name as category'])
    ->whereRaw('o.option REGEXP ?', [$contractionPattern])
    ->orderBy('q.id')
    ->get();

$items = $rows->groupBy('id')->map(function ($answers) use ($tests): array {
    $first = $answers->first();

    return [
        'id' => (int) $first->id,
        'question' => (string) $first->question,
        'answers' => $answers->sortBy(static fn ($answer) => (int) substr($answer->marker, 1))
            ->map(static fn ($answer) => [(string) $answer->marker, (string) $answer->answer])->values()->all(),
        'category' => (string) ($first->category ?? ''),
        'seeder' => (string) ($first->seeder ?? ''),
        'tests' => $tests->filter(static fn (array $test) => in_array((int) $first->id, $test['questions'], true))
            ->pluck('slug')->values()->all(),
    ];
})->values();

$escape = static fn (string $value): string => str_replace(['|', "\r", "\n"], ['\\|', '', '<br>'], $value);
$lines = [
    '# Питання з підтримкою скорочених і повних форм',
    '',
    'Згенеровано локально: '.now()->toIso8601String().'. Джерело: канонічні записи `questions` + `question_answers` + `question_options` локальної БД.',
    '',
    'У списку **'.$items->count().' питань**. Для кожного перелічено саме текст питання та канонічну відповідь, для якої механізм тепер приймає еквівалентну повну або скорочену форму. Питання не переписувалися: змінилася лише перевірка введення.',
    '',
    'Якщо колонка «Статичний тест» порожня, питання потрапляє до тесту через його фільтри/змішану добірку; точний набір такого тесту залежить від режиму, рівня та випадкового порядку. Інакше вказано прямий локальний URL.',
    '',
    '## Розподіл за темою',
    '',
    '| Тема (категорія) | Питань |',
    '| --- | ---: |',
];

foreach ($items->groupBy('category')->sortKeys() as $category => $questions) {
    $lines[] = '| '.$escape((string) ($category ?: 'Без категорії')).' | '.$questions->count().' |';
}

$lines[] = '';
$lines[] = '## Повний перелік';
$lines[] = '';
$lines[] = '| ID | Категорія | Питання | Маркер → канонічна відповідь | Статичний тест |';
$lines[] = '| ---: | --- | --- | --- | --- |';

foreach ($items as $item) {
    $answers = implode('<br>', array_map(static fn (array $answer): string => '`'.$answer[0].'` → `'.$answer[1].'`', $item['answers']));
    $urls = $item['tests'] === []
        ? '—'
        : implode('<br>', array_map(static fn (string $slug): string => '[`/test/'.$slug.'`](http://gramlyze.loc/test/'.$slug.')', $item['tests']));
    $lines[] = '| '.$item['id'].' | '.$escape($item['category'] ?: 'Без категорії').' | '
        .$escape($item['question']).' | '.$escape($answers).' | '.$urls.' |';
}

$reportPath = dirname(__DIR__, 2).'/docs/reports/english-contractions-questions.md';
file_put_contents($reportPath, implode("\n", $lines)."\n");

fwrite(STDOUT, "Generated {$items->count()} questions: {$reportPath}".PHP_EOL);
