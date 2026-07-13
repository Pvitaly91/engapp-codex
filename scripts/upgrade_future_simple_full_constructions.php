<?php

declare(strict_types=1);

/**
 * Upgrade the standalone Future Simple V3 tests from auxiliary-only gaps to
 * complete Future Simple constructions. The script is intentionally scoped to
 * the four canonical FutureSimple definitions and is safe to run repeatedly.
 */

$root = dirname(__DIR__);

$packages = [
    'forms' => $root.'/database/seeders/V3/FutureForms/FutureSimple/FutureSimpleFormsAllLevelsV3Seeder',
    'negatives' => $root.'/database/seeders/V3/FutureForms/FutureSimple/FutureSimpleNegativesAllLevelsV3Seeder',
    'questions' => $root.'/database/seeders/V3/FutureForms/FutureSimple/FutureSimpleQuestionsAllLevelsV3Seeder',
    'time' => $root.'/database/seeders/V3/FutureForms/FutureSimple/FutureSimpleTimeExpressionsAllLevelsV3Seeder',
];

$translationOverrides = [
    'uk' => [
        'address' => 'розглянути', 'allocate' => 'виділити', 'answer' => 'відповісти',
        'articulate' => 'сформулювати', 'assess' => 'оцінити', 'call' => 'зателефонувати',
        'challenge' => 'поставити під сумнів', 'clean' => 'прибрати', 'compare' => 'порівняти',
        'contact' => 'зв’язатися', 'contest' => 'оскаржити', 'cook' => 'приготувати',
        'coordinate' => 'координувати', 'correct' => 'виправити', 'elucidate' => 'роз’яснити',
        'ensure' => 'забезпечити', 'facilitate' => 'сприяти', 'finance' => 'фінансувати',
        'guarantee' => 'гарантувати', 'influence' => 'вплинути', 'judge' => 'оцінити',
        'like' => 'подобатися', 'listen' => 'слухати', 'mediate' => 'бути посередником',
        'monitor' => 'відстежувати', 'need' => 'потребувати', 'open' => 'відкрити',
        'orchestrate' => 'організувати', 'oversee' => 'контролювати', 'qualify' => 'відповідати вимогам',
        'rain' => 'йти (про дощ)', 'recommend' => 'рекомендувати', 'recontextualize' => 'переосмислити в новому контексті',
        'redress' => 'виправити', 'remain' => 'залишатися', 'remember' => 'пам’ятати',
        'repair' => 'відремонтувати', 'rephrase' => 'перефразувати', 'resolve' => 'вирішити',
        'return' => 'повернутися', 'review' => 'переглянути', 'safeguard' => 'захистити',
        'study' => 'вивчати', 'support' => 'підтримати', 'travel' => 'подорожувати',
        'trigger' => 'спричинити', 'underwrite' => 'фінансувати', 'update' => 'оновити',
        'wait' => 'чекати', 'work' => 'працювати', 'yield' => 'дати результат',
    ],
];

/** @return array<string, string> */
function loadWordTranslations(string $path): array
{
    $payload = readJson($path);
    $translations = [];
    foreach ($payload['with_translation'] ?? [] as $row) {
        $word = strtolower(trim((string) ($row['word'] ?? '')));
        $translation = trim((string) ($row['translation'] ?? ''));
        if ($word !== '' && $translation !== '' && ! isset($translations[$word])) {
            $translations[$word] = $translation;
        }
    }

    return $translations;
}

$localizedHints = [
    'forms' => [
        'uk' => 'Оберіть повну конструкцію Future Simple: will (або підмет + ’ll) + початкова форма дієслова без to.',
        'en' => 'Choose the complete Future Simple construction: will (or subject + ’ll) + the base verb without to.',
        'pl' => 'Wybierz pełną konstrukcję Future Simple: will (lub podmiot + ’ll) + czasownik w formie podstawowej bez to.',
    ],
    'negatives' => [
        'uk' => 'Доберіть форму, що логічно завершує речення.',
        'en' => 'Choose the form that logically completes the sentence.',
        'pl' => 'Wybierz formę, która logicznie uzupełnia zdanie.',
    ],
    'question_order' => [
        'uk' => 'Оберіть повне загальне питання: Will + підмет + початкова форма дієслова?',
        'en' => 'Choose the complete yes/no question: Will + subject + base verb?',
        'pl' => 'Wybierz pełne pytanie ogólne: Will + podmiot + czasownik w formie podstawowej?',
    ],
    'wh_questions' => [
        'uk' => 'Після питального слова використайте повний порядок: will + підмет + початкова форма дієслова.',
        'en' => 'After the question word, use the complete order: will + subject + base verb.',
        'pl' => 'Po słowie pytającym użyj pełnego szyku: will + podmiot + czasownik w formie podstawowej.',
    ],
    'short_answers' => [
        'uk' => 'Оберіть повну коротку відповідь: Yes, підмет + will або No, підмет + won’t.',
        'en' => 'Choose the complete short answer: Yes, subject + will or No, subject + won’t.',
        'pl' => 'Wybierz pełną krótką odpowiedź: Yes, podmiot + will lub No, podmiot + won’t.',
    ],
    'time' => [
        'uk' => 'Часовий маркер указує на майбутнє; оберіть повну конструкцію will + початкова форма дієслова.',
        'en' => 'The time marker points to the future; choose the complete will + base verb construction.',
        'pl' => 'Określenie czasu wskazuje na przyszłość; wybierz pełną konstrukcję will + czasownik w formie podstawowej.',
    ],
];

/** @return array<string, mixed> */
function readJson(string $path): array
{
    return json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
}

/** @param array<string, mixed> $data */
function writeJson(string $path, array $data): void
{
    file_put_contents(
        $path,
        json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR).PHP_EOL
    );
}

function replaceQuestionAndVariants(array &$question, string $newQuestion): void
{
    $oldQuestion = (string) $question['question'];
    $question['question'] = $newQuestion;
    $question['variants'] = array_map(
        static fn (mixed $variant): mixed => $variant === $oldQuestion ? $newQuestion : $variant,
        $question['variants'] ?? []
    );
}

/** @return array{0: string, 1: string}|null */
function consumeVerbAfterMarker(string $question): ?array
{
    if (preg_match('/\{a1\}\s+([A-Za-z]+(?:-[A-Za-z]+)?)/u', $question, $matches) !== 1) {
        return null;
    }

    $verb = $matches[1];
    $updated = preg_replace('/\{a1\}\s+'.preg_quote($verb, '/').'\b/u', '{a1}', $question, 1);

    return is_string($updated) ? [$verb, $updated] : null;
}

function baseSubject(string $question, string $answer): string
{
    if (preg_match("/^([A-Za-z]+)'ll\s+/i", $answer, $matches) === 1) {
        return $matches[1];
    }

    $beforeMarker = strstr($question, '{a1}', true);
    if (! is_string($beforeMarker) || preg_match('/([A-Za-z]+)\s*$/', $beforeMarker, $matches) !== 1) {
        return 'it';
    }

    return $matches[1];
}

function isThirdPersonSingular(string $subject): bool
{
    $subject = strtolower($subject);

    if (in_array($subject, ['i', 'you', 'we', 'they', 'researchers', 'people', 'children'], true)) {
        return false;
    }

    return ! str_ends_with($subject, 's');
}

function beForm(string $subject): string
{
    return match (strtolower($subject)) {
        'i' => 'am',
        'you', 'we', 'they', 'researchers', 'people', 'children' => 'are',
        default => isThirdPersonSingular($subject) ? 'is' : 'are',
    };
}

function contractedBeForm(string $subject): string
{
    return match (strtolower($subject)) {
        'i' => "I'm",
        'you' => "You're",
        'he' => "He's",
        'she' => "She's",
        'it' => "It's",
        'we' => "We're",
        'they' => "They're",
        default => $subject.' '.beForm($subject),
    };
}

function thirdPersonForm(string $verb): string
{
    if ($verb === 'be') {
        return 'is';
    }
    if ($verb === 'have') {
        return 'has';
    }
    if (preg_match('/[^aeiou]y$/i', $verb) === 1) {
        return substr($verb, 0, -1).'ies';
    }
    if (preg_match('/(s|sh|ch|x|z|o)$/i', $verb) === 1) {
        return $verb.'es';
    }

    return $verb.'s';
}

function ingForm(string $verb): string
{
    $irregular = ['be' => 'being', 'lie' => 'lying', 'die' => 'dying', 'tie' => 'tying', 'see' => 'seeing'];
    if (isset($irregular[$verb])) {
        return $irregular[$verb];
    }
    if (str_ends_with($verb, 'ie')) {
        return substr($verb, 0, -2).'ying';
    }
    if (str_ends_with($verb, 'e') && ! str_ends_with($verb, 'ee')) {
        return substr($verb, 0, -1).'ing';
    }
    if (strlen($verb) <= 4 && preg_match('/[aeiou][^aeiouwxy]$/i', $verb) === 1) {
        return $verb.substr($verb, -1).'ing';
    }

    return $verb.'ing';
}

function pastForm(string $verb, string $subject): string
{
    $irregular = [
        'be' => strtolower($subject) === 'you' || ! isThirdPersonSingular($subject) ? 'were' : 'was',
        'become' => 'became',
        'begin' => 'began',
        'build' => 'built',
        'buy' => 'bought',
        'choose' => 'chose',
        'come' => 'came',
        'do' => 'did',
        'find' => 'found',
        'get' => 'got',
        'give' => 'gave',
        'go' => 'went',
        'have' => 'had',
        'lead' => 'led',
        'make' => 'made',
        'meet' => 'met',
        'read' => 'read',
        'see' => 'saw',
        'send' => 'sent',
        'speak' => 'spoke',
        'take' => 'took',
        'tell' => 'told',
        'think' => 'thought',
        'win' => 'won',
        'write' => 'wrote',
    ];
    if (isset($irregular[$verb])) {
        return $irregular[$verb];
    }
    if (str_ends_with($verb, 'e')) {
        return $verb.'d';
    }
    if (preg_match('/[^aeiou]y$/i', $verb) === 1) {
        return substr($verb, 0, -1).'ied';
    }
    if (strlen($verb) <= 4 && preg_match('/[aeiou][^aeiouwxy]$/i', $verb) === 1) {
        return $verb.substr($verb, -1).'ed';
    }

    return $verb.'ed';
}

/** @return list<string> */
function affirmativeOptions(string $question, string $correct, string $verb): array
{
    $subject = baseSubject($question, $correct);

    if ($verb === 'be') {
        return [$correct, beForm($subject), pastForm($verb, $subject), isThirdPersonSingular($subject) ? 'has been' : 'have been'];
    }

    $simple = isThirdPersonSingular($subject) ? thirdPersonForm($verb) : $verb;
    $continuous = beForm($subject).' '.ingForm($verb);

    if (preg_match("/^([A-Za-z]+)'ll\s+/i", $correct, $matches) === 1) {
        $explicitSubject = $matches[1];
        $simple = $explicitSubject.' '.(isThirdPersonSingular($explicitSubject) ? thirdPersonForm($verb) : $verb);
        $continuous = contractedBeForm($explicitSubject).' '.ingForm($verb);

        return [$correct, $continuous, $explicitSubject.' '.pastForm($verb, $explicitSubject), $simple];
    }

    return [
        $correct,
        $continuous,
        pastForm($verb, $subject),
        $simple,
    ];
}

/** @return list<string> */
function negativeOptions(string $question, string $correct, string $verb): array
{
    $subject = baseSubject($question, $correct);
    if ($verb === 'be') {
        $present = beForm($subject) === 'are' ? "aren't" : (beForm($subject) === 'am' ? "am not" : "isn't");
        $past = pastForm('be', $subject) === 'were' ? "weren't" : "wasn't";

        return [$correct, $present, $past, isThirdPersonSingular($subject) ? "hasn't been" : "haven't been"];
    }

    $present = isThirdPersonSingular($subject) ? "doesn't {$verb}" : "don't {$verb}";
    $continuous = match (beForm($subject)) {
        'am' => 'am not '.ingForm($verb),
        'are' => "aren't ".ingForm($verb),
        default => "isn't ".ingForm($verb),
    };

    return [$correct, $present, $continuous, "didn't {$verb}"];
}

/** @return array{type: string, hint: string}|null */
function upgradeQuestion(string $package, array &$question): ?array
{
    if (! isset($question['markers']['a1'])) {
        return null;
    }

    $marker =& $question['markers']['a1'];
    $answer = trim((string) ($marker['answer'] ?? ''));

    $predictionRepairs = [
        'fs-forms-v3-c1-01' => ['The committee {a1} the proposal', 'will most likely reject', 'reject'],
        'fs-forms-v3-c1-09' => ['The market {a1} cautiously', 'will presumably respond', 'respond'],
        'fs-forms-v3-c2-01' => ["Historians {a1} this event's significance", 'will likely reassess', 'reassess'],
    ];
    $predictionRepair = $package === 'forms'
        ? ($predictionRepairs[(string) ($question['uuid'] ?? '')] ?? null)
        : null;
    if ($predictionRepair !== null && in_array($answer, ['will most', 'will presumably', 'will likely'], true)) {
        [$newQuestion, $correct, $verb] = $predictionRepair;
        $marker['answer'] = $correct;
        $marker['options'] = affirmativeOptions($newQuestion, $correct, $verb);
        replaceQuestionAndVariants($question, $newQuestion);

        return ['type' => 'forms', 'hint' => ''];
    }

    if ($package === 'forms' && preg_match('/^will\s+(probably|perhaps|personally|inevitably|eventually|far)$/i', $answer) === 1) {
        $consumed = consumeVerbAfterMarker((string) $question['question']);
        if ($consumed === null) {
            throw new RuntimeException('Cannot consume a base verb after a Future Simple adverb for '.$question['uuid']);
        }

        [$verb, $newQuestion] = $consumed;
        $correct = $answer.' '.$verb;
        $marker['answer'] = $correct;
        $marker['options'] = affirmativeOptions($newQuestion, $correct, strtolower($verb));
        $marker['verb_hint'] = 'Оберіть повну конструкцію Future Simple: will + прислівник + початкова форма дієслова без to.';
        replaceQuestionAndVariants($question, $newQuestion);

        return ['type' => 'forms', 'hint' => $marker['verb_hint']];
    }

    if ($package === 'forms' && (strcasecmp($answer, 'will') === 0 || preg_match("/^(I|you|he|she|it|we|they)'ll$/i", $answer) === 1)) {
        $consumed = consumeVerbAfterMarker((string) $question['question']);
        if ($consumed === null) {
            throw new RuntimeException('Cannot consume a base verb for '.$question['uuid']);
        }

        [$verb, $newQuestion] = $consumed;
        $correct = $answer.' '.$verb;
        $marker['answer'] = $correct;
        $marker['options'] = affirmativeOptions($newQuestion, $correct, $verb);
        $marker['verb_hint'] = 'Оберіть повну конструкцію Future Simple: will (або підмет + ’ll) + початкова форма дієслова без to.';
        replaceQuestionAndVariants($question, $newQuestion);

        return ['type' => 'forms', 'hint' => $marker['verb_hint']];
    }

    if ($package === 'forms' && preg_match('/^[A-Za-z-]+$/', $answer) === 1
        && preg_match('/\bwill(?:\s+(probably|personally|inevitably|eventually|far))?\s+\{a1\}/i', (string) $question['question'], $matches) === 1) {
        $adverb = isset($matches[1]) && $matches[1] !== '' ? ' '.strtolower($matches[1]) : '';
        $correct = 'will'.$adverb.' '.$answer;
        $newQuestion = preg_replace(
            '/\bwill(?:\s+(?:probably|personally|inevitably|eventually|far))?\s+\{a1\}/i',
            '{a1}',
            (string) $question['question'],
            1
        );
        if (! is_string($newQuestion)) {
            throw new RuntimeException('Cannot expand a complete affirmative form for '.$question['uuid']);
        }

        $marker['answer'] = $correct;
        $marker['options'] = affirmativeOptions($newQuestion, $correct, strtolower($answer));
        $marker['verb_hint'] = 'Оберіть повну конструкцію Future Simple: will (можливо з прислівником) + початкова форма дієслова без to.';
        replaceQuestionAndVariants($question, $newQuestion);

        return ['type' => 'forms', 'hint' => $marker['verb_hint']];
    }

    if ($package === 'negatives' && in_array(strtolower($answer), ["won't", 'will not'], true)) {
        $consumed = consumeVerbAfterMarker((string) $question['question']);
        if ($consumed === null) {
            throw new RuntimeException('Cannot consume a negative base verb for '.$question['uuid']);
        }

        [$verb, $newQuestion] = $consumed;
        $correct = $answer.' '.$verb;
        $marker['answer'] = $correct;
        $marker['options'] = negativeOptions($newQuestion, $correct, $verb);
        $marker['verb_hint'] = 'Доберіть форму, що логічно завершує речення.';
        replaceQuestionAndVariants($question, $newQuestion);

        return ['type' => 'negatives', 'hint' => $marker['verb_hint']];
    }

    if ($package === 'negatives' && preg_match('/^[A-Za-z-]+$/', $answer) === 1
        && preg_match('/\b(won\'t|will not)\s+\{a1\}/i', (string) $question['question'], $matches) === 1) {
        $negativeAuxiliary = strtolower($matches[1]) === "won't" ? "won't" : 'will not';
        $correct = $negativeAuxiliary.' '.$answer;
        $newQuestion = preg_replace('/\b(?:won\'t|will not)\s+\{a1\}/i', '{a1}', (string) $question['question'], 1);
        if (! is_string($newQuestion)) {
            throw new RuntimeException('Cannot expand a complete negative form for '.$question['uuid']);
        }

        $marker['answer'] = $correct;
        $marker['options'] = negativeOptions($newQuestion, $correct, strtolower($answer));
        $marker['verb_hint'] = 'Доберіть форму, що логічно завершує речення.';
        replaceQuestionAndVariants($question, $newQuestion);

        return ['type' => 'negatives', 'hint' => $marker['verb_hint']];
    }

    if ($package === 'time' && strcasecmp($answer, 'will') === 0) {
        $consumed = consumeVerbAfterMarker((string) $question['question']);
        if ($consumed === null) {
            throw new RuntimeException('Cannot consume a time-expression base verb for '.$question['uuid']);
        }

        [$verb, $newQuestion] = $consumed;
        $correct = 'will '.$verb;
        $marker['answer'] = $correct;
        $marker['options'] = affirmativeOptions($newQuestion, $correct, $verb);
        $marker['verb_hint'] = 'Часовий маркер указує на майбутнє; оберіть повну конструкцію will + початкова форма дієслова.';
        replaceQuestionAndVariants($question, $newQuestion);

        return ['type' => 'time', 'hint' => $marker['verb_hint']];
    }

    if ($package !== 'questions') {
        return null;
    }

    $gapType = (string) ($marker['gap_tags'][0] ?? '');
    $text = (string) $question['question'];

    if ($gapType === 'question_order' && preg_match('/^\{a1\}\s+(.+)\?$/u', $text, $matches) === 1) {
        $tail = $matches[1];
        $correct = 'Will '.$tail;
        $marker['answer'] = $correct;
        $marker['options'] = [$correct, 'Could '.$tail, 'Did '.$tail, 'Would '.$tail];
        $marker['verb_hint'] = 'Оберіть повне загальне питання: Will + підмет + початкова форма дієслова?';
        replaceQuestionAndVariants($question, '{a1}?');

        return ['type' => 'question_order', 'hint' => $marker['verb_hint']];
    }

    if ($gapType === 'base_verb' && preg_match('/^Will\s+(.+?)\s+\{a1\}(.*?)\?$/u', $text, $matches) === 1) {
        $tail = trim($matches[1].' '.$answer.$matches[2]);
        $correct = 'Will '.$tail;
        $marker['answer'] = $correct;
        $marker['options'] = [$correct, 'Could '.$tail, 'Did '.$tail, 'Would '.$tail];
        $marker['verb_hint'] = 'Оберіть повне загальне питання: Will + підмет + початкова форма дієслова?';
        replaceQuestionAndVariants($question, '{a1}?');

        return ['type' => 'question_order', 'hint' => $marker['verb_hint']];
    }

    if ($gapType === 'wh_questions' && preg_match('/^(When|Where|What|Why|How)\s+\{a1\}\s+(.+)\?$/u', $text, $matches) === 1) {
        [$whole, $questionWord, $tail] = $matches;
        $correct = 'will '.$tail;
        $marker['answer'] = $correct;
        $marker['options'] = [$correct, 'did '.$tail, 'might '.$tail, 'would '.$tail];
        $marker['verb_hint'] = 'Після питального слова використайте повний порядок: will + підмет + початкова форма дієслова.';
        replaceQuestionAndVariants($question, $questionWord.' {a1}?');

        return ['type' => 'wh_questions', 'hint' => $marker['verb_hint']];
    }

    if ($gapType === 'short_answers' && preg_match('/^(.*\?)\s+—\s+Yes,\s+(they|it)\s+\{a1\}\.$/iu', $text, $matches) === 1) {
        $lead = $matches[1];
        $pronoun = strtolower($matches[2]);
        $negative = str_ends_with((string) $question['uuid'], '-12');
        $correct = $negative ? "No, {$pronoun} won't" : "Yes, {$pronoun} will";
        $presentAux = $pronoun === 'it' ? "doesn't" : "don't";
        $beAux = $pronoun === 'it' ? "isn't" : "aren't";
        $marker['answer'] = $correct;
        $marker['options'] = $negative
            ? [$correct, "Yes, {$pronoun} will", "No, {$pronoun} {$presentAux}", "No, {$pronoun} {$beAux}"]
            : [$correct, "No, {$pronoun} won't", "Yes, {$pronoun} ".($pronoun === 'it' ? 'does' : 'do'), "Yes, {$pronoun} ".($pronoun === 'it' ? 'is' : 'are')];
        $marker['verb_hint'] = 'Оберіть повну коротку відповідь: Yes, підмет + will або No, підмет + won’t.';
        replaceQuestionAndVariants($question, $lead.' — {a1}.');

        return ['type' => 'short_answers', 'hint' => $marker['verb_hint']];
    }

    return null;
}

function normalizeFullConstructionOptions(string $package, array &$question): void
{
    if (! isset($question['markers']['a1'])) {
        return;
    }

    $marker =& $question['markers']['a1'];
    $answer = trim((string) ($marker['answer'] ?? ''));

    if (($package === 'forms' || $package === 'time') && preg_match("/^(?:will(?:\s+(?:most likely|probably|perhaps|likely|presumably|personally|inevitably|eventually|far))?|[A-Za-z]+'ll)\s+([A-Za-z-]+)$/i", $answer, $matches) === 1) {
        $marker['options'] = affirmativeOptions((string) $question['question'], $answer, strtolower($matches[1]));
    }

    if ($package === 'negatives' && preg_match("/^(?:won't|will not)\s+([A-Za-z-]+)$/i", $answer, $matches) === 1) {
        $marker['options'] = negativeOptions((string) $question['question'], $answer, strtolower($matches[1]));
    }
}

function extractTargetVerb(string $package, array $question): string
{
    $marker = $question['markers']['a1'];
    $answer = trim((string) $marker['answer']);
    $gapType = (string) ($marker['gap_tags'][0] ?? '');

    if (in_array($package, ['forms', 'negatives'], true) || ($package === 'time' && $gapType === 'future_marker')) {
        if (preg_match('/([A-Za-z-]+)$/', $answer, $matches) === 1) {
            return strtolower($matches[1]);
        }
    }

    $subjects = 'my sister|our teacher|the team|the company|the committee|researchers|I|you|she|he|we|they|it';
    if ($package === 'questions') {
        $source = $gapType === 'short_answers' ? (string) $question['question'] : $answer;
        if (preg_match('/^Will\s+(?:'.$subjects.')\s+([A-Za-z-]+)/i', $source, $matches) === 1
            || preg_match('/^will\s+(?:'.$subjects.')\s+([A-Za-z-]+)/i', $source, $matches) === 1) {
            return strtolower($matches[1]);
        }
    }

    if ($package === 'time' && preg_match('/\bwill\s+([A-Za-z-]+)/i', (string) $question['question'], $matches) === 1) {
        return strtolower($matches[1]);
    }

    throw new RuntimeException('Cannot determine the target verb for '.$question['uuid']);
}

/** @param array<string, array<string, string>> $dictionaries */
function localizedVerbHint(string $package, string $gapType, string $verb, string $locale, array $dictionaries): string
{
    $translation = $locale === 'en' ? $verb : ($dictionaries[$locale][$verb] ?? $verb);
    $label = match ($locale) {
        'uk' => "Дієслово: «{$translation}». ",
        'pl' => "Czasownik: „{$translation}”. ",
        default => "Verb: “{$verb}”. ",
    };

    if ($package === 'negatives') {
        return $label.match ($locale) {
            'uk' => 'Доберіть форму, що логічно завершує речення.',
            'pl' => 'Wybierz formę, która logicznie uzupełnia zdanie.',
            default => 'Choose the form that logically completes the sentence.',
        };
    }
    if ($package === 'questions') {
        return $label.match ($locale) {
            'uk' => $gapType === 'short_answers'
                ? 'У короткій відповіді повторіть will або won’t.'
                : 'Поставте will, підмет і це дієслово у правильному порядку.',
            'pl' => $gapType === 'short_answers'
                ? 'W krótkiej odpowiedzi powtórz will lub won’t.'
                : 'Ustaw will, podmiot i ten czasownik we właściwej kolejności.',
            default => $gapType === 'short_answers'
                ? 'Repeat will or won’t in the short answer.'
                : 'Put will, the subject, and this verb in the correct order.',
        };
    }
    if ($package === 'time' && $gapType !== 'future_marker') {
        return $label.match ($locale) {
            'uk' => 'Оберіть часовий маркер, що відповідає контексту Future Simple.',
            'pl' => 'Wybierz określenie czasu pasujące do kontekstu Future Simple.',
            default => 'Choose the time expression that fits the Future Simple context.',
        };
    }

    return $label.match ($locale) {
        'uk' => 'Оберіть повну конструкцію з will.',
        'pl' => 'Wybierz pełną konstrukcję z will.',
        default => 'Choose the complete construction with will.',
    };
}

function diversifyWhQuestions(string $package, array &$question): void
{
    if ($package !== 'questions' || ! isset($question['markers']['a1'])) {
        return;
    }

    $replacements = [
        'fs-questions-v3-a1-10' => ['Where', 'will the company work'],
        'fs-questions-v3-a2-10' => ['What', 'will the company discuss'],
        'fs-questions-v3-b1-10' => ['What', 'will the company expand'],
        'fs-questions-v3-b2-10' => ['What', 'will the company finance'],
        'fs-questions-v3-c1-10' => ['What', 'will the company diversify'],
        'fs-questions-v3-c2-10' => ['What', 'will the company underwrite'],
    ];
    $replacement = $replacements[(string) ($question['uuid'] ?? '')] ?? null;
    if ($replacement === null) {
        return;
    }

    [$questionWord, $correct] = $replacement;
    $tail = substr($correct, strlen('will '));
    $marker =& $question['markers']['a1'];
    $marker['answer'] = $correct;
    $marker['options'] = [$correct, 'did '.$tail, 'might '.$tail, 'would '.$tail];
    replaceQuestionAndVariants($question, $questionWord.' {a1}?');
}

$dictionaries = [
    'uk' => loadWordTranslations($root.'/public/exports/words/words_uk.json'),
    'pl' => loadWordTranslations($root.'/public/exports/words/words_pl.json'),
];
foreach ($translationOverrides as $locale => $overrides) {
    $dictionaries[$locale] = array_replace($dictionaries[$locale] ?? [], $overrides);
}
$changesByPackage = [];

foreach ($packages as $package => $directory) {
    $definitionPath = $directory.'/definition.json';
    $definition = readJson($definitionPath);
    $changes = [];
    $verbs = [];

    foreach ($definition['questions'] as &$question) {
        $change = upgradeQuestion($package, $question);
        if ($change !== null) {
            $changes[(string) $question['uuid']] = $change['type'];
        }
        normalizeFullConstructionOptions($package, $question);
        diversifyWhQuestions($package, $question);
        $uuid = (string) $question['uuid'];
        $verb = extractTargetVerb($package, $question);
        $verbs[$uuid] = [
            'verb' => $verb,
            'gap_type' => (string) ($question['markers']['a1']['gap_tags'][0] ?? ''),
        ];
        $question['markers']['a1']['verb_hint'] = localizedVerbHint(
            $package,
            $verbs[$uuid]['gap_type'],
            $verb,
            'uk',
            $dictionaries
        );
    }
    unset($question);

    writeJson($definitionPath, $definition);
    $changesByPackage[$package] = $changes;

    foreach (['uk', 'en', 'pl'] as $locale) {
        $localizationPath = $directory.'/localizations/'.$locale.'.json';
        $localization = readJson($localizationPath);

        foreach ($localization['questions'] as &$localizedQuestion) {
            $uuid = (string) ($localizedQuestion['uuid'] ?? '');
            $verbData = $verbs[$uuid] ?? null;
            if ($verbData === null) {
                continue;
            }

            $localizedQuestion['verb_hints']['a1'] = localizedVerbHint(
                $package,
                $verbData['gap_type'],
                $verbData['verb'],
                $locale,
                $dictionaries
            );
        }
        unset($localizedQuestion);

        writeJson($localizationPath, $localization);
    }
}

foreach ($changesByPackage as $package => $changes) {
    echo $package.': '.count($changes).' questions upgraded'.PHP_EOL;
}
