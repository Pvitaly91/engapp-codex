<?php

declare(strict_types=1);

require __DIR__.'/../../vendor/autoload.php';

$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Page;
use App\Services\GrammarTestFilterService;
use App\Services\TheoryPagePromptLinkedTestsService;
use Illuminate\Support\Facades\DB;

/**
 * Generate an auditable, local inventory of canonical answers for which the
 * shared answer matcher accepts a contracted/full equivalent. It does not
 * alter questions, tests, snapshots, or the database.
 */
$contractionPattern = "(^|[^A-Za-z])([A-Za-z]+(n't|'m|'re|'ve|'ll|'s|'d|'d've|n't've)|am[[:space:]]+not|are[[:space:]]+not|is[[:space:]]+not|was[[:space:]]+not|were[[:space:]]+not|have[[:space:]]+not|has[[:space:]]+not|had[[:space:]]+not|will[[:space:]]+not|would[[:space:]]+not|shall[[:space:]]+not|should[[:space:]]+not|could[[:space:]]+not|can[[:space:]]+not|do[[:space:]]+not|does[[:space:]]+not|did[[:space:]]+not|might[[:space:]]+not|must[[:space:]]+not|may[[:space:]]+not|need[[:space:]]+not|dare[[:space:]]+not|ought[[:space:]]+not|let[[:space:]]+us)([^A-Za-z]|$)";

$rows = DB::table('questions as q')
    ->join('question_answers as a', 'a.question_id', '=', 'q.id')
    ->leftJoin('question_options as o', 'a.option_id', '=', 'o.id')
    ->leftJoin('categories as c', 'q.category_id', '=', 'c.id')
    ->select(['q.id', 'q.question', 'q.seeder', 'a.marker', 'o.option as answer', 'c.name as category'])
    ->whereRaw('o.option REGEXP ?', [$contractionPattern])
    ->orderBy('q.id')
    ->get();

$itemsById = $rows->groupBy('id')->map(function ($answers): array {
    $first = $answers->first();

    return [
        'id' => (int) $first->id,
        'question' => (string) $first->question,
        'answers' => $answers->sortBy(static fn ($answer) => (int) substr($answer->marker, 1))
            ->map(static fn ($answer) => [(string) $answer->marker, (string) $answer->answer])->values()->all(),
        'category' => (string) ($first->category ?? ''),
        'seeder' => (string) ($first->seeder ?? ''),
    ];
});

$filterService = app(GrammarTestFilterService::class);
$theoryPageTests = app(TheoryPagePromptLinkedTestsService::class);
$testGroups = Page::query()->with('category')->orderBy('id')->get()
    ->flatMap(function (Page $page) use ($filterService, $theoryPageTests, $itemsById) {
        return $theoryPageTests->buildForPage($page)
            ->filter(static fn ($test): bool => (bool) data_get($test->filters ?? [], '__meta.theory_page_mixed_all_levels_test'))
            ->map(function ($test) use ($page, $filterService, $itemsById): ?array {
                $filters = $test->filters ?? [];
                $candidateIds = $filterService->matchingQuestionsQuery($filters)->pluck('id');
                $questions = $candidateIds->map(fn ($id) => $itemsById->get((int) $id))->filter()->values();
                if ($questions->isEmpty()) {
                    return null;
                }
                $path = trim((string) data_get($filters, 'prompt_generator.theory_page.category_slug_path', ''));
                $theoryUrl = 'http://gramlyze.loc/theory/'.trim($path.'/'.$page->slug, '/');

                return [
                    'slug' => trim((string) ($test->public_slug ?? $test->slug), '/'),
                    'name' => (string) $test->name,
                    'theory_url' => $theoryUrl,
                    'questions' => $questions,
                ];
            })
            ->filter();
    })
    ->sortBy('slug')
    ->values();

$escape = static fn (string $value): string => str_replace(['|', "\r", "\n"], ['\\|', '', '<br>'], $value);
$lines = [
    '# Питання з підтримкою скорочених і повних форм',
    '',
    'Згенеровано локально: '.now()->toIso8601String().'. Джерело: канонічні записи `questions` + `question_answers` + `question_options` локальної БД.',
    '',
    'У списку **'.$testGroups->count().' mixed-тестів**. Під кожним URL наведено фактичні питання з його повного пулу, для яких механізм тепер приймає еквівалентну повну або скорочену форму. Питання не переписувалися: змінилася лише перевірка введення.',
    '',
    'Mixed-тест показує обмежену добірку за рівнями, тому будь-яке питання з відповідного списку **може з’явитися** після нового запуску тесту. Списки не приховують питання через поточний випадковий порядок.',
];

foreach ($testGroups as $test) {
    $lines[] = '';
    $lines[] = '## ['.$escape($test['name']).'](http://gramlyze.loc/test/'.$test['slug'].')';
    $lines[] = '';
    $lines[] = 'Теорія: ['.$escape($test['theory_url']).']('.$test['theory_url'].')<br>';
    $lines[] = 'Питань у пулі з підтримкою альтернативної форми: **'.$test['questions']->count().'**.';
    $lines[] = '';
    $lines[] = '| ID | Категорія | Питання | Маркер → канонічна відповідь |';
    $lines[] = '| ---: | --- | --- | --- |';
    foreach ($test['questions'] as $item) {
        $answers = implode('<br>', array_map(static fn (array $answer): string => '`'.$answer[0].'` → `'.$answer[1].'`', $item['answers']));
        $lines[] = '| '.$item['id'].' | '.$escape($item['category'] ?: 'Без категорії').' | '
            .$escape($item['question']).' | '.$escape($answers).' |';
    }
}

$reportPath = dirname(__DIR__, 2).'/docs/reports/english-contractions-questions.md';
file_put_contents($reportPath, implode("\n", $lines)."\n");

fwrite(STDOUT, "Generated {$testGroups->count()} test URL groups: {$reportPath}".PHP_EOL);
