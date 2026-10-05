<?php

declare(strict_types=1);

/** Finite editorial catalogue; never used by the request-time matcher. */
function ppcQualityFocus(string $focus): array
{
    $texts = [
        'process' => ['Відновіть тривалий процес до названої минулої події у Past Perfect Continuous.', 'Use Past Perfect Continuous for the ongoing process leading up to the stated past event.', 'Użyj Past Perfect Continuous dla procesu trwającego do wskazanego wydarzenia w przeszłości.'],
        'cause' => ['У Past Perfect Continuous покажіть попередню діяльність як причину стану в минулому.', 'Use Past Perfect Continuous to present earlier activity as the cause of a past condition.', 'Użyj Past Perfect Continuous, aby pokazać wcześniejszą czynność jako przyczynę stanu w przeszłości.'],
        'spelling' => ['Утворіть Past Perfect Continuous; перевірте написання закінчення -ing.', 'Build Past Perfect Continuous and check the spelling of the -ing form.', 'Utwórz Past Perfect Continuous i sprawdź pisownię formy z końcówką -ing.'],
        'state' => ['Це стан, а не діяльність: використайте Past Perfect Simple, без Continuous.', 'This is a state, not an activity: use Past Perfect Simple, not Continuous.', 'To stan, nie czynność: użyj Past Perfect Simple, bez Continuous.'],
        'complete' => ['Умова підсумовує завершений результат, а не тривалість: використайте Past Perfect Simple.', 'The sentence counts a completed result, not duration: use Past Perfect Simple.', 'Zdanie podsumowuje ukończony wynik, nie czas trwania: użyj Past Perfect Simple.'],
        'negative' => ['У Past Perfect Continuous заперечте попередню діяльність; не втратьте not або been.', 'Negate the earlier activity in Past Perfect Continuous; retain not and been.', 'Zaprzecz wcześniejszej czynności w Past Perfect Continuous; zachowaj not i been.'],
        'not_long' => ['Заперечте саме тривалість у Past Perfect Continuous: дія була, але тривала недовго.', 'Negate the duration in Past Perfect Continuous: the activity happened, but not for long.', 'Zaprzecz czasowi trwania w Past Perfect Continuous: czynność miała miejsce, ale trwała krótko.'],
        'not_well' => ['Заперечте якість у Past Perfect Continuous: діяльність була, але не відбувалася добре.', 'Negate the quality in Past Perfect Continuous: the activity occurred, but not well.', 'Zaprzecz jakości w Past Perfect Continuous: czynność miała miejsce, ale nie przebiegała dobrze.'],
        'yesno' => ['Побудуйте загальне запитання у Past Perfect Continuous з інверсією had і підмета.', 'Build a yes/no question in Past Perfect Continuous, placing had before the subject.', 'Utwórz pytanie ogólne w Past Perfect Continuous, stawiając had przed podmiotem.'],
        'duration_q' => ['Запитайте про тривалість у Past Perfect Continuous; почніть із двослівного питального виразу.', 'Ask about duration in Past Perfect Continuous; start with a two-word question phrase.', 'Zapytaj o czas trwania w Past Perfect Continuous; zacznij od dwuwyrazowego wyrażenia pytającego.'],
        'place_q' => ['Запитайте про місце попередньої діяльності у Past Perfect Continuous.', 'Ask about the location of the earlier activity in Past Perfect Continuous.', 'Zapytaj o miejsce wcześniejszej czynności w Past Perfect Continuous.'],
        'reason_q' => ['Запитайте про причину попередньої діяльності у Past Perfect Continuous.', 'Ask about the reason for the earlier activity in Past Perfect Continuous.', 'Zapytaj o przyczynę wcześniejszej czynności w Past Perfect Continuous.'],
        'object_q' => ['Запитайте про предмет або вид попередньої діяльності у Past Perfect Continuous.', 'Ask about the object or kind of earlier activity in Past Perfect Continuous.', 'Zapytaj o przedmiot lub rodzaj wcześniejszej czynności w Past Perfect Continuous.'],
        'subject_q' => ['Запитайте, хто виконував дію: питальне слово є підметом, додаткова інверсія не потрібна.', 'Ask who performed the activity: the question word is the subject, so no extra inversion is needed.', 'Zapytaj, kto wykonywał czynność: wyraz pytający jest podmiotem, więc nie potrzeba dodatkowej inwersji.'],
        'embedded' => ['Відновіть непряме запитання у Past Perfect Continuous: після сполучного слова потрібен розповідний порядок.', 'Complete the embedded question in Past Perfect Continuous: use statement word order after the linking word.', 'Uzupełnij pytanie zależne w Past Perfect Continuous: po wyrazie łączącym użyj szyku zdania oznajmującego.'],
        'negative_q' => ['Побудуйте заперечне запитання у Past Perfect Continuous; not ставиться після підмета у повній формі.', 'Build a negative question in Past Perfect Continuous; in the full form, put not after the subject.', 'Utwórz pytanie przeczące w Past Perfect Continuous; w pełnej formie postaw not po podmiocie.'],
        'short_yes' => ['Факт вимагає ствердної короткої відповіді. Узгодьте займенник із мовцем; використайте had без been.', 'Give an affirmative short answer consistent with the fact. Match the pronoun to the speaker; use had without been.', 'Podaj twierdzącą krótką odpowiedź zgodną z faktem. Dopasuj zaimek do mówiącego; użyj had bez been.'],
        'short_no' => ['Факт вимагає заперечної короткої відповіді. Узгодьте займенник із підметом; заперечте had без been.', 'Give a negative short answer consistent with the fact. Match the pronoun to the subject; negate had without been.', 'Podaj przeczącą krótką odpowiedź zgodną z faktem. Dopasuj zaimek do podmiotu; zaprzecz had bez been.'],
        'duration' => ['Потрібен прийменник перед виміряною тривалістю, а не перед початковою точкою.', 'Use the preposition introducing a measured duration, not a starting point.', 'Użyj przyimka wprowadzającego zmierzony czas trwania, nie punkt początkowy.'],
        'start' => ['Потрібен прийменник, що позначає початок періоду до минулої опорної події.', 'Use the preposition marking the start of the interval leading to the past reference event.', 'Użyj przyimka wskazującego początek okresu trwającego do wydarzenia odniesienia w przeszłości.'],
        'whole' => ['Позначте весь названий період без прийменника тривалості; пропуск — одне слово.', 'Mark the whole named period without a duration preposition; the gap is one word.', 'Wskaż cały podany okres bez przyimka czasu trwania; luka zawiera jedno słowo.'],
        'prior' => ['Зв’яжіть попередню діяльність із подією, що сталася пізніше; пропуск — один сполучник.', 'Link the earlier activity to the event that happened later; the gap is one conjunction.', 'Połącz wcześniejszą czynność z wydarzeniem, które nastąpiło później; luka zawiera jeden spójnik.'],
        'deadline' => ['Потрібне трислівне словосполучення зі значенням «на той момент, коли», а не початок чи кінець дії.', 'Use the three-word phrase meaning at the point when, not the start or end of the activity.', 'Użyj trzywyrazowego wyrażenia oznaczającego moment, w którym, nie początek ani koniec czynności.'],
        'endpoint' => ['Позначте кінець періоду: дія тривала до названого моменту, а тоді припинилася.', 'Mark the endpoint: the activity continued up to the stated moment and then stopped.', 'Wskaż koniec okresu: czynność trwała do podanego momentu, a następnie się zakończyła.'],
        'elapsed' => ['Відновіть двослівне питання про тривалість, не про частоту або момент початку.', 'Supply the two-word question about elapsed duration, not frequency or starting time.', 'Wstaw dwuwyrazowe pytanie o czas trwania, nie o częstotliwość ani moment rozpoczęcia.'],
        'frequency' => ['Запитайте про частоту повторюваної діяльності у попередньому періоді, а не про її загальну тривалість.', 'Ask about the frequency of repeated activity in the earlier interval, not its total duration.', 'Zapytaj o częstotliwość powtarzanej czynności we wcześniejszym okresie, nie o jej łączny czas trwania.'],
        'throughout' => ['Потрібен один прийменник зі значенням «упродовж усього періоду», без зміни тривалості на початок.', 'Use one preposition meaning during the entire period, without changing duration into a starting point.', 'Użyj jednego przyimka oznaczającego przez cały okres, bez zmiany czasu trwania na punkt początkowy.'],
        'relative' => ['Пов’яжіть подію з тривалістю до неї; потрібен відносний прислівник часу після названої події.', 'Relate the event to the duration leading up to it; use a relative time adverb after the named event.', 'Powiąż wydarzenie z poprzedzającym je czasem trwania; użyj względnego przysłówka czasu po nazwanym wydarzeniu.'],
    ];
    if (! isset($texts[$focus])) {
        throw new RuntimeException("Unknown PPC focus: {$focus}");
    }
    return array_combine(['uk', 'en', 'pl'], $texts[$focus]);
}

/** Explicit English learning lemmas remove synonym guesswork without giving a completed answer. */
function ppcQualityHint(array $item): array
{
    $hints = ppcQualityFocus($item['focus']);
    if ($item['lemma'] !== '') {
        $hints['uk'] = 'Навчальна лема: '.$item['lemma'].' («'.$item['meaning'].'»). '.$hints['uk'];
        $hints['en'] = 'Learning lemma: '.$item['lemma'].'. '.$hints['en'];
        $hints['pl'] = 'Czasownik do użycia: '.$item['lemma'].'. '.$hints['pl'];
    }
    if (($item['subject'] ?? '') !== '') {
        $hints['uk'] .= ' Підмет у пропуску: «'.$item['subject'].'».';
        $hints['en'] .= ' Subject inside the gap: '.$item['subject'].'.';
        $hints['pl'] .= ' Podmiot w luce: '.$item['subject'].'.';
    }
    return $hints;
}

function ppcQualityItem(string $question, string $answer, string $lemma, string $meaning, string $focus = 'process', ?array $options = null, string $subject = ''): array
{
    if ($options === null) {
        $full = str_replace("hadn't", 'had not', $answer);
        $options = [$answer];
        foreach ([
            preg_replace('/\bbeen\s+/', '', $full),
            preg_replace('/\bbeen\b/', 'being', $full),
            preg_replace('/\bhad\b/i', 'has', $full),
            preg_replace('/\b([a-z]+ing)\b/i', $lemma, $full),
            preg_replace('/\bhad\b/i', 'having', $full),
        ] as $candidate) {
            if ($candidate !== $answer && ! in_array($candidate, $options, true)) {
                $options[] = $candidate;
            }
            if (count($options) === 5) {
                break;
            }
        }
    }
    return compact('question', 'answer', 'lemma', 'meaning', 'focus', 'options', 'subject');
}

function ppcQualityQuestion(string $question, string $answer, string $lemma, string $meaning, string $focus, string $subject, ?array $options = null): array
{
    return ppcQualityItem($question, $answer, $lemma, $meaning, $focus, $options, $subject);
}

function ppcQualityShort(string $question, string $answer, array $options): array
{
    return ppcQualityItem($question, $answer, '', '', str_starts_with($answer, 'Yes') ? 'short_yes' : 'short_no', $options);
}

function ppcQualityTimeMulti(string $question, array $answers, array $focuses): array
{
    $item = ppcQualityItem($question, $answers[0], '', '', $focuses[0], []);
    $optionFamilies = [
        'for' => ['for', 'since', 'until', 'at', 'by'],
        'since' => ['since', 'for', 'during', 'until', 'by'],
        'until' => ['until', 'since', 'for', 'during', 'by'],
        'before' => ['before', 'after', 'while', 'since', 'as soon as'],
        'throughout' => ['throughout', 'since', 'until', 'by', 'at'],
        'By the time' => ['By the time', 'Since the time', 'For the time', 'During the time', 'Until the time'],
    ];
    $item['extra_markers'] = [];
    foreach ($answers as $index => $answer) {
        $markerItem = ppcQualityItem('', $answer, '', '', $focuses[$index], $optionFamilies[$answer]);
        if ($index === 0) {
            $item['options'] = $markerItem['options'];
        } else {
            $item['extra_markers']['a'.($index + 1)] = $markerItem;
        }
    }
    return $item;
}

function ppcQualityCatalog(): array
{
    return [
        'Forms' => require __DIR__.'/ppc_quality_mixed_forms.php',
        'Negatives' => require __DIR__.'/ppc_quality_mixed_negatives.php',
        'Questions' => require __DIR__.'/ppc_quality_mixed_questions.php',
        'TimeExpressions' => require __DIR__.'/ppc_quality_mixed_time.php',
    ];
}

function ppcQualityProject(array $definition, array $levels): array
{
    static $sources;
    $sources ??= require __DIR__.'/ppc_quality_mixed_sources.php';
    preg_match('/PastPerfectContinuous(Forms|Negatives|Questions|TimeExpressions)AllLevelsV3Seeder$/', $definition['seeder']['class'], $familyMatch);
    $family = $familyMatch[1] ?? throw new RuntimeException('Unexpected PPC seeder class.');
    if (count($definition['questions']) !== 72) {
        throw new RuntimeException('Expected the existing 72-question mixed bank.');
    }
    foreach ($definition['questions'] as $index => &$question) {
        $item = $levels[$question['level']][$index % 12] ?? null;
        if (! is_array($item) || count($item['options']) !== 5 || $item['options'][0] !== $item['answer']) {
            throw new RuntimeException('Invalid editorial item for '.$question['uuid']);
        }
        $normalized = array_map(static fn ($value) => mb_strtolower(trim(preg_replace('/\s+/u', ' ', str_replace(['’', 'ʼ'], "'", $value)))), $item['options']);
        if (count(array_unique($normalized)) !== 5) {
            throw new RuntimeException('Equivalent authored options for '.$question['uuid']);
        }
        if (substr_count($item['question'], '{a1}') !== 1) {
            throw new RuntimeException('Expected one a1 in '.$question['uuid']);
        }
        $question['question'] = $item['question'];
        $question['variants'] = [$item['question']]; // Presentation alternatives, not accepted answers.
        $markerTags = $question['markers']['a1']['gap_tags'];
        if ($family === 'Questions' && $item['focus'] === 'embedded') {
            $markerTags = ['question_order', 'wh_questions'];
            $question['tag_keys'] = $markerTags;
        }
        if ($family === 'Questions' && $item['focus'] === 'subject_q') {
            $markerTags = ['wh_questions', 'question_order'];
            $question['tag_keys'] = $markerTags;
        }
        $question['markers'] = [];
        $question['localizations'] = [];
        $timeTags = [
            'duration' => 'for_duration', 'start' => 'since_start_point',
            'whole' => 'all_period', 'prior' => 'before', 'deadline' => 'by_the_time',
            'endpoint' => 'until_then', 'elapsed' => 'how_long',
            'frequency' => 'duration_before_past', 'throughout' => 'duration_before_past',
        ];
        if ($family === 'TimeExpressions') {
            $question['tag_keys'] = [];
        }
        foreach (['a1' => $item] + ($item['extra_markers'] ?? []) as $marker => $markerItem) {
            $hints = ppcQualityHint($markerItem);
            $tags = $family === 'TimeExpressions' ? [$timeTags[$markerItem['focus']]] : $markerTags;
            $question['markers'][$marker] = [
                'answer' => $markerItem['answer'],
                'options' => $markerItem['options'],
                'verb_hint' => $hints['uk'],
                'gap_tags' => $tags,
            ];
            if ($family === 'TimeExpressions') {
                $question['tag_keys'] = array_values(array_unique(array_merge($question['tag_keys'], $tags)));
            }
            foreach ($hints as $locale => $hint) {
                $question['localizations'][$locale]['verb_hints'][$marker] = $hint;
            }
        }
        if ($family === 'TimeExpressions' && $index % 12 === 10) {
            $question['tag_keys'][] = 'past_perfect_simple_contrast';
        }
        $source = $sources[$family][$question['level']][$index % 12] ?? null;
        if (! is_array($source) || count($source) !== 2) {
            throw new RuntimeException('Missing authored compose source for '.$question['uuid']);
        }
        $question['source_text_uk'] = $source[0];
        $question['localizations']['uk']['source_text'] = $source[0];
        $question['localizations']['pl']['source_text'] = $source[1];
        $question['localizations']['en']['source_text'] = 'Reconstruct the sentence in the displayed clause order. Keep the stated subjects and time-phrase positions; fill each blank using its learning hint: '
            .preg_replace('/\{a\d+\}/', '____', $item['question']);
    }
    unset($question);
    return $definition;
}
