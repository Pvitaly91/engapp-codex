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
$rules = \App\Support\AcceptedAnswerVariants::rules();
$negativePairs = $rules['negative'];
$positivePairs = $rules['positive'];
uksort($negativePairs, static fn (string $left, string $right): int => strlen($right) <=> strlen($left));
uksort($positivePairs, static fn (string $left, string $right): int => strlen($right) <=> strlen($left));
// Apply negative pairs first: "they will not" must be presented as
// "they won't", never the less natural "they'll not".
$pairs = $negativePairs + $positivePairs;
$replaceWhole = static function (string $text, string $from, string $to): string {
    return preg_replace("~(?<![a-z'])".preg_quote($from, '~')."(?![a-z'])~i", $to, $text) ?? $text;
};
$subjectFromTail = static function (string $tail) use ($rules): ?array {
    $boundary = '(?=\\s+(?:'.$rules['question_predicates'].')\\b|\\s*$)';
    foreach ([
        "/^\\s+((?:the|a|an|this|that|these|those|my|your|his|her|our|their) (?:[a-z'-]+ ){0,3}?[a-z'-]+)".$boundary.'/i',
        '/^\\s+(I|you|he|she|it|we|they|there|this|that|these|those)\\b/i',
        '/^\\s+([A-Z][a-z]+(?: [A-Z][a-z]+){0,2})'.$boundary.'/',
    ] as $pattern) {
        if (preg_match($pattern, $tail, $match)) {
            return $match;
        }
    }

    return null;
};
$formsFor = static function (string $answer, string $question, string $marker) use ($pairs, $rules, $replaceWhole, $subjectFromTail): ?array {
    $answer = \App\Support\AcceptedAnswerVariants::normalizeTypography($answer);
    $placeholder = '{'.$marker.'}';
    $position = strpos($question, $placeholder);
    $tail = $position === false ? '' : substr($question, $position + strlen($placeholder));
    $subject = null;

    // The UI joins a subject that follows a negative-question blank, so show
    // the learner the exact forms it accepts: Don't you / Do you not.
    foreach (array_keys($rules['negative']) as $short) {
        if (preg_match("~(?<![a-z'])".preg_quote($short, '~')."(?![a-z'])~i", $answer)
            && ($subject = $subjectFromTail($tail))) {
            $answer .= ' '.$subject[1];
            $tail = substr($tail, strlen($subject[0]));
            break;
        }
    }

    $short = $answer;
    $full = $answer;
    $matched = false;
    foreach ($pairs as $contracted => $expanded) {
        if (preg_match("~(?<![a-z'])".preg_quote($contracted, '~')."(?![a-z'])~i", $answer)) {
            $full = $replaceWhole($full, $contracted, $expanded);
            $matched = true;
        }
        if (preg_match("~(?<![a-z'])".preg_quote($expanded, '~')."(?![a-z'])~i", $answer)) {
            $short = $replaceWhole($short, $expanded, $contracted);
            $matched = true;
        }
    }
    if (preg_match('/\\bcan not\\b/i', $answer)) {
        $short = preg_replace('/\\bcan not\\b/i', "can't", $short) ?? $short;
        $full = preg_replace('/\\bcan not\\b/i', 'cannot', $full) ?? $full;
        $matched = true;
    }
    $short = preg_replace("/\\b(I|you|he|she|it|we|they|there|that|who|what)'ll not\\b/i", "\$1 won't", $short) ?? $short;

    // Resolve author-written 's and 'd only when the next word makes the
    // meaning clear; this mirrors the production matcher rather than guessing.
    $next = strtolower((string) preg_replace('/^.*?([a-z]+).*$/i', '$1', ltrim($tail)));
    $participle = in_array($next, $rules['participles'], true) || preg_match('/(?:ed|en)$/', $next);
    foreach (['s' => $rules['s_subjects'], 'd' => $rules['d_subjects']] as $suffix => $subjects) {
        $pattern = '/\\b('.implode('|', $subjects).")'".$suffix.'\\b/i';
        if (preg_match($pattern, $answer)) {
            $auxiliary = $suffix === 's'
                ? (in_array($next, ['been', 'got', 'gotten'], true) ? 'has' : ($participle ? null : 'is'))
                : ($next === 'better' || $participle ? 'had' : 'would');
            if ($auxiliary !== null) {
                $full = preg_replace_callback($pattern, static fn (array $match): string => $match[1].' '.$auxiliary, $full) ?? $full;
                $matched = true;
            }
        }
    }

    if ($subject !== null) {
        $full = preg_replace('/\\b(can|could|do|does|did|is|are|was|were|have|has|had|will|would|shall|should|must|might|need|dare|ought) not '.preg_quote($subject[1], '/').'\\b/i', '$1 '.$subject[1].' not', $full) ?? $full;
        $full = preg_replace('/\\bare I not\\b/i', 'am I not', $full) ?? $full;
    }

    // An author can place a complete negative question in one blank, for
    // example "won't you?". Its readable full equivalent is "will you not?",
    // not the mechanically expanded but ungrammatical "will not you?".
    $full = preg_replace(
        '/\\b(can|could|do|does|did|is|are|was|were|have|has|had|will|would|shall|should|must|might|need|dare|ought) not (I|you|he|she|it|we|they|there|this|that|these|those)\\b/i',
        '$1 $2 not',
        $full,
    ) ?? $full;
    $full = preg_replace('/\\bare I not\\b/i', 'am I not', $full) ?? $full;

    if (preg_match('/^[A-Z]/', $short)) {
        $full = ucfirst($full);
    }

    return $matched ? ['short' => $short, 'full' => $full] : null;
};
$lines = [
    '# Питання з підтримкою скорочених і повних форм',
    '',
    'Згенеровано локально: '.now()->toIso8601String().'. Джерело: канонічні записи `questions` + `question_answers` + `question_options` локальної БД.',
    '',
    'У списку **'.$testGroups->count().' mixed-тестів**. Під кожним URL наведено назву/текст питання та дві еквівалентні форми відповіді, які приймає поле вводу: скорочену й повну. Питання не переписувалися: змінилася лише перевірка введення.',
    '',
    'Mixed-тест показує обмежену добірку за рівнями, тому будь-яке питання з відповідного списку **може з’явитися** після нового запуску тесту. Списки не приховують питання через поточний випадковий порядок.',
];

foreach ($testGroups as $test) {
    $lines[] = '';
    $lines[] = '## ['.$escape($test['name']).'](http://gramlyze.loc/test/'.$test['slug'].')';
    $lines[] = '';
    $lines[] = 'Теорія: ['.$escape($test['theory_url']).']('.$test['theory_url'].')<br>';
    $questions = $test['questions']->flatMap(static function (array $item) use ($formsFor): array {
        return collect($item['answers'])->map(function (array $answer) use ($item, $formsFor): ?array {
            [$marker, $value] = $answer;
            $forms = $formsFor($value, $item['question'], $marker);
            if ($forms === null) {
                return null;
            }

            return [
                'question' => str_replace('{'.$marker.'}', '____', $item['question']),
                'short' => $forms['short'],
                'full' => $forms['full'],
            ];
        })->filter()->all();
    })->values();
    $lines[] = 'Питань у пулі з підтримкою альтернативної форми: **'.$questions->count().'**.';
    $lines[] = '';
    $lines[] = '| Питання | Скорочена форма | Повна форма |';
    $lines[] = '| --- | --- | --- |';
    foreach ($questions as $question) {
        $lines[] = '| '.$escape($question['question']).' | `'.$escape($question['short']).'` | `'.$escape($question['full']).'` |';
    }
}

$reportPath = dirname(__DIR__, 2).'/docs/reports/english-contractions-questions.md';
file_put_contents($reportPath, implode("\n", $lines)."\n");

fwrite(STDOUT, "Generated {$testGroups->count()} test URL groups: {$reportPath}".PHP_EOL);
