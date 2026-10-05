<?php

declare(strict_types=1);

/**
 * Finite, editorial Sentence Builder projection for Past Perfect Continuous.
 * Every row is independently authored: target | Ukrainian meaning | English
 * semantic task (never the completed target) | Polish meaning | learning focus.
 * Slots retain UUIDs and relations; changing a subject is not a level upgrade.
 */
function ppcQualityBuilderRows(string $text): array
{
    return array_map(static function (string $line): array {
        $parts = array_map('trim', explode('|', $line));
        if (count($parts) !== 5) {
            throw new RuntimeException('Invalid authored PPC builder row: '.$line);
        }
        return array_combine(['target', 'uk', 'en', 'pl', 'focus'], $parts);
    }, array_values(array_filter(explode("\n", trim($text)), 'strlen')));
}

function ppcQualityBuilderLexemes(): array
{
    $raw = <<<'LEXEMES'
working|work|працювати|pracować
waiting|wait|чекати|czekać
studying|study|навчатися / вивчати|uczyć się / badać
walking|walk|іти пішки|iść pieszo
playing|play|грати / гратися|grać / bawić się
running|run|бігати / працювати про механізм|biegać / działać o urządzeniu
gardening|garden|працювати в саду|pracować w ogrodzie
barking|bark|гавкати|szczekać
reading|read|читати|czytać
snowing|snow|падати про сніг|padać o śniegu
raining|rain|іти про дощ|padać o deszczu
painting|paint|фарбувати|malować
queuing|queue|стояти в черзі|stać w kolejce
volunteering|volunteer|волонтерити|działać jako wolontariusz
entering|enter|потрапляти всередину|wnikać do środka
reviewing|review|переглядати докази|przeglądać dowody
visiting|visit|відвідувати|odwiedzać
changing|change|змінюватися|zmieniać się
advising|advise|радити / консультувати|doradzać
deepening|deepen|поглиблюватися|pogłębiać się
underestimating|underestimate|недооцінювати|lekceważyć / niedoszacowywać
swimming|swim|плавати|pływać
carrying|carry|нести|nieść
cooking|cook|готувати їжу|gotować
driving|drive|їхати за кермом|prowadzić
practising|practise|репетирувати / тренуватися|ćwiczyć
looking|look|шукати у сполученні з for; доглядати у сполученні з after|szukać z for; opiekować się z after
saving|save|відкладати гроші|oszczędzać
washing|wash|мити|myć
crying|cry|плакати|płakać
moving|move|пересувати / переносити|przesuwać / przenosić
learning|learn|вивчати|uczyć się
living|live|жити|mieszkać
feeding|feed|годувати|karmić
drawing|draw|малювати|rysować
testing|test|перевіряти / тестувати|testować
discussing|discuss|обговорювати|omawiać
standing|stand|стояти|stać
rising|rise|підвищуватися|rosnąć
trying|try|намагатися|próbować
staying|stay|тимчасово жити / гостювати|mieszkać tymczasowo / gościć
expecting|expect|очікувати|spodziewać się
copying|copy|списувати / копіювати|przepisywać / kopiować
slowing|slow|уповільнюватися|zwalniać
measuring|measure|вимірювати|mierzyć
rehearsing|rehearse|репетирувати|ćwiczyć do występu
talking|talk|розмовляти|rozmawiać
hearing|hear|чути повторюваний звук|słyszeć powtarzający się dźwięk
falling|fall|знижуватися|spadać
considering|consider|обмірковувати|rozważać
checking|check|перевіряти|sprawdzać
revising|revise|переробляти текст|przerabiać tekst
using|use|користуватися|korzystać
distributing|distribute|роздавати|rozdawać
leaking|leak|просочуватися / витікати|przeciekać
watching|watch|спостерігати / дивитися|obserwować / oglądać
developing|develop|розвиватися|rozwijać się
treating|treat|трактувати як|traktować jako
growing|grow|зростати|rosnąć
sharing|share|ділитися|dzielić się
weighing|weigh|зважувати ризики|rozważać ryzyko
suspecting|suspect|підозрювати|podejrzewać
fluctuating|fluctuate|коливатися|wahać się
applying|apply|застосовувати|stosować
exchanging|exchange|обмінюватися|wymieniać się
confusing|confuse|плутати|mylić
building|build|наростати|narastać
refining|refine|удосконалювати|dopracowywać
understating|understate|занижувати|zaniżać
questioning|question|ставити під сумнів|kwestionować
accumulating|accumulate|накопичуватися|gromadzić się
defining|define|визначати|definiować
interpreting|interpret|тлумачити|interpretować
attempting|attempt|намагатися здійснити|próbować osiągnąć
eroding|erode|слабшати / руйнуватися поступово|słabnąć stopniowo
sleeping|sleep|спати|spać
eating|eat|їсти|jeść
listening|listen|слухати|słuchać
feeling|feel|почуватися|czuć się
training|train|тренуватися|trenować
making|make|робити|robić
cleaning|clean|прибирати|sprzątać
preparing|prepare|готуватися / готувати|przygotowywać się / przygotowywać
ignoring|ignore|ігнорувати|ignorować
following|follow|дотримуватися|stosować się do
arguing|argue|сперечатися|spierać się
paying|pay|приділяти увагу у сполученні з attention|poświęcać uwagę z attention
recording|record|записувати|rejestrować
allowing|allow|враховувати у сполученні з for|uwzględniać z for
caring|care|доглядати у сполученні з for|opiekować się z for
comparing|compare|порівнювати|porównywać
monitoring|monitor|стежити за|monitorować
losing|lose|втрачати|tracić
concealing|conceal|приховувати|ukrywać
relying|rely|покладатися|polegać
helping|help|допомагати|pomagać
describing|describe|описувати|opisywać
postponing|postpone|відкладати рішення|odkładać decyzję
disputing|dispute|оспорювати|kwestionować
recovering|recover|відновлюватися|odradzać się
raising|raise|висловлювати / порушувати питання|zgłaszać / podnosić kwestie
overlooking|overlook|нехтувати / не помічати|pomijać
reacting|react|реагувати|reagować
examining|examine|розглядати уважно|analizować
failing|fail|давати збої|zawodzić
endorsing|endorse|підтримувати погляд|popierać pogląd
negotiating|negotiate|вести переговори|negocjować
rejecting|reject|відкидати|odrzucać
conceding|concede|визнавати аргумент правильним|przyznawać rację argumentowi
occurring|occur|виникати|występować
disagreeing|disagree|не погоджуватися / сперечатися|nie zgadzać się / spierać się
giving|give|приділяти увагу у сполученні з consideration|poświęcać uwagę z consideration
undermining|undermine|підривати|podważać
seeking|seek|прагнути / шукати|dążyć / szukać
answering|answer|відповідати|odpowiadać
consulting|consult|консультуватися з|konsultować się z
doing|do|робити|robić
avoiding|avoid|уникати|unikać
withholding|withhold|приховувати / не надавати|ukrywać / nie udostępniać
influencing|influence|впливати|wpływać
warning|warn|попереджати|ostrzegać
declining|decline|знижуватися|spadać
serving|serve|служити інтересам|służyć interesom
excluding|exclude|виключати / відхиляти|wykluczać
mistaking|mistake|помилково вважати чимось у сполученні з for|błędnie uznawać za coś z for
shaping|shape|формувати|kształtować
distinguishing|distinguish|розрізняти|odróżniać
protecting|protect|захищати|chronić
masking|mask|приховувати|maskować
discounting|discount|применшувати|bagatelizować
asking|ask|питати|pytać
speaking|speak|говорити / виступати|mówić / przemawiać
assuming|assume|припускати|zakładać
editing|edit|редагувати|redagować
repairing|repair|ремонтувати / усувати несправність|naprawiać
gathering|gather|збирати|zbierać
complaining|complain|скаржитися|skarżyć się
keeping|keep|вести щоденник у сполученні з diary|prowadzić dziennik z diary
urging|urge|наполягати на|nalegać na
deteriorating|deteriorate|погіршуватися|pogarszać się
collecting|collect|збирати|zbierać
widening|widen|розширюватися / збільшуватися|poszerzać się
shifting|shift|переходити / зміщуватися|przesuwać się
issuing|issue|видавати дозволи|wydawać pozwolenia
spreading|spread|поширюватися|rozpowszechniać się
stretching|stretch|розширювати межі|rozszerzać zakres
drifting|drift|поступово відхилятися|stopniowo się odchylać
presenting|present|подавати як|przedstawiać jako
polarising|polarise|поляризуватися|polaryzować się
recurring|recur|повторюватися|powtarzać się
LEXEMES;
    $result = [];
    foreach (explode("\n", $raw) as $line) {
        [$form, $lemma, $uk, $pl] = explode('|', $line);
        $result[$form] = ['lemma' => $lemma, 'uk' => $uk, 'pl' => $pl];
    }
    return $result;
}

/** Lexical basis only: no complete inflected answer is ever exposed. */
function ppcQualityBuilderLexeme(array $row): ?array
{
    if (preg_match('/\bbeen ([a-z]+ing)\b/', $row['target'], $match)) {
        $lexeme = ppcQualityBuilderLexemes()[$match[1]] ?? null;
        if ($lexeme === null) {
            throw new RuntimeException('Missing lexical review: '.$match[1]);
        }
        return ['token' => $match[1]] + $lexeme;
    }
    return match ($row['focus']) {
        'state-contrast' => ['token' => 'known', 'lemma' => 'know', 'uk' => 'знати', 'pl' => 'znać'],
        'desire-state' => ['token' => 'wanted', 'lemma' => 'want', 'uk' => 'хотіти', 'pl' => 'chcieć'],
        'ownership-state' => ['token' => 'owned', 'lemma' => 'own', 'uk' => 'володіти', 'pl' => 'posiadać'],
        'belief-state' => ['token' => 'believed', 'lemma' => 'believe', 'uk' => 'вірити', 'pl' => 'wierzyć'],
        default => null,
    };
}

function ppcQualityBuilderHints(string $bank, array $row): array
{
    $target = $row['target'];
    if (preg_match('/^short-(positive|negative)-/', $row['focus'])) {
        return [
            'uk' => 'Визнач полярність відповіді з умови та змісту запитання. Заміни названу особу або предмет відповідним займенником; основну дію не повторюй.',
            'en' => 'Determine the polarity from the facts and the meaning of the question. Use the appropriate pronoun without repeating the main activity.',
            'pl' => 'Ustal odpowiedź twierdzącą lub przeczącą na podstawie warunków i znaczenia pytania. Użyj odpowiedniego zaimka bez powtarzania głównej czynności.',
        ];
    }
    $specific = match ($row['focus']) {
        'state-contrast', 'desire-state', 'ownership-state', 'belief-state' => [
            'uk' => 'Це тривалий стан до минулого моменту, а не процес: використовуй Past Perfect Simple, без форми на -ing.',
            'en' => 'This is a state lasting up to a past point, not an activity: use the past perfect simple, without an -ing form.',
            'pl' => 'To stan trwający do momentu w przeszłości, nie czynność: użyj Past Perfect Simple bez formy z -ing.',
        ],
        'state-activity-contrast', 'existence-versus-practice', 'acceptance-versus-questioning', 'achievement-versus-attempt' => [
            'uk' => 'Не прирівнюй завершений результат або стан до тривалого процесу. Обидві частини мають зберегти свої значення.',
            'en' => 'Keep the distinction between a completed result or state and an ongoing process in the two clauses.',
            'pl' => 'Zachowaj różnicę między osiągniętym wynikiem lub stanem a trwającym procesem w obu częściach zdania.',
        ],
        'negated-inference', 'denial-of-negation' => [
            'uk' => 'Збережи два різні заперечення: одне стосується повідомлення чи висновку, друге — вкладеної тривалої дії. Вони не взаємозамінні.',
            'en' => 'Preserve both negatives: one belongs to the report or inference, the other to the embedded ongoing activity.',
            'pl' => 'Zachowaj oba przeczenia: jedno dotyczy wypowiedzi lub wniosku, drugie zagnieżdżonej trwającej czynności.',
        ],
        'embedded-why', 'embedded-object', 'reported-duration', 'reported-whether', 'reported-beneficiary', 'embedded-scope', 'reported-subject-question' => [
            'uk' => 'Питання вкладене в повідомлення: у вкладеній частині порядок підмета й допоміжного дієслова звичайний, без інверсії.',
            'en' => 'The question is embedded in a report: keep ordinary subject–auxiliary order in the embedded clause.',
            'pl' => 'Pytanie jest zagnieżdżone w wypowiedzi: zachowaj zwykły szyk podmiot–czasownik pomocniczy, bez inwersji.',
        ],
        'who-subject', 'subject-phrasal', 'quantity-subject', 'adviser-subject', 'subject-participation', 'narrative-agent' => [
            'uk' => 'Питальне слово саме називає підмет. Не додавай іншого підмета й не використовуй допоміжне do.',
            'en' => 'The question word itself is the subject. Do not add another subject or auxiliary do.',
            'pl' => 'Zaimek pytający sam jest podmiotem. Nie dodawaj drugiego podmiotu ani czasownika pomocniczego do.',
        ],
        'fronted-preposition', 'grounds-preposition', 'possessive-preposition', 'formal-extent' => [
            'uk' => 'У цій керованій вправі прийменник стоїть перед питальною частиною. Далі використай порядок слів прямого запитання.',
            'en' => 'This guided task puts the preposition before the question phrase. Then use direct-question word order.',
            'pl' => 'W tym zadaniu przyimek stoi przed wyrażeniem pytającym. Dalej zastosuj szyk pytania bezpośredniego.',
        ],
        default => null,
    };
    if ($specific === null && $bank === 'TimeExpressions') {
        $specific = [
            'uk' => 'Точно передай часовий зв’язок: виміряний проміжок, початок, кінець, увесь період або тривалість до названого минулого моменту. Не міняй їх місцями.',
            'en' => 'Preserve the time relation: a measured length, starting point, endpoint, whole period or duration accumulated at the named past point.',
            'pl' => 'Zachowaj relację czasu: długość okresu, początek, koniec, cały okres lub czas, który upłynął do wskazanego momentu w przeszłości.',
        ];
        if (str_contains($target, 'since') && !str_contains($target, 'for ')) {
            $specific = [
                'uk' => 'Названа дата або подія задає початок, а не кількість часу. Зв’яжи її з процесом, який тривав до минулого моменту.',
                'en' => 'The named date or event gives a starting point, not a measured length. Connect it to the earlier continuing process.',
                'pl' => 'Wskazana data lub wydarzenie wyznacza początek, nie długość okresu. Połącz je z wcześniejszym trwającym procesem.',
            ];
        } elseif (str_contains($target, 'for ') && !str_contains($target, 'since')) {
            $specific = [
                'uk' => 'Умова вимірює, скільки тривав процес до минулої події, а не називає дату його початку.',
                'en' => 'The task measures how long the process lasted before a past event, rather than naming its starting date.',
                'pl' => 'Zadanie określa długość procesu przed wydarzeniem w przeszłości, a nie datę jego rozpoczęcia.',
            ];
        } elseif (str_contains($target, 'until') || str_contains($target, 'Up to') || str_contains($target, 'up to')) {
            $specific = [
                'uk' => 'Часова частина називає кінцеву межу процесу. Не підміняй цю межу початком або тривалістю.',
                'en' => 'The time phrase names an endpoint of the process. Do not replace it with a start or a duration.',
                'pl' => 'Wyrażenie czasu określa koniec procesu. Nie zastępuj go początkiem ani długością okresu.',
            ];
        }
    }
    if ($specific === null && $bank === 'Negatives') {
        $specific = [
            'uk' => 'Збережи точну сферу заперечення: дія, її тривалість, якість, учасник, об’єкт або причина. Заперечення однієї ознаки не скасовує всю дію.',
            'en' => 'Keep the precise scope of negation: the activity, duration, quality, participant, object or reason. Negating one feature need not deny the activity.',
            'pl' => 'Zachowaj dokładny zakres przeczenia: czynność, czas trwania, jakość, uczestnik, obiekt lub powód. Zaprzeczenie jednej cechy nie musi negować całej czynności.',
        ];
    }
    if ($specific === null && ($bank === 'Questions' || str_ends_with($target, '?'))) {
        $specific = [
            'uk' => 'У прямому запитанні допоміжне дієслово передує підмету; питальна частина стоїть перед ним. Часовий зв’язок з минулою подією має зберегтися.',
            'en' => 'In a direct question the auxiliary precedes the subject, with the question phrase first. Preserve the relation to the later past event.',
            'pl' => 'W pytaniu bezpośrednim czasownik pomocniczy poprzedza podmiot, a wyrażenie pytające stoi na początku. Zachowaj relację do późniejszego wydarzenia.',
        ];
    }
    $specific ??= [
        'uk' => 'Умова описує процес до минулого моменту, а не лише завершений результат. Збережи причину, протиставлення або часовий зв’язок, названі в умові.',
        'en' => 'Describe the process leading up to a past point, not merely a finished result. Preserve the stated cause, contrast or time relation.',
        'pl' => 'Opisz proces trwający do momentu w przeszłości, nie tylko zakończony wynik. Zachowaj wskazaną przyczynę, kontrast lub relację czasu.',
    ];
    $lexeme = ppcQualityBuilderLexeme($row);
    if ($lexeme !== null) {
        $specific['uk'] = 'Лексична основа: '.$lexeme['lemma'].' — '.$lexeme['uk'].'. '.$specific['uk'];
        $specific['en'] = 'Lexical verb: '.$lexeme['lemma'].'. '.$specific['en'];
        $specific['pl'] = 'Czasownik podstawowy: '.$lexeme['lemma'].' — '.$lexeme['pl'].'. '.$specific['pl'];
    }
    return $specific;
}

/** Authored task/order guidance; shared by canonical and display projections. */
function ppcQualityBuilderTaskPrefixes(array $row): array
{
    $tokens = preg_split('/\s+/', rtrim($row['target'], '.?')) ?: [];
    $isShort = preg_match('/^short-(positive|negative)-/', $row['focus']) === 1;
    $isQuestion = str_ends_with($row['target'], '?');
    $isState = in_array($row['focus'], ['state-contrast', 'desire-state', 'ownership-state', 'belief-state'], true);
    $prefixes = [
        'uk' => $isShort ? '' : ($isState
            ? 'Відтвори тривалий стан у Past Perfect Simple. '
            : 'Побудуй речення про попередній процес у Past Perfect Continuous. '),
        'en' => $isShort ? '' : ($isState
            ? 'Use the past perfect simple for a state. '
            : 'Use the past perfect continuous for the earlier process. '),
        'pl' => $isShort ? '' : ($isState
            ? 'Użyj Past Perfect Simple dla stanu. '
            : 'Użyj Past Perfect Continuous dla wcześniejszego procesu. '),
    ];
    // Explicit learning constraints, not a hidden answer-based rejection of
    // otherwise natural clause orders. Prefixing keeps ? on actual questions.
    if (!$isShort) {
        $first = $tokens[0];
        $fronted = in_array($first, ['By', 'Until', 'Since', 'Throughout', 'During', 'In', 'For', 'Up', 'Long', 'Ever', 'Although', 'Even', 'Only', 'What'], true)
            && !($isQuestion && $first === 'What');
        $order = $isQuestion
            ? ['uk' => 'Почни з питальної частини або допоміжного дієслова; обставини залиш після дієслівної групи. ', 'en' => 'Start with the question phrase or auxiliary; keep other circumstances after the verb group. ', 'pl' => 'Zacznij od wyrażenia pytającego lub czasownika pomocniczego; pozostałe okoliczniki pozostaw po grupie czasownikowej. ']
            : ($fronted
                ? ['uk' => 'Збережи порядок смислових частин умови; початкову часову, допустову або тематичну частину залиш на початку. ', 'en' => 'Preserve the order of the semantic parts; keep the opening time, concession or topic phrase first. ', 'pl' => 'Zachowaj kolejność części znaczeniowych; początkowe wyrażenie czasu, przyzwolenia lub tematu pozostaw na początku. ']
                : ['uk' => 'Почни з підмета головної частини; часові та причинні обставини не перенось на початок. ', 'en' => 'Start with the main-clause subject; do not move time or reason phrases to the front. ', 'pl' => 'Zacznij od podmiotu zdania głównego; nie przenoś wyrażeń czasu ani przyczyny na początek. ']);
        if (in_array($row['focus'], ['fronted-question-duration', 'fronted-preposition', 'grounds-preposition', 'possessive-preposition', 'formal-extent'], true)) {
            $order = ['uk' => 'Почни з усієї питальної частини; далі — допоміжне дієслово й підмет. ', 'en' => 'Start with the complete question phrase, followed by the auxiliary and subject. ', 'pl' => 'Zacznij od całego wyrażenia pytającego, następnie użyj czasownika pomocniczego i podmiotu. '];
        }
        foreach ($prefixes as $locale => &$prefix) {
            $prefix .= $order[$locale];
        }
        unset($prefix);
    }
    return $prefixes;
}

/**
 * Presentation-only split. No runtime string stripping and no target injection.
 * Short answers keep all authored evidence and speaker/response-role conditions.
 */
function ppcQualityBuilderDisplayProjection(string $bank, array $question, array $row): array
{
    $prefixes = ppcQualityBuilderTaskPrefixes($row);
    $canonical = ppcQualityBuilderProjection($bank, $question, $row);
    $locales = [];
    foreach (['uk', 'en', 'pl'] as $locale) {
        $expected = $prefixes[$locale].$row[$locale];
        if (($question['localizations'][$locale]['source_text'] ?? null) !== $expected
            || $canonical['localizations'][$locale]['source_text'] !== $expected) {
            throw new RuntimeException('Canonical builder source drift: '.$question['uuid'].'/'.$locale);
        }
        $locales[$locale] = [
            'expected_source' => $expected,
            'display_source' => $row[$locale],
            'instructions' => trim($prefixes[$locale]),
        ];
    }
    return $locales;
}

function ppcQualityBuilderProjection(string $bank, array $question, array $row): array
{
    $tokens = preg_split('/\s+/', rtrim($row['target'], '.?')) ?: [];
    $answers = [];
    foreach ($tokens as $index => $token) {
        $answers['a'.($index + 1)] = $token;
    }
    $hints = ppcQualityBuilderHints($bank, $row);
    $isShort = preg_match('/^short-(positive|negative)-/', $row['focus']) === 1;
    $isQuestion = str_ends_with($row['target'], '?');
    $isState = in_array($row['focus'], ['state-contrast', 'desire-state', 'ownership-state', 'belief-state'], true);
    $prefixes = ppcQualityBuilderTaskPrefixes($row);
    $question['question'] = $prefixes['uk'].$row['uk'];
    $question['source_text_uk'] = $question['question'];
    $question['target_text'] = $row['target'];
    $question['answers'] = $answers;
    $question['tokens_correct'] = $tokens;
    $question['hint_uk'] = $hints['uk'];
    $question['hints'] = [$hints['uk']];
    $question['variants'] = [];
    unset($question['verb_hints']);
    $correct = array_map('strtolower', $tokens);
    $potential = $isShort ? ['was', 'were', 'have', 'has', 'did', 'been'] : ['have', 'has', 'was', 'were', 'did'];
    $lexeme = ppcQualityBuilderLexeme($row);
    if ($lexeme !== null) {
        $potential[] = $lexeme['lemma'];
    }
    if ($bank === 'TimeExpressions') {
        $potential = array_merge($potential, ['for', 'since', 'until', 'during']);
    }
    if ($isState) {
        $potential[] = 'been';
        $potential[] = match ($row['focus']) {
            'state-contrast' => 'knowing', 'desire-state' => 'wanting', 'ownership-state' => 'owning', 'belief-state' => 'believing',
        };
    }
    $question['distractors'] = array_values(array_filter(array_unique($potential), static fn ($token) => !in_array(strtolower($token), $correct, true)));
    $alternatives = [];
    if (preg_match('/\bhad not\b/i', $row['target']) || ($isQuestion && in_array('not', $correct, true))) {
        $alternatives[] = $isQuestion && str_starts_with($row['target'], 'Had ') ? "Hadn't" : "hadn't";
    }
    if (str_contains($row['target'], 'did not')) {
        $alternatives[] = "didn't";
    }
    $question['options'] = array_merge($tokens, $alternatives, $question['distractors']);
    $question['compose_accepted_token_variants'] = $alternatives;
    $question['localizations'] = [
        'uk' => ['source_text' => $question['question'], 'hints' => [$hints['uk']]],
        'en' => ['source_text' => $prefixes['en'].$row['en'], 'hints' => [$hints['en']]],
        'pl' => ['source_text' => $prefixes['pl'].$row['pl'], 'hints' => [$hints['pl']]],
    ];
    if ($bank === 'BasicsB2') {
        foreach ($question['localizations'] as &$payload) {
            $payload['hint_provider'] = 'polyglot-v3';
        }
        unset($payload);
    }
    $rationale = [
        'A1' => 'Guided short construction with familiar vocabulary and one form/time operation.',
        'A2' => 'Guided construction with a concrete prior context, one added scope or temporal distinction.',
        'B1' => 'Independent construction links earlier activity to a later event, cause or reported perspective.',
        'B2' => 'Independent construction preserves a contrast, scope, causal interpretation or two-step temporal relation.',
        'C1' => 'Precise retrospective discourse, reported/embedded perspective, scope or formal question construction.',
        'C2' => 'Precise discourse reinterpretation, nested scope, evidential distinction or relational time frame.',
    ];
    $question['quality_review'] = [
        'learning_focus' => $row['focus'],
        'level_rationale' => $rationale[$question['level']],
        'near_duplicate_decision' => 'Reviewed all bank slots; kept '.$row['focus'].' as a distinct operation: '.$row['en'].' Shared morphology is not itself a duplicate; the legacy aligned cross-level template is not reused.',
        'uuid_identity' => 'Editorial revision of the existing subtopic/level slot; stable UUID and progress relations retained.',
        'word_order_policy' => 'Visible guided-order constraint; contractions accepted by the existing EnglishAnswerVariants matcher, not variants.',
    ];
    return $question;
}

function ppcQualityBuilderBanks(): array
{
    return [
        'BasicsB2' => [
            'B2' => ppcQualityBuilderRows(<<<'ROWS'
I had been editing the wrong chapter when she called.|Коли вона зателефонувала, я весь цей час редагував не той розділ.|Describe sustained work on the wrong chapter up to her call.|Kiedy zadzwoniła, przez cały ten czas redagowałem niewłaściwy rozdział.|retrospective-wrong-object
She had been studying the cases rather than memorising the answers.|Вона весь цей час вивчала випадки, а не заучувала відповіді.|Contrast study of cases with rote learning of answers.|Przez cały ten czas analizowała przypadki, zamiast uczyć się odpowiedzi na pamięć.|study-versus-memorising
They had been waiting for permission before they changed the schedule.|Вони чекали на дозвіл перед тим, як змінили розклад.|Explain a preceding wait for authorisation before a schedule change.|Czekali na pozwolenie, zanim zmienili harmonogram.|authorisation-before-action
We had been driving along the coast until the road was blocked.|Ми їхали вздовж узбережжя аж до того, як дорогу перекрили.|Describe coastal driving ending when access to the road was blocked.|Jechaliśmy wzdłuż wybrzeża, aż droga została zablokowana.|external-endpoint
He had been practising without a teacher before he developed the bad habit.|Він тренувався без учителя перед тим, як у нього сформувалася шкідлива звичка.|Connect unsupervised practice with the later development of a bad habit.|Ćwiczył bez nauczyciela, zanim nabrał złego nawyku.|practice-cause
The team had been discussing possible failures when the manager asked about costs.|Команда вже обговорювала можливі збої, коли керівник запитав про витрати.|Contrast the team's previous discussion topic with the manager's later question.|Zespół już omawiał możliwe awarie, kiedy kierownik zapytał o koszty.|topic-shift
I had been reading the draft but had not checked the appendix.|Я читав чернетку, але ще не перевірив додаток.|Contrast an ongoing reading process with an incomplete separate check.|Czytałem szkic, ale jeszcze nie sprawdziłem załącznika.|activity-and-uncompleted-result
She had been living abroad temporarily before the contract ended.|Вона тимчасово жила за кордоном до завершення контракту.|Describe temporary overseas residence before a contract ended.|Tymczasowo mieszkała za granicą przed końcem umowy.|temporary-state-perspective
They had been looking for a cheaper supplier when prices rose again.|Вони вже шукали дешевшого постачальника, коли ціни знову зросли.|Describe a search already underway before another price increase.|Szukali już tańszego dostawcy, kiedy ceny ponownie wzrosły.|search-before-trigger
We had been training on dry ground before the course flooded.|Ми тренувалися на сухій землі перед тим, як трасу затопило.|Contrast earlier training conditions with a later flooded course.|Trenowaliśmy na suchym terenie, zanim trasę zalało.|changed-conditions
He had been repairing the lock rather than replacing the door.|Він увесь цей час ремонтував замок, а не замінював двері.|Distinguish two plausible repair activities by their object and operation.|Przez cały ten czas naprawiał zamek, zamiast wymieniać drzwi.|repair-versus-replacement
The children had been playing indoors because the garden was unsafe.|Діти гралися в приміщенні, бо сад був небезпечним.|Explain the location of the earlier play through an unsafe garden.|Dzieci bawiły się w środku, bo ogród był niebezpieczny.|activity-and-state-cause
I had not been sleeping deeply before the alarm rang.|Перед сигналом будильника я спав не міцно.|Negate depth of sleep before the alarm, not all sleep.|Przed dzwonkiem budzika nie spałem głęboko.|not-deeply
She had not been preparing for this type of question before the interview.|До співбесіди вона готувалася не до такого типу запитань.|Reject the relevant question type as the object of her prior preparation.|Przed rozmową nie przygotowywała się do tego rodzaju pytań.|preparation-object-scope
They had not been waiting outside long when the organiser let them in.|Коли організатор впустив їх, вони чекали надворі ще недовго.|Describe a short outdoor wait before being admitted.|Kiedy organizator ich wpuścił, czekali na dworze jeszcze niedługo.|short-outdoor-duration
We had not been living together before we signed the lease.|Ми жили не разом до підписання договору оренди.|Negate living together before a shared lease without denying separate residences.|Nie mieszkaliśmy razem przed podpisaniem umowy najmu.|co-residence-scope
He had not been working towards the agreed goal before the review.|До перевірки він працював не заради узгодженої мети.|Reject the agreed goal as the direction of his preceding efforts.|Przed przeglądem nie pracował nad osiągnięciem uzgodnionego celu.|goal-scope
The students had not been listening for changes in pronunciation.|Студенти вслухалися не в зміни вимови.|Reject pronunciation changes as the object they were listening for.|Studenci nie nasłuchiwali zmian w wymowie.|listening-for-object
She had not been feeling any better despite the treatment.|Попри лікування вона не почувалася краще.|Negate improvement over time despite a treatment, not the treatment itself.|Mimo leczenia wcale nie czuła się lepiej.|lack-of-improvement
We had not been training at full intensity before the final.|Перед фіналом ми тренувалися не з повною інтенсивністю.|Reject full intensity of the preceding training period.|Przed finałem nie trenowaliśmy z pełną intensywnością.|intensity-scope
He had not been looking for a permanent job before the move.|До переїзду він шукав не постійну роботу.|Reject permanence as a property of the job he sought.|Przed przeprowadzką nie szukał stałej pracy.|job-kind-scope
They had not been talking directly to the owner before the dispute.|До суперечки вони розмовляли не безпосередньо з власником.|Negate direct contact with the owner during earlier discussions.|Przed sporem nie rozmawiali bezpośrednio z właścicielem.|directness-scope
I had not been using the latest version when the error occurred.|Коли виникла помилка, я користувався не найновішою версією.|Reject the latest version as the one in prior use at the error.|Kiedy wystąpił błąd, nie korzystałem z najnowszej wersji.|version-scope
She had not been crying from sadness before we spoke.|Перед нашою розмовою вона плакала не від смутку.|Reject sadness as the motive for earlier crying without denying tears.|Przed naszą rozmową nie płakała ze smutku.|cause-scope
Had they been waiting for approval rather than for supplies?|Вони чекали на схвалення, а не на матеріали?|Ask which of two possible objects motivated the earlier wait.|Czy czekali na zgodę, a nie na materiały?|yes-no-object-contrast
Had you been working towards a different target when I called?|Коли я зателефонував, ти працював заради іншої цілі?|Ask whether a different goal directed your listener's preceding work.|Czy w chwili mojego telefonu pracowałeś nad innym celem?|goal-confirmation
Had she been sleeping badly because of the night shifts?|Вона погано спала через нічні зміни?|Ask whether night shifts explain her earlier poor sleep.|Czy źle sypiała z powodu nocnych zmian?|cause-confirmation
Had we been driving in the wrong direction before we stopped?|До зупинки ми їхали не в тому напрямку?|Ask whether our earlier route direction was wrong.|Czy przed postojem jechaliśmy w niewłaściwym kierunku?|direction-check
Had he been studying independently before the course began?|Він навчався самостійно перед початком курсу?|Ask whether independent study preceded his formal course.|Czy uczył się samodzielnie przed rozpoczęciem kursu?|prior-independence
Had they been training regularly despite the lack of equipment?|Попри брак обладнання вони тренувалися регулярно?|Ask whether regular earlier training occurred despite a practical obstacle.|Czy trenowali regularnie mimo braku sprzętu?|concessive-confirmation
Had she been living alone before the relatives arrived?|Вона жила сама до приїзду родичів?|Ask whether she lived alone in the period preceding her relatives' arrival.|Czy mieszkała sama przed przyjazdem krewnych?|prior-household
Had the team been discussing risks without recording its decisions?|Команда обговорювала ризики, не записуючи своїх рішень?|Ask whether an earlier discussion lacked a parallel record-keeping activity.|Czy zespół omawiał ryzyko, nie zapisując swoich decyzji?|activity-without-record
Had the children been playing with the broken toy before the accident?|Діти гралися зламаною іграшкою перед аварією?|Ask whether the earlier play involved the specific broken toy.|Czy dzieci bawiły się uszkodzoną zabawką przed wypadkiem?|specific-object-confirmation
Had he been repairing the same fault repeatedly before replacing the part?|Він неодноразово усував ту саму несправність перед заміною деталі?|Ask whether repeated repair of one fault preceded replacement.|Czy wielokrotnie naprawiał tę samą usterkę przed wymianą części?|repetition-before-result
Had you been reading a summary rather than the full report?|Ти читав короткий виклад, а не повний звіт?|Ask whether the material read was a summary rather than the full report.|Czy czytałeś streszczenie, a nie pełny raport?|document-contrast
Had she been waiting for us at the wrong entrance?|Вона чекала на нас біля іншого, неправильного входу?|Ask whether her earlier wait for us took place at the incorrect entrance.|Czy czekała na nas przy niewłaściwym wejściu?|wrong-location-confirmation
How long had you been studying the evidence before forming an opinion?|Як довго ти вивчав докази перед тим, як сформував думку?|Ask about the time spent examining evidence before reaching an opinion.|Jak długo analizowałeś dowody przed wyrobieniem sobie opinii?|duration-before-judgement
How often had they been working overtime before the office closed?|Як часто вони працювали понаднормово до закриття офісу?|Ask about the frequency of earlier overtime rather than its total duration.|Jak często pracowali po godzinach przed zamknięciem biura?|frequency-not-duration
What had she been doing differently before the results improved?|Що вона робила інакше перед тим, як результати покращилися?|Ask about a changed prior practice that preceded better results.|Co robiła inaczej, zanim wyniki się poprawiły?|changed-method
Where had you been living while the house was being repaired?|Де ти жив, поки будинок ремонтували?|Ask for temporary accommodation during an earlier house repair.|Gdzie mieszkałeś, kiedy remontowano dom?|overlap-accommodation
Why had he been running tests without a control group?|Чому він проводив тести без контрольної групи?|Ask for the reason behind an earlier testing method lacking a control group.|Dlaczego przeprowadzał testy bez grupy kontrolnej?|method-reason
How closely had we been watching the trend before the announcement?|Наскільки уважно ми стежили за тенденцією до оголошення?|Ask about the degree of attention to an earlier trend.|Jak uważnie obserwowaliśmy trend przed ogłoszeniem?|monitoring-degree
Which part of the design had the team been working on before the launch?|Над якою частиною конструкції команда працювала до запуску?|Ask for a particular design component as the object of preceding work.|Nad którą częścią projektu pracował zespół przed uruchomieniem?|part-object
Who had she been talking to without telling the rest of us?|З ким вона розмовляла, не повідомляючи решті з нас?|Ask about an undisclosed earlier conversation partner.|Z kim rozmawiała, nie informując reszty z nas?|partner-and-omission
Why had they not been training under realistic conditions?|Чому вони тренувалися не в реалістичних умовах?|Ask why prior training lacked realistic conditions.|Dlaczego nie trenowali w realistycznych warunkach?|negative-condition-question
What had the children been playing with before the glass broke?|Чим діти гралися перед тим, як скло розбилося?|Ask which object was involved in play preceding broken glass.|Czym dzieci się bawiły, zanim pękło szkło?|object-with-preposition
How much progress had she been making before the course ended?|Наскільки вона просувалася в навчанні до завершення курсу?|Ask about the amount of progress developing before a course ended.|Jak duże postępy robiła przed końcem kursu?|progress-amount
What had you been reading that made you question the decision?|Що ти читав перед тим, як це змусило тебе поставити рішення під сумнів?|Ask for earlier reading that prompted doubt about a decision.|Co czytałeś, co skłoniło cię do zakwestionowania decyzji?|cause-linked-object
ROWS),
        ],
        'TimeExpressions' => [
            'A1' => ppcQualityBuilderRows(<<<'ROWS'
They had been waiting for two hours before the doors opened.|Вони чекали дві години, перш ніж двері відчинилися.|Describe a two-hour wait before the doors opened; express a length of time.|Czekali przez dwie godziny, zanim otworzyły się drzwi.|duration-for
She had been working since morning when dinner started.|Коли почалася вечеря, вона працювала вже з ранку.|Describe work beginning in the morning and continuing up to dinner.|Kiedy zaczęła się kolacja, pracowała już od rana.|start-since
He had been running all morning before lunch.|Він бігав увесь ранок перед обідом.|Describe running throughout the morning before lunch.|Biegał przez cały ranek przed obiadem.|whole-all
We had been preparing the room before the guests arrived.|Ми готували кімнату перед приходом гостей.|Place our room preparation earlier than the guests' arrival.|Przygotowywaliśmy pokój przed przyjściem gości.|earlier-before
By the time we arrived, it had been raining for hours.|На момент нашого приїзду дощ ішов уже кілька годин.|Put our arrival first as the point by which hours of rain had accumulated.|Do chwili naszego przyjazdu padało już od kilku godzin.|by-the-time
The children had been playing until it got dark.|Діти гралися аж до того, як стемніло.|Mark darkness as the endpoint of the children's play.|Dzieci bawiły się, aż zrobiło się ciemno.|endpoint-until
She had been practising since the lesson began.|Вона репетирувала відтоді, як почався урок.|Use the beginning of the lesson as the event from which her earlier practice continued.|Ćwiczyła, odkąd zaczęła się lekcja.|event-as-start
The team had been training for three weeks before the match.|Команда тренувалася три тижні перед матчем.|Describe three weeks of training leading up to a match.|Zespół trenował przez trzy tygodnie przed meczem.|weeks-for
They had been living there since 2019 when they moved.|Коли вони переїхали, то жили там уже з 2019 року.|Use 2019 as the starting point of residence ending at a move.|Kiedy się przeprowadzili, mieszkali tam już od 2019 roku.|calendar-since
I had been reading all evening when you called.|Коли ти зателефонував, я читав уже весь вечір.|Describe reading throughout the evening up to your listener's call.|Kiedy zadzwoniłeś, czytałem już przez cały wieczór.|evening-all
The baby had been sleeping for an hour at that point.|На той момент дитина спала вже годину.|Describe an hour of sleep accumulated at the past point mentioned.|W tamtym momencie dziecko spało już od godziny.|past-point
We had been walking since noon before we stopped.|Ми йшли з полудня, перш ніж зупинилися.|Use noon as the beginning of our walk preceding a stop.|Szliśmy od południa, zanim się zatrzymaliśmy.|clock-since
ROWS),
            'A2' => ppcQualityBuilderRows(<<<'ROWS'
Emma had been practising for half an hour when the lesson began.|Коли почався урок, Емма репетирувала вже пів години.|Express a half-hour practice duration before the lesson began.|Kiedy zaczęła się lekcja, Emma ćwiczyła już od pół godziny.|fraction-duration
Tom had been saving since his birthday before he bought the bike.|Том відкладав гроші з дня народження, перш ніж купив велосипед.|Use a birthday event as the starting point of saving before a bicycle purchase.|Tom odkładał pieniądze od urodzin, zanim kupił rower.|event-start
The neighbours had been painting all weekend before Monday.|Сусіди фарбували протягом усіх вихідних перед понеділком.|Describe painting throughout the weekend preceding Monday.|Sąsiedzi malowali przez cały weekend przed poniedziałkiem.|weekend-all
We had been driving until midnight before we rested.|Ми їхали аж до півночі, перш ніж відпочили.|Mark midnight as the endpoint of driving followed by rest.|Jechaliśmy aż do północy, zanim odpoczęliśmy.|clock-endpoint
By the time the shop closed, I had been queuing for forty minutes.|На момент закриття магазину я стояв у черзі вже сорок хвилин.|Put the shop's closing first as the past point reached after forty minutes in a queue.|Do chwili zamknięcia sklepu stałem w kolejce już od czterdziestu minut.|accumulated-to-closure
She had been feeling ill since the journey began.|Вона почувалася недобре від початку подорожі.|Use the journey's beginning as the start of an earlier continuing illness.|Źle się czuła od początku podróży.|clausal-since
They had been staying with us for a few days before the wedding.|Вони гостювали в нас кілька днів перед весіллям.|Express an approximate length of a visit before the wedding.|Gościli u nas przez kilka dni przed ślubem.|approximate-duration
The dog had been barking during the night before we found it.|Собака гавкав уночі перед тим, як ми його знайшли.|Locate barking within the preceding night rather than give its exact duration.|Pies szczekał w nocy, zanim go znaleźliśmy.|during-period
I had been learning the song since last Tuesday when we performed it.|Коли ми виконали пісню, я вчив її вже з минулого вівторка.|Use last Tuesday as the starting point of learning up to a performance.|Kiedy wykonaliśmy piosenkę, uczyłem się jej już od zeszłego wtorku.|relative-day-start
The children had been playing for a while before lunch.|Діти гралися деякий час перед обідом.|Express an unspecified short duration of play before lunch.|Dzieci bawiły się przez pewien czas przed obiadem.|for-a-while
We had been working all day up to that moment.|До того моменту ми працювали вже цілий день.|Describe a whole working day extending up to a past moment.|Do tego momentu pracowaliśmy już przez cały dzień.|up-to-point
How long had he been waiting when the taxi arrived?|Як довго він уже чекав, коли приїхало таксі?|Ask about accumulated waiting time at the taxi's arrival.|Jak długo już czekał, kiedy przyjechała taksówka?|when-reference
ROWS),
            'B1' => ppcQualityBuilderRows(<<<'ROWS'
The river had been rising for several days before the warning.|Рівень води в річці підвищувався кілька днів до попередження.|Express several days of a rising trend before a warning.|Poziom wody w rzece podnosił się przez kilka dni przed ostrzeżeniem.|trend-duration-for
Maria had been working remotely since the office closed.|Марія працювала дистанційно відтоді, як офіс закрився.|Use an office closure event as the start of remote work.|Maria pracowała zdalnie, odkąd biuro zostało zamknięte.|event-since
We had been checking the figures throughout the afternoon before submission.|Ми перевіряли цифри впродовж усього пополудня перед поданням.|Express the whole afternoon as a period filled by checking before submission.|Sprawdzaliśmy liczby przez całe popołudnie przed złożeniem.|throughout
The technicians had been testing the alarm until the fault appeared.|Техніки перевіряли сигналізацію аж до появи несправності.|Mark the appearance of a fault as the endpoint of testing.|Technicy testowali alarm, aż pojawiła się usterka.|event-until
By then the passengers had been standing for nearly an hour.|На той час пасажири стояли вже майже годину.|Start with the past reference expression and give a duration just short of an hour.|Do tego czasu pasażerowie stali już prawie od godziny.|by-then-nearly
She had been volunteering there since she left school.|Вона волонтерила там відтоді, як закінчила школу.|Use leaving school as the start of volunteering at an earlier reference point.|Działała tam jako wolontariuszka, odkąd skończyła szkołę.|since-clause
The band had been rehearsing over the previous month before the tour.|Гурт репетирував протягом попереднього місяця перед туром.|Place rehearsal in the month preceding a past tour, not the current month.|Zespół ćwiczył przez poprzedni miesiąc przed trasą.|previous-period
We had been discussing the route while the guide checked the weather.|Ми вже деякий час обговорювали маршрут, поки гід перевіряв погоду.|Connect an earlier ongoing discussion with an overlapping weather check.|Od pewnego czasu omawialiśmy trasę, podczas gdy przewodnik sprawdzał pogodę.|overlap-while
They had been repairing the bridge long before the flood.|Вони ремонтували міст задовго до повені.|Show that bridge repairs began well in advance of the flood.|Naprawiali most na długo przed powodzią.|long-before
I had been looking for work for just a week when the offer came.|Коли надійшла пропозиція, я шукав роботу лише тиждень.|Emphasise that the job-search duration at the offer was only one week.|Kiedy przyszła oferta, szukałem pracy dopiero od tygodnia.|just-duration
The nurse had been helping him since his first appointment.|Медсестра допомагала йому від першого прийому.|Use his first appointment as the start of the earlier help.|Pielęgniarka pomagała mu od pierwszej wizyty.|noun-event-start
How long had the machine been running by the time you switched it off?|Як довго машина вже працювала на момент, коли ти її вимкнув?|Ask for duration accumulated by the moment of switching off.|Jak długo maszyna już pracowała do chwili, gdy ją wyłączyłeś?|duration-by-time
ROWS),
            'B2' => ppcQualityBuilderRows(<<<'ROWS'
Demand had been declining ever since the prices rose.|Попит знижувався весь час відтоді, як ціни зросли.|Emphasise uninterrupted time from the price increase to a later past point.|Popyt spadał przez cały czas, odkąd ceny wzrosły.|ever-since
The team had been gathering feedback for the preceding six months.|Команда збирала відгуки протягом попередніх шести місяців.|Measure feedback gathering backwards from a past reference point over six months.|Zespół zbierał opinie przez poprzednie sześć miesięcy.|preceding-duration
Until the inspection they had been using an unapproved method.|До перевірки вони весь цей час застосовували незатверджений метод.|Put the endpoint first and describe the method used up to that point.|Do kontroli przez cały ten czas stosowali niezatwierdzoną metodę.|fronted-until
By the time the leak was detected, water had been entering the wall for days.|На момент виявлення витоку вода просочувалася в стіну вже кілька днів.|Put detection first and describe days of leakage already accumulated.|Do chwili wykrycia wycieku woda wnikała w ścianę już od kilku dni.|detection-delay
She had been revising the article in the weeks leading up to publication.|Вона переробляла статтю протягом тижнів, що передували публікації.|Define the revision period by its approach to a later publication.|Przerabiała artykuł w tygodniach poprzedzających publikację.|leading-up-to
We had been negotiating up until the moment the offer was withdrawn.|Ми вели переговори аж до моменту відкликання пропозиції.|Emphasise the exact endpoint at which negotiation stopped.|Negocjowaliśmy aż do chwili wycofania oferty.|up-until-exact
The staff had been complaining since well before the reorganisation.|Працівники скаржилися ще задовго до реорганізації й далі продовжували це робити.|Place the beginning of complaints substantially earlier than the reorganisation.|Pracownicy skarżyli się już na długo przed reorganizacją i dalej to robili.|since-before
Throughout that winter the volunteers had been distributing hot meals.|Усю ту зиму волонтери роздавали гарячі страви.|Put the whole past winter first as the period of repeated distribution.|Przez całą tamtą zimę wolontariusze rozdawali gorące posiłki.|fronted-throughout
I had been keeping a diary since I joined the expedition.|Я вів щоденник відтоді, як приєднався до експедиції.|Use joining an expedition as the starting event for keeping a diary.|Prowadziłem dziennik, odkąd dołączyłem do wyprawy.|start-to-reference
The company had been losing customers for some time before it noticed.|Компанія втрачала клієнтів уже деякий час, перш ніж це помітила.|Express an ongoing loss that preceded its recognition by an unspecified period.|Firma traciła klientów już od pewnego czasu, zanim to zauważyła.|for-some-time
As the hearing approached the lawyers had been reviewing the evidence daily.|У міру наближення слухання юристи щодня переглядали докази.|Put the approaching hearing first and describe a repeated daily activity.|W miarę zbliżania się rozprawy prawnicy codziennie przeglądali dowody.|approach-frequency
How long had confidence been falling before the policy changed?|Як довго довіра знижувалася до зміни політики?|Ask how long a downward trend preceded a policy change.|Jak długo zaufanie spadało przed zmianą polityki?|trend-boundary-question
ROWS),
            'C1' => ppcQualityBuilderRows(<<<'ROWS'
By the final review the researchers had been collecting data for almost a decade.|На момент остаточної перевірки дослідники збирали дані вже майже десятиліття.|Put the final review first and quantify the accumulated collection period just below a decade.|Do końcowego przeglądu badacze zbierali dane już prawie od dekady.|by-nearly-decade
The disagreements had been widening from the outset until the talks collapsed.|Розбіжності поглиблювалися від самого початку аж до провалу переговорів.|Give both the beginning and endpoint of a worsening trend.|Rozbieżności pogłębiały się od samego początku aż do załamania rozmów.|from-until
She had been questioning the estimate ever since its assumptions were disclosed.|Вона весь час ставила оцінку під сумнів відтоді, як розкрили її припущення.|Link sustained criticism to the disclosure event that initiated it.|Przez cały czas kwestionowała szacunek, odkąd ujawniono jego założenia.|disclosure-ever-since
In the months before the vote support had been shifting between the parties.|Упродовж місяців перед голосуванням підтримка переходила від одних партій до інших.|Put the pre-vote period first and describe shifting support within it.|W miesiącach przed głosowaniem poparcie przesuwało się między partiami.|bounded-background-period
The pressure had been building long before it became publicly visible.|Тиск наростав задовго до того, як став помітним громадськості.|Distinguish the earlier build-up from its later public visibility.|Presja narastała na długo przed tym, jak stała się publicznie widoczna.|onset-versus-visibility
We had been using the same criteria up to that point but not afterwards.|Ми застосовували ті самі критерії до того моменту, але не після нього.|Define a past endpoint and explicitly exclude the period after it.|Stosowaliśmy te same kryteria do tego momentu, ale nie później.|before-after-boundary
During the period under review officials had been issuing permits without checks.|Протягом періоду, який перевіряли, посадовці видавали дозволи без перевірок.|Put the reviewed period first as the interval containing repeated permit issuance.|W okresie objętym przeglądem urzędnicy wydawali pozwolenia bez kontroli.|during-reviewed-period
The equipment had been deteriorating for longer than the records suggested.|Обладнання псувалося довше, ніж свідчили записи.|Compare the actual earlier deterioration duration with the shorter duration implied by records.|Sprzęt niszczał dłużej, niż sugerowały zapisy.|comparative-duration
Since the first warning the advisers had been urging immediate action.|Від першого попередження радники наполягали на негайних діях.|Put the first warning first as the beginning of repeated appeals.|Od pierwszego ostrzeżenia doradcy nalegali na natychmiastowe działanie.|fronted-since
The witnesses had been discussing the case in the days preceding their interviews.|Свідки обговорювали справу протягом днів, що передували їхнім допитам.|Define the discussion interval relative to later interviews.|Świadkowie omawiali sprawę w dniach poprzedzających ich przesłuchania.|preceding-interviews
Until then I had been treating the figures as provisional.|До того часу я весь цей час вважав цифри попередніми.|Start with a past endpoint for the earlier provisional interpretation.|Do tego czasu traktowałem liczby jako tymczasowe.|until-then
How long before the announcement had demand been declining?|Постав часову частину перед допоміжним дієсловом. Як довго до оголошення попит уже знижувався?|Ask about prior duration and place the whole duration phrase before the auxiliary.|Umieść całą frazę czasu przed czasownikiem pomocniczym. Jak długo przed ogłoszeniem popyt już spadał?|fronted-question-duration
ROWS),
            'C2' => ppcQualityBuilderRows(<<<'ROWS'
By the time the discrepancy was acknowledged, losses had been accumulating for years.|На момент визнання розбіжності втрати накопичувалися вже роками.|Put acknowledgement first while locating years of accumulation before it.|Do chwili przyznania rozbieżności straty narastały już od lat.|acknowledgement-lag
The practice had been spreading well before it acquired a name.|Ця практика поширювалася ще задовго до того, як отримала назву.|Separate the onset of a practice from its later naming.|Ta praktyka rozpowszechniała się na długo przed tym, jak otrzymała nazwę.|phenomenon-before-label
Ever since the exception was introduced officials had been stretching its scope.|Увесь час від запровадження винятку посадовці розширювали його межі.|Put the initiating exception first and emphasise an uninterrupted tendency from then onwards.|Przez cały czas od wprowadzenia wyjątku urzędnicy rozszerzali jego zakres.|fronted-ever-since
For much of the period in question, the two processes had been developing independently.|Протягом значної частини розглянутого періоду ці два процеси розвивалися незалежно.|Put a restricted portion of the reviewed period first; do not claim the whole interval.|Przez znaczną część rozpatrywanego okresu te dwa procesy rozwijały się niezależnie.|partial-period-scope
The advisers had been raising objections until shortly before the agreement was signed.|Радники висловлювали заперечення аж до часу незадовго перед підписанням угоди.|Place the endpoint a short time before signature, not at signature itself.|Doradcy zgłaszali zastrzeżenia aż do czasu na krótko przed podpisaniem umowy.|endpoint-before-event
In the intervening years expectations had been rising faster than capacity.|Протягом років між цими подіями очікування зростали швидше, ніж можливості.|Put the years between two previously mentioned events first and compare two rates.|W latach pomiędzy tymi wydarzeniami oczekiwania rosły szybciej niż możliwości.|intervening-period
The model had been drifting since long before the first anomaly was reported.|Модель поступово відхилялася від початкового стану вже задовго до повідомлення про першу аномалію.|Date the start of drift substantially before the first reported anomaly.|Model stopniowo odbiegał od stanu początkowego już na długo przed zgłoszeniem pierwszej anomalii.|start-before-report
Up to the point of disclosure the agency had been presenting estimates as facts.|Аж до моменту розкриття агентство подавало оцінки як факти.|Put the disclosure endpoint first for a prior misleading presentation practice.|Aż do chwili ujawnienia agencja przedstawiała szacunki jako fakty.|fronted-up-to-point
The debate had been polarising throughout the years leading up to the referendum.|Дебати поляризувалися впродовж усіх років, що передували референдуму.|Define the entire pre-referendum interval as the period of growing polarisation.|Debata polaryzowała się przez wszystkie lata poprzedzające referendum.|throughout-leading-up
Long before the review began we had been applying the wrong definition.|Задовго до початку перевірки ми вже застосовували неправильне визначення.|Put the distant pre-review time first and describe an erroneous practice already underway.|Na długo przed rozpoczęciem przeglądu stosowaliśmy już błędną definicję.|fronted-long-before
The pattern had been recurring for years rather than only since the latest change.|Ця закономірність повторювалася роками, а не лише від останньої зміни.|Contrast a long measured duration with a falsely recent starting point.|Ten schemat powtarzał się od lat, a nie tylko od ostatniej zmiany.|for-versus-since-claim
By then the gap had been widening for twice as long as we had assumed.|На той час розрив збільшувався вже вдвічі довше, ніж ми припускали.|Put the past reference first and express a duration double the one previously assumed.|Do tego czasu luka powiększała się już dwa razy dłużej, niż zakładaliśmy.|relative-duration
ROWS),
        ],
        'Questions' => [
            'A1' => ppcQualityBuilderRows(<<<'ROWS'
Had you been working before dinner started?|Ти вже деякий час працював перед тим, як почалася вечеря?|Ask whether your listener spent time working before dinner began.|Czy pracowałeś już od pewnego czasu, zanim zaczęła się kolacja?|yes-no
Had she been waiting for you before breakfast?|Вона чекала на тебе перед сніданком?|Ask whether your listener was the person she was waiting for before breakfast.|Czy czekała na ciebie przed śniadaniem?|question-recipient
Had they been studying before the teacher came in?|Вони вже деякий час навчалися перед тим, як зайшов учитель?|Ask whether studying was their earlier activity before the teacher entered.|Czy uczyli się już od pewnego czasu, zanim wszedł nauczyciel?|plural-inversion
What had he been carrying before the bus stopped?|Що він ніс перед тим, як автобус зупинився?|Ask for the object he was carrying before a bus stopped.|Co niósł, zanim autobus się zatrzymał?|what-carried-object
How long had we been walking before the break?|Як довго ми йшли перед перервою?|Ask about the duration of our walk before a break.|Jak długo szliśmy przed przerwą?|how-long
Yes, I had.|Дай коротку ствердну відповідь від свого імені на запитання, чи ти вже деякий час чекав.|Answer affirmatively as yourself when asked whether you spent time waiting earlier.|Odpowiedz krótko twierdząco we własnym imieniu na pytanie, czy wcześniej przez pewien czas czekałeś.|short-positive-i
Had Maria been making notes before the lesson?|Марія робила нотатки перед уроком?|Ask whether Maria's activity before class involved making notes.|Czy Maria robiła notatki przed lekcją?|named-subject
Where had they been living before the move?|Де вони жили до переїзду?|Ask about their temporary home before the move.|Gdzie mieszkali przed przeprowadzką?|where-location
How long had he been living with us before the move?|Як довго він жив у нас до переїзду?|Ask for the duration of his stay with us ending at a move.|Jak długo mieszkał u nas przed przeprowadzką?|duration-of-arrangement
Had you been looking for your bag before I called?|Ти шукав свою сумку перед тим, як я зателефонував?|Ask whether your listener was searching for their own bag before your call.|Czy szukałeś swojej torby, zanim zadzwoniłem?|possessive-role
No, they had not.|Дай коротку заперечну відповідь про них на запитання, чи вони раніше тренувалися.|Answer negatively about them when asked whether they spent time training earlier.|Odpowiedz krótko przecząco o nich na pytanie, czy wcześniej trenowali.|short-negative-they
Had the dog been barking before you woke up?|Собака гавкав перед тим, як ти прокинувся?|Ask whether the dog's barking preceded your listener's waking.|Czy pies szczekał, zanim się obudziłeś?|animal-subject
ROWS),
            'A2' => ppcQualityBuilderRows(<<<'ROWS'
Had Emma been practising the piano all evening?|Емма грала на піаніно весь попередній вечір?|Ask whether Emma spent the whole previous evening practising piano.|Czy Emma ćwiczyła na pianinie przez cały poprzedni wieczór?|whole-period-question
Why had Tom been running before school?|Чому Том бігав перед школою?|Ask for the reason for Tom's earlier running before school.|Dlaczego Tom biegał przed szkołą?|why-reason
Who had been using my cup before I arrived?|Хто користувався моєю чашкою перед тим, як я прийшов?|Ask who was responsible for earlier use of your cup.|Kto korzystał z mojego kubka, zanim przyszedłem?|who-subject
What had the children been watching before bed?|Що діти дивилися перед сном?|Ask for the object of the children's earlier viewing.|Co dzieci oglądały przed snem?|what-object
Had the neighbours been moving furniture when you heard the noise?|Коли ти почув шум, сусіди вже деякий час пересували меблі?|Ask whether furniture moving explains the noise your listener heard.|Czy kiedy usłyszałeś hałas, sąsiedzi już od pewnego czasu przesuwali meble?|evidence-question
Yes, she had.|Дай коротку ствердну відповідь про Емму на запитання, чи вона раніше репетирувала.|Answer affirmatively about Emma's earlier practice using a pronoun.|Odpowiedz krótko twierdząco za pomocą zaimka na pytanie, czy Emma wcześniej ćwiczyła.|short-positive-she
How long had your parents been staying with you before they left?|Як довго батьки гостювали в тебе перед від'їздом?|Ask your listener how long their parents' visit lasted before departure.|Jak długo rodzice gościli u ciebie przed wyjazdem?|visitor-duration
Where had we been waiting before the taxi came?|Де ми чекали перед тим, як приїхало таксі?|Ask for the location of our wait before the taxi arrived.|Gdzie czekaliśmy, zanim przyjechała taksówka?|past-location
Had he been sleeping during the journey?|Він спав під час подорожі?|Ask whether he spent part of the earlier journey sleeping.|Czy spał podczas podróży?|period-question
Who had you been talking to before lunch?|З ким ти розмовляв перед обідом?|Ask which person your listener was talking to earlier; keep the preposition at the end of the verb group.|Z kim rozmawiałeś przed obiadem?|who-object-preposition
No, we had not.|Дай коротку заперечну відповідь від імені вашої групи на запитання, чи ви раніше працювали разом.|Answer negatively on behalf of your group about earlier work together.|Odpowiedz krótko przecząco w imieniu swojej grupy na pytanie, czy wcześniej pracowaliście razem.|short-negative-we
Had it been raining before the picnic?|Дощ ішов перед пікніком?|Ask about rain during the period before the picnic.|Czy przed piknikiem padało?|weather-question
ROWS),
            'B1' => ppcQualityBuilderRows(<<<'ROWS'
What had the mechanic been checking before the engine stopped?|Що механік перевіряв перед тим, як двигун зупинився?|Ask for the object of the mechanic's checks preceding an engine failure.|Co mechanik sprawdzał, zanim silnik się zatrzymał?|technical-object
Why had she not been answering our messages?|Чому вона не відповідала на наші повідомлення?|Ask for the reason for her earlier repeated lack of replies.|Dlaczego nie odpowiadała na nasze wiadomości?|negative-why
How long had the river been rising when the warning came?|Як довго рівень води в річці вже підвищувався, коли надійшло попередження?|Ask how long an upward river trend preceded the warning.|Jak długo poziom wody w rzece już się podnosił, kiedy nadeszło ostrzeżenie?|trend-duration
Who had been looking after the children while you worked?|Хто доглядав за дітьми, поки ти працював?|Ask for the person responsible for earlier childcare while your listener worked.|Kto opiekował się dziećmi, kiedy pracowałeś?|subject-phrasal
Had they been using the old software until the update?|Вони користувалися старим програмним забезпеченням до оновлення?|Ask whether their use of the old software continued up to the update.|Czy korzystali ze starego oprogramowania aż do aktualizacji?|end-boundary
Yes, it had.|Комп'ютер працював без перерв від запуску до збою. Дай коротку відповідь на запитання, чи він до збою вже деякий час працював.|The computer ran without interruption from startup to failure. Give a short answer when asked whether it was already operating for a period before the failure.|Komputer działał bez przerwy od uruchomienia do awarii. Odpowiedz krótko na pytanie, czy przed awarią pracował już od pewnego czasu.|short-positive-it-evidence
How often had she been visiting the centre before it closed?|Як часто вона відвідувала центр до його закриття?|Ask for the frequency of her earlier visits before the centre closed.|Jak często odwiedzała ośrodek przed jego zamknięciem?|frequency
What had you been working on when the power failed?|Над чим ти працював перед тим, як зникло живлення?|Ask about the project your listener was working on before a power failure.|Nad czym pracowałeś przed awarią zasilania?|stranded-on
Had the team not been preparing for the match?|Хіба команда не готувалася до матчу?|Ask a negative confirmation question about the team's earlier preparation.|Czyżby zespół nie przygotowywał się do meczu?|negative-confirmation
Where had the students been staying during the exchange?|Де студенти жили під час обміну?|Ask for the students' temporary accommodation during an earlier exchange.|Gdzie studenci mieszkali podczas wymiany?|temporary-stay-question
No, he had not.|Водій увесь час їхав повільніше за дозволену швидкість. Дай коротку відповідь на запитання, чи він до аварії перевищував швидкість.|The male driver stayed below the speed limit throughout. Give a short answer when asked whether he was speeding before the accident.|Kierowca przez cały czas jechał poniżej dozwolonej prędkości. Odpowiedz krótko na pytanie, czy przed wypadkiem przekraczał prędkość.|short-negative-he-inference
How many people had been waiting outside when you arrived?|Скільки людей чекало надворі перед твоїм приходом?|Ask for the number of people in the earlier waiting group.|Ile osób czekało na dworze przed twoim przyjściem?|quantity-subject
ROWS),
            'B2' => ppcQualityBuilderRows(<<<'ROWS'
Why had the figures been changing before the report was published?|Чому цифри змінювалися перед публікацією звіту?|Ask for an explanation of a prior changing trend in the figures.|Dlaczego liczby zmieniały się przed publikacją raportu?|trend-explanation
How long had the team been considering the merger before it was rejected?|Як довго команда обмірковувала злиття перед тим, як його відхилили?|Ask about the duration of deliberation before rejection of a merger.|Jak długo zespół rozważał fuzję przed jej odrzuceniem?|deliberation-duration
Who had the editor been consulting before revising the article?|З ким редактор консультувався перед переробкою статті?|Ask which person the editor consulted repeatedly before a revision.|Z kim redaktor konsultował się przed przeredagowaniem artykułu?|consulted-object
Who had been advising the editor before the mistake was discovered?|Хто консультував редактора перед виявленням помилки?|Ask for the adviser as the subject of an earlier activity.|Kto doradzał redaktorowi przed wykryciem błędu?|adviser-subject
Had we not been comparing different versions of the document?|Хіба ми порівнювали не різні версії документа?|Ask for negative confirmation of a possible mismatch between document versions.|Czy nie porównywaliśmy różnych wersji dokumentu?|negative-mismatch
Yes, they had.|Журнал збору даних містить щоденні записи тієї самої групи дослідників за весь попередній місяць. Дай коротку відповідь на запитання, чи ці дослідники весь той місяць збирали дані.|The data-collection log contains daily entries from the same research group throughout the preceding month. Answer briefly whether that group was collecting data during the whole month.|Dziennik zbierania danych zawiera codzienne wpisy tej samej grupy badaczy z całego poprzedniego miesiąca. Odpowiedz krótko na pytanie, czy ci badacze przez cały ten miesiąc zbierali dane.|short-positive-they-record-inference
What had she been trying to achieve before the strategy changed?|Чого вона намагалася досягти перед зміною стратегії?|Ask about the goal of sustained attempts before a strategy change.|Co próbowała osiągnąć przed zmianą strategii?|intended-goal
How closely had they been monitoring the leak before the repairs?|Наскільки уважно вони стежили за витоком перед ремонтом?|Ask about the degree of care in earlier leak monitoring.|Jak uważnie obserwowali wyciek przed naprawą?|degree-manner
Which proposal had the committee been discussing when the chair returned?|Яку пропозицію комітет уже обговорював, коли повернувся голова?|Ask which of the proposals was the object of the preceding discussion.|Którą propozycję komitet już omawiał, kiedy wrócił przewodniczący?|which-object
I wondered why he had been avoiding the issue.|Я замислився, чому він весь цей час уникав цього питання.|Report my earlier wonder about his avoidance; use an embedded question, not direct inversion.|Zastanawiałem się, dlaczego przez cały ten czas unikał tego tematu.|embedded-why
No, I had not.|Перед рішенням я перевіряв чутки за офіційними документами. Відповідай від свого імені на запитання, чи ти весь цей час покладався лише на чутки.|Before the decision I checked rumours against official documents. Answer as yourself when asked whether rumours were your sole basis throughout that earlier period.|Przed decyzją sprawdzałem plotki na podstawie oficjalnych dokumentów. Odpowiedz we własnym imieniu na pytanie, czy przez cały ten czas polegałeś wyłącznie na plotkach.|short-negative-i-scope-inference
How much attention had the staff been paying to the warnings?|Скільки уваги працівники приділяли попередженням?|Ask about the amount of attention given to prior warnings.|Ile uwagi pracownicy poświęcali ostrzeżeniom?|amount-object
ROWS),
            'C1' => ppcQualityBuilderRows(<<<'ROWS'
The auditor asked how long managers had been withholding the records.|Аудитор запитав, як довго керівники приховували записи.|Report the auditor's duration question about earlier withholding of records.|Audytor zapytał, jak długo menedżerowie ukrywali dokumenty.|reported-duration
To whom had the witness been speaking before the call ended?|Постав прийменник перед питальним словом. З ким свідок розмовляв перед завершенням дзвінка?|Ask formally about the witness's conversation partner, fronting the preposition.|Umieść przyimek przed zaimkiem pytającym. Z kim świadek rozmawiał przed zakończeniem połączenia?|fronted-preposition
Why had the team not been questioning its assumptions sooner?|Чому команда не ставила під сумнів свої припущення раніше?|Ask why critical examination of assumptions was absent earlier.|Dlaczego zespół nie kwestionował swoich założeń wcześniej?|negative-retrospective
Who had been influencing the decision without attending the meetings?|Хто впливав на рішення, не відвідуючи засідань?|Ask for an unseen influencing participant as the subject of the earlier activity.|Kto wpływał na decyzję, nie uczestnicząc w spotkaniach?|subject-participation
What evidence had the researchers been relying on before the finding was challenged?|На які докази дослідники покладалися до того, як висновок оскаржили?|Ask for the evidence supporting earlier research before a challenge.|Na jakich dowodach polegali badacze przed zakwestionowaniem ustalenia?|evidence-basis
She asked whether we had been interpreting the clause too narrowly.|Вона запитала, чи ми весь цей час тлумачили положення надто вузько.|Report her yes-no question about an overly narrow earlier interpretation.|Zapytała, czy przez cały ten czas interpretowaliśmy zapis zbyt wąsko.|reported-whether
How consistently had the policy been working across different regions?|Наскільки послідовно ця політика давала результат у різних регіонах?|Ask about consistency of earlier results across regions.|Jak konsekwentnie ta polityka przynosiła efekty w różnych regionach?|cross-region-consistency
Had the advisers not been warning us of precisely this outcome?|Хіба радники не попереджали нас саме про такий наслідок?|Ask a negative confirmation question recalling repeated earlier warnings about this exact outcome.|Czy doradcy nie ostrzegali nas właśnie przed takim skutkiem?|recalled-warning
For whose benefit had the agency been withholding the information?|В інтересах кого агентство приховувало інформацію?|Ask formally whose interests benefited from earlier withholding; begin with the prepositional phrase.|Dla czyjej korzyści agencja ukrywała informacje?|possessive-preposition
I wanted to know what they had been doing differently.|Я хотів знати, що вони весь цей час робили інакше.|Embed an earlier activity question within my desire to know.|Chciałem wiedzieć, co przez cały ten czas robili inaczej.|embedded-object
Yes, we had.|До висновку ми порівнювали дві гіпотези й відхилили одну як слабко обґрунтовану. Від імені групи дай коротку відповідь на здивоване заперечне запитання про те, чи ви раніше не розглядали альтернативних пояснень.|Before the conclusion our group compared two hypotheses and rejected one as weakly supported. Answer for the group to the surprised negative question asking whether you were not considering alternative explanations earlier.|Przed wyciągnięciem wniosku porównywaliśmy dwie hipotezy i odrzuciliśmy jedną jako słabo uzasadnioną. Odpowiedz w imieniu grupy na zdziwione pytanie przeczące o to, czy wcześniej nie rozważaliście alternatywnych wyjaśnień.|short-positive-we-negative-question-inference
How far had public confidence been declining before the announcement?|Наскільки довіра громадськості вже знизилася до оголошення?|Ask about the extent of a prior decline in public confidence.|Jak bardzo zaufanie społeczne już spadło przed ogłoszeniem?|extent-question
ROWS),
            'C2' => ppcQualityBuilderRows(<<<'ROWS'
The inquiry asked whose interests the officials had been serving.|Розслідування порушило питання, чиїм інтересам посадовці весь цей час служили.|Report an inquiry's question about the beneficiaries of officials' earlier conduct.|W dochodzeniu zapytano, czyim interesom urzędnicy przez cały ten czas służyli.|reported-beneficiary
On what grounds had the panel been excluding those cases?|На яких підставах комісія відхиляла ті випадки?|Ask formally for the grounds of repeated exclusions, fronting the preposition.|Na jakiej podstawie komisja wykluczała te przypadki?|grounds-preposition
What exactly had we been mistaking for independent evidence?|Що саме ми весь цей час помилково вважали незалежними доказами?|Ask precisely what was wrongly treated as independent evidence.|Co dokładnie przez cały ten czas braliśmy za niezależne dowody?|precise-misidentification
Who had been shaping the narrative before the facts became public?|Хто формував цей виклад подій до того, як факти стали публічними?|Ask for the agent of earlier narrative shaping, not its audience.|Kto kształtował tę narrację, zanim fakty stały się publiczne?|narrative-agent
Had they not been distinguishing uncertainty from ignorance all along?|Хіба вони не розрізняли невизначеність і необізнаність увесь цей час?|Ask for negative confirmation of a sustained conceptual distinction.|Czy nie odróżniali niepewności od niewiedzy przez cały ten czas?|conceptual-negative-question
She questioned whether the safeguards had been protecting everyone equally.|Вона поставила під сумнів, чи запобіжні заходи однаково захищали всіх.|Embed doubt about equal earlier protection within her questioning.|Podała w wątpliwość, czy zabezpieczenia chroniły wszystkich jednakowo.|embedded-scope
To what extent had the apparent consensus been masking disagreement?|Наскільки видима одностайність приховувала незгоду?|Ask formally about the extent of concealment behind apparent consensus.|W jakim stopniu pozorna jednomyślność maskowała niezgodę?|formal-extent
Which consequences had the negotiators been discounting rather than denying?|Які наслідки переговорники применшували, а не заперечували?|Ask which consequences were downplayed rather than denied in earlier negotiations.|Które konsekwencje negocjatorzy bagatelizowali, zamiast im zaprzeczać?|stance-object
Why had no one been asking whether the comparison was valid?|Чому ніхто не ставив питання, чи порівняння було коректним?|Combine a direct why question with an embedded validity question and a negative subject.|Dlaczego nikt nie pytał, czy porównanie było zasadne?|nested-question
The chair wondered who had been speaking on behalf of the absent members.|Голова замислився, хто виступав від імені відсутніх членів.|Report a subject question about an earlier representative speaker.|Przewodniczący zastanawiał się, kto przemawiał w imieniu nieobecnych członków.|reported-subject-question
No, it had not.|Регулювання обмежувало зростання витрат лише в одному місяці; в інших одинадцяти витрати зростали без обмежень. Дай коротку відповідь на запитання, чи регулювання впродовж того року послідовно стримувало зростання витрат.|The regulation limited cost growth in only one month; in the other eleven growth was unrestricted. Give a short answer about whether the regulation was consistently restraining cost growth during that year.|Regulacja ograniczała wzrost kosztów tylko w jednym miesiącu; w pozostałych jedenastu koszty rosły bez ograniczeń. Odpowiedz krótko na pytanie, czy przez ten rok regulacja konsekwentnie ograniczała wzrost kosztów.|short-negative-it-consistency-scope
How long had we been assuming that silence implied agreement?|Як довго ми весь цей час припускали, що мовчання означає згоду?|Ask for the duration of an earlier assumption containing an embedded proposition.|Jak długo przez cały ten czas zakładaliśmy, że milczenie oznacza zgodę?|duration-of-assumption
ROWS),
        ],
        'Forms' => [
            'A1' => ppcQualityBuilderRows(<<<'ROWS'
I had been swimming so my hair was wet.|Я перед цим плавав, тому моє волосся було мокрим.|State my earlier swimming first, then connect it to my wet hair as the consequence.|Wcześniej pływałem, więc moje włosy były mokre.|process-to-evidence
They had been waiting since morning when the shop opened.|Коли магазин відчинився, вони чекали вже з ранку.|Describe their wait from the morning up to the opening of the shop.|Kiedy sklep otwarto, czekali już od rana.|starting-point
She had been reading to the baby when I arrived.|Коли я прийшов, вона вже деякий час читала немовляті.|Describe her earlier reading addressed to a baby, up to my arrival.|Kiedy przyszedłem, już od pewnego czasu czytała niemowlęciu.|recipient-before-reference
We had been walking for an hour before we rested.|Ми йшли вже годину, перш ніж відпочили.|Describe our hour-long walk followed by a rest.|Szliśmy już od godziny, zanim odpoczęliśmy.|earlier-duration
The children had been playing outside before it rained.|Діти гралися надворі перед тим, як пішов дощ.|Describe the children's outdoor play before the rain began.|Dzieci bawiły się na dworze, zanim zaczął padać deszcz.|earlier-activity
He was tired because he had been running.|Він був утомлений, бо перед цим бігав.|Explain his tiredness using an earlier period of running.|Był zmęczony, bo wcześniej biegał.|visible-cause
My hands were dirty because I had been gardening.|Мої руки були брудні, бо перед цим я працював у саду.|Explain why my hands were dirty: I spent time working in the garden.|Moje ręce były brudne, bo wcześniej pracowałem w ogrodzie.|physical-evidence
The dog had been barking before I woke up.|Собака гавкав перед тим, як я прокинувся.|Describe the dog's noisy activity before I woke.|Pies szczekał, zanim się obudziłem.|prior-background
You had been reading all evening before bed.|Ти читав увесь вечір перед сном.|Describe your reading throughout the evening before bedtime.|Czytałeś przez cały wieczór przed snem.|whole-period
It had been snowing when we left home.|Коли ми вийшли з дому, сніг падав уже деякий час.|Describe snowfall already in progress before our departure.|Kiedy wyszliśmy z domu, śnieg padał już od pewnego czasu.|weather-background
Dad had been cooking before we got home.|Тато готував їжу перед тим, як ми повернулися додому.|Describe Dad's cooking activity before we arrived home.|Tata gotował, zanim wróciliśmy do domu.|past-reference
Anna had known Lev for five years by then.|На той час Анна знала Лева вже п'ять років.|Describe Anna's five-year acquaintance with Lev up to that past point; use the state verb know.|W tamtym czasie Anna znała Lwa już od pięciu lat.|state-contrast
ROWS),
            'A2' => ppcQualityBuilderRows(<<<'ROWS'
The bus driver had been driving all night before the break.|Водій автобуса їхав усю ніч перед перервою.|Describe a bus driver's night-long driving period before a break.|Kierowca autobusu prowadził przez całą noc przed przerwą.|extended-period
Emma had been practising the song when her friend arrived.|Коли прийшла її подруга, Емма вже деякий час репетирувала пісню.|Describe Emma's ongoing song practice before her friend's arrival.|Kiedy przyszła jej koleżanka, Emma już od pewnego czasu ćwiczyła piosenkę.|practice-background
We had been looking for the cat before we heard it.|Ми шукали кота перед тим, як почули його.|Describe our search for the cat before its sound revealed it.|Szukaliśmy kota, zanim go usłyszeliśmy.|search-process
Tom had been saving money for a bike before his birthday.|Том відкладав гроші на велосипед ще до свого дня народження.|Describe Tom's ongoing savings for a bicycle before his birthday.|Tom odkładał pieniądze na rower jeszcze przed urodzinami.|unfinished-goal
The floor was wet because someone had been washing it.|Підлога була мокра, бо хтось перед цим її мив.|Explain the wet floor as evidence of an earlier washing activity.|Podłoga była mokra, bo ktoś wcześniej ją mył.|result-evidence
The baby had been crying before her mother picked her up.|Дитина плакала перед тим, як мама взяла її на руки.|Describe a baby girl's crying before her mother lifted her.|Dziewczynka płakała, zanim mama wzięła ją na ręce.|stopped-activity
Our neighbours had been moving boxes all day when we helped them.|Коли ми допомогли сусідам, вони переносили коробки вже цілий день.|Describe our neighbours' day-long box moving before we joined in.|Kiedy pomogliśmy sąsiadom, przenosili pudła już przez cały dzień.|later-intervention
I had been learning English since September when the course ended.|Коли курс завершився, я вивчав англійську вже з вересня.|Describe my English learning from September up to the end of the course.|Kiedy kurs się skończył, uczyłem się angielskiego już od września.|bounded-start
She had been working at home before she rented an office.|Вона працювала вдома перед тим, як орендувала офіс.|Describe her earlier home-working arrangement before renting an office.|Pracowała w domu, zanim wynajęła biuro.|changed-arrangement
They had been feeding the birds every morning before winter.|Вони годували птахів щоранку ще до зими.|Describe their repeated morning bird feeding before winter.|Karmili ptaki każdego ranka jeszcze przed zimą.|repeated-habit
I had wanted a dog for years before we got one.|Я хотів собаку багато років, перш ніж ми завели його.|Describe my long-standing wish for a dog before we finally got one; want expresses a state.|Chciałem mieć psa od lat, zanim go przygarnęliśmy.|desire-state
The class had been drawing while the teacher checked the books.|Клас уже деякий час малював, поки вчитель перевіряв зошити.|Describe the class's drawing already underway during the teacher's checking of exercise books.|Klasa już od pewnego czasu rysowała, podczas gdy nauczyciel sprawdzał zeszyty.|overlapping-background
ROWS),
            'B1' => ppcQualityBuilderRows(<<<'ROWS'
The mechanic found the fault after he had been testing the engine.|Механік знайшов несправність після того, як деякий час перевіряв двигун.|Link the mechanic's discovery to a preceding period of engine testing.|Mechanik znalazł usterkę po tym, jak przez pewien czas sprawdzał silnik.|discovery-after-process
I recognised the tune because my sister had been playing it all week.|Я впізнав мелодію, бо сестра грала її весь тиждень.|Explain my recognition of a tune through my sister's repeated playing during the previous week.|Rozpoznałem melodię, bo siostra grała ją przez cały tydzień.|repetition-cause
We had been discussing the route but had not chosen one.|Ми вже деякий час обговорювали маршрут, але ще не обрали його.|Contrast our ongoing route discussion with the absence of a final choice.|Od pewnego czasu omawialiśmy trasę, ale jeszcze żadnej nie wybraliśmy.|process-result-contrast
The passengers had been standing for an hour when seats became available.|Коли з'явилися вільні місця, пасажири стояли вже годину.|Describe the passengers' hour on their feet up to the appearance of available seats.|Kiedy zwolniły się miejsca, pasażerowie stali już od godziny.|duration-to-change
She said that she had been working on the wrong file.|Вона сказала, що весь цей час працювала не з тим файлом.|Report her statement about earlier work on an incorrect file.|Powiedziała, że przez cały ten czas pracowała na niewłaściwym pliku.|reported-perspective
The river had been rising steadily before the road flooded.|Рівень води в річці поступово підвищувався перед тим, як затопило дорогу.|Connect a gradual rise in the river with the later flooding of the road.|Poziom wody w rzece stopniowo się podnosił, zanim droga została zalana.|gradual-cause
I had been trying to call you when my battery died.|Я вже деякий час намагався тобі зателефонувати, коли розрядився телефон.|Describe repeated unsuccessful attempts to call before my phone battery died.|Od pewnego czasu próbowałem do ciebie zadzwonić, kiedy rozładowała się bateria.|unsuccessful-attempts
They had been staying with relatives until their flat was ready.|Вони тимчасово жили в родичів до того моменту, коли їхня квартира була готова.|Describe a temporary stay with relatives ending when the flat became ready.|Tymczasowo mieszkali u krewnych, aż ich mieszkanie było gotowe.|temporary-arrangement
We had been expecting a quiet evening before the guests arrived.|Ми розраховували на спокійний вечір, перш ніж прийшли гості.|Describe our ongoing expectation of a quiet evening before guests changed the situation.|Spodziewaliśmy się spokojnego wieczoru, zanim przyszli goście.|temporary-expectation
The teacher noticed that Leo had been copying his neighbour's answers.|Учитель помітив, що Лео списував відповіді в сусіда.|Report the teacher's discovery of Leo's earlier copying activity.|Nauczyciel zauważył, że Leo przepisywał odpowiedzi od sąsiada.|evidence-in-report
She had owned the shop for years before she sold it.|Вона володіла магазином багато років, перш ніж продала його.|Describe her long ownership ending in a sale; ownership is a state, not an ongoing activity.|Była właścicielką sklepu od lat, zanim go sprzedała.|ownership-state
The runners had been slowing down as the heat increased.|Бігуни поступово знижували темп у міру того, як ставало спекотніше.|Describe the runners' gradual loss of pace alongside increasing heat.|Biegacze stopniowo zwalniali, gdy robiło się coraz goręcej.|parallel-development
ROWS),
            'B2' => ppcQualityBuilderRows(<<<'ROWS'
The trial failed because the team had been measuring the wrong variable.|Випробування провалилося, бо команда весь цей час вимірювала не ту величину.|Explain a failed trial by reference to an ongoing error in what was being measured.|Próba się nie powiodła, bo zespół przez cały ten czas mierzył niewłaściwą wielkość.|retrospective-error
Although she had been rehearsing for weeks, she still forgot a line.|Хоча вона репетирувала вже кілька тижнів, усе ж забула одну репліку.|Contrast weeks of rehearsal with a later forgotten line; begin with the concession.|Chociaż ćwiczyła już od kilku tygodni, i tak zapomniała jednej kwestii.|concession
I realised that we had been talking about different deadlines.|Я зрозумів, що весь цей час ми говорили про різні кінцеві терміни.|Describe a later realisation that the preceding discussion involved different deadlines.|Zrozumiałem, że przez cały ten czas mówiliśmy o różnych terminach.|misunderstanding
The alarm explained the noise we had been hearing all afternoon.|Сигналізація пояснювала шум, який ми чули весь пополудень.|Identify an ongoing, repeated sound in the preceding afternoon as coming from the alarm.|Alarm wyjaśniał hałas, który słyszeliśmy przez całe popołudnie.|repeated-perception
Sales had been falling even before the new competitor appeared.|Продажі знижувалися ще до того, як з'явився новий конкурент.|Show that a downward sales trend predates the new competitor.|Sprzedaż spadała jeszcze zanim pojawił się nowy konkurent.|prior-trend
We had been considering moving abroad but the offer changed our plans.|Ми вже деякий час обмірковували переїзд за кордон, але пропозиція змінила наші плани.|Contrast ongoing consideration of emigration with an offer that redirected our plans.|Od pewnego czasu rozważaliśmy przeprowadzkę za granicę, ale oferta zmieniła nasze plany.|deliberation-not-decision
Her eyes were sore because she had been checking tiny labels.|У неї боліли очі, бо перед цим вона перевіряла дрібні написи на етикетках.|Explain sore eyes through sustained checking of small label print.|Bolały ją oczy, bo wcześniej sprawdzała drobny druk na etykietach.|physical-cause
The editor had been revising the introduction rather than checking the figures.|Редактор увесь цей час переробляв вступ, а не перевіряв цифри.|Distinguish the editor's actual earlier activity from the suggested alternative.|Redaktor przez cały ten czas przerabiał wstęp, zamiast sprawdzać liczby.|activity-contrast
He admitted that he had been using an outdated map.|Він визнав, що весь цей час користувався застарілою картою.|Report his admission about continued use of an obsolete map.|Przyznał, że przez cały ten czas korzystał z nieaktualnej mapy.|admission-backshift
The volunteers had been distributing blankets until supplies ran out.|Волонтери роздавали ковдри, поки запаси не вичерпалися.|Describe distribution as a process interrupted by exhaustion of supplies.|Wolontariusze rozdawali koce, aż skończyły się zapasy.|externally-ended-process
I had believed the estimate until I saw the original figures.|Я вірив цій оцінці до того моменту, коли побачив вихідні цифри.|Describe a belief that lasted until contradictory original figures appeared; use a state form.|Wierzyłem w tę ocenę, dopóki nie zobaczyłem pierwotnych danych.|belief-state
The repairs took longer because water had been leaking into the wall.|Ремонт тривав довше, бо вода перед цим просочувалася в стіну.|Explain extra repair time through a prior ongoing water leak into a wall.|Naprawa trwała dłużej, bo wcześniej woda przesiąkała do ściany.|accumulated-damage
ROWS),
            'C1' => ppcQualityBuilderRows(<<<'ROWS'
The witness recalled that the guard had been watching the side entrance.|Свідок згадав, що охоронець перед цим спостерігав за бічним входом.|Report the witness's recollection of an activity preceding the event under investigation.|Świadek przypomniał sobie, że strażnik wcześniej obserwował boczne wejście.|reconstructed-perspective
What looked like a sudden failure had been developing for months.|Те, що здавалося раптовою поломкою, насправді розвивалося місяцями.|Contrast an apparently sudden failure with its long gradual development; begin with what looked like.|To, co wyglądało na nagłą awarię, rozwijało się przez wiele miesięcy.|appearance-process-contrast
The researcher had been treating correlation as evidence of causation.|Дослідник увесь цей час трактував кореляцію як доказ причинного зв'язку.|Describe a sustained analytical mistake, not one completed decision.|Badacz przez cały ten czas traktował korelację jako dowód przyczynowości.|sustained-interpretation
Even though demand had been growing, the factory closed.|Хоча попит зростав, фабрика закрилася.|Express the concession between a growing earlier trend and a later factory closure.|Mimo że popyt rósł, fabryka została zamknięta.|counterexpectation
The manager learnt that staff had been sharing passwords without permission.|Керівник дізнався, що працівники перед цим ділилися паролями без дозволу.|Report discovery of an unauthorised repeated practice among staff.|Kierownik dowiedział się, że pracownicy wcześniej udostępniali sobie hasła bez pozwolenia.|discovered-practice
The committee had been weighing the risks without reaching a decision.|Комітет уже деякий час зважував ризики, але рішення ще не ухвалив.|Describe sustained deliberation that produced no final decision.|Komitet od pewnego czasu rozważał ryzyko, nie podejmując decyzji.|process-without-result
She had been working towards a promotion when the department was dissolved.|Вона вже деякий час докладала зусиль заради підвищення, коли відділ розформували.|Describe an ongoing effort towards promotion cut short by organisational change.|Od pewnego czasu starała się o awans, kiedy dział rozwiązano.|goal-interrupted
The files confirmed what the auditors had been suspecting.|Документи підтвердили те, що аудитори вже деякий час підозрювали.|Describe a developing suspicion later confirmed by documents.|Dokumenty potwierdziły to, co audytorzy od pewnego czasu podejrzewali.|developing-suspicion
The temperature had been fluctuating rather than rising steadily.|Температура перед цим коливалася, а не зростала рівномірно.|Distinguish fluctuations from a steady upward trend.|Temperatura wcześniej się wahała, zamiast stale rosnąć.|trend-precision
I had understood the rule but had been applying it too broadly.|Я розумів правило, але весь цей час застосовував його надто широко.|Contrast prior understanding of a rule with a sustained error in its application.|Rozumiałem zasadę, ale przez cały ten czas stosowałem ją zbyt szeroko.|state-activity-contrast
By the final hearing the parties had been exchanging proposals for a year.|На момент останнього слухання сторони обмінювалися пропозиціями вже рік.|Describe a year's repeated exchanges leading up to the final hearing; put the hearing first.|Do ostatniej rozprawy strony wymieniały propozycje już od roku.|repeated-negotiation
The silence was unusual because the machines had been running continuously.|Тиша була незвичною, бо до того машини працювали безперервно.|Explain why silence seemed unusual after uninterrupted earlier machine operation.|Cisza była niezwykła, bo wcześniej maszyny pracowały bez przerwy.|cessation-evidence
ROWS),
            'C2' => ppcQualityBuilderRows(<<<'ROWS'
The apparent consensus concealed disagreements that had been deepening for years.|Видима одностайність приховувала розбіжності, які поглиблювалися роками.|Contrast the appearance of agreement with a long deterioration concealed beneath it.|Pozorna jednomyślność ukrywała rozbieżności, które pogłębiały się przez lata.|discourse-reinterpretation
She later acknowledged that she had been confusing absence of evidence with evidence of absence.|Згодом вона визнала, що весь цей час плутала відсутність доказів із доказом відсутності.|Report a later admission of a sustained reasoning error involving two distinct concepts.|Później przyznała, że przez cały ten czas myliła brak dowodów z dowodem braku.|conceptual-distinction
The inquiry established that pressure had been building long before anyone complained.|Розслідування встановило, що тиск наростав задовго до того, як хтось поскаржився.|Place the onset of an accumulating problem well before the first complaint in the inquiry's findings.|Dochodzenie ustaliło, że presja narastała na długo przed pierwszą skargą.|onset-versus-report
Although the symptoms had subsided, the patient had been deteriorating in other respects.|Хоча симптоми ослабли, в інших аспектах стан пацієнта погіршувався.|Contrast a completed reduction in symptoms with an earlier continuing decline in other respects.|Chociaż objawy ustąpiły, stan pacjenta pogarszał się pod innymi względami.|result-versus-trend
What the public saw as indecision was a compromise the negotiators had been refining.|Те, що громадськість сприймала як нерішучість, було компромісом, який переговорники весь цей час удосконалювали.|Reinterpret apparent indecision as ongoing refinement of a compromise.|To, co opinia publiczna uznawała za niezdecydowanie, było kompromisem, który negocjatorzy przez cały ten czas dopracowywali.|perspective-reframing
The board discovered that managers had been understating costs while overstating demand.|Рада з'ясувала, що керівники весь цей час занижували витрати й водночас завищували попит.|Report two simultaneous misleading practices with opposite directions.|Rada odkryła, że menedżerowie przez cały ten czas zaniżali koszty, jednocześnie zawyżając popyt.|parallel-contrast
I had accepted the principle but had been questioning its practical consequences.|Я прийняв цей принцип, але весь цей час ставив під сумнів його практичні наслідки.|Distinguish prior acceptance of a principle from continued questioning of its consequences.|Zaakceptowałem tę zasadę, ale przez cały ten czas kwestionowałem jej praktyczne konsekwencje.|acceptance-versus-questioning
The delay mattered because small losses had been accumulating unnoticed.|Затримка мала значення, бо до того дрібні втрати непомітно накопичувалися.|Explain the significance of a delay by an unnoticed cumulative process.|Opóźnienie miało znaczenie, bo wcześniej drobne straty gromadziły się niezauważenie.|cumulative-consequence
Only later did we realise how narrowly we had been defining success.|Лише згодом ми усвідомили, наскільки вузько весь цей час визначали успіх.|Express a delayed realisation with initial only later and an earlier sustained definition of success.|Dopiero później zdaliśmy sobie sprawę, jak wąsko przez cały ten czas definiowaliśmy sukces.|delayed-realisation
The policy had existed for decades but officials had been interpreting it differently.|Ця політика існувала десятиліттями, але посадовці весь цей час тлумачили її по-різному.|Contrast the duration of a policy's existence with varying ongoing interpretations.|Ta polityka istniała od dziesięcioleci, ale urzędnicy przez cały ten czas różnie ją interpretowali.|existence-versus-practice
The report distinguished what the team had achieved from what it had been attempting.|Звіт розрізняв те, чого команда досягла, і те, чого вона весь цей час намагалася досягти.|Separate completed achievements from sustained attempts within one retrospective report.|Raport odróżniał to, co zespół osiągnął, od tego, co przez cały ten czas próbował osiągnąć.|achievement-versus-attempt
The assurances sounded hollow because confidence had been eroding behind the scenes.|Запевнення звучали непереконливо, бо за лаштунками довіра весь цей час слабшала.|Explain the mismatch between public assurances and a hidden gradual loss of confidence.|Zapewnienia brzmiały pusto, bo za kulisami zaufanie przez cały ten czas słabło.|public-hidden-contrast
ROWS),
        ],
        'Negatives' => [
            'A1' => ppcQualityBuilderRows(<<<'ROWS'
I had not been working there long before the shop closed.|Я працював там недовго, перш ніж магазин закрився.|Say that my time working there before the shop closed was short, not that I never worked there.|Pracowałem tam niedługo, zanim sklep zamknięto.|not-long
They had not been waiting long when the doors opened.|Коли двері відчинилися, вони чекали ще недовго.|Describe a short wait up to the opening of the doors using a negative duration.|Kiedy drzwi się otworzyły, czekali jeszcze niedługo.|short-wait
She had not been studying before the teacher arrived.|Вона не навчалася перед тим, як прийшов учитель.|Deny that studying was her activity before the teacher's arrival.|Nie uczyła się, zanim przyszedł nauczyciel.|denied-activity
We had not been sleeping well before the trip.|Ми погано спали перед поїздкою.|Describe poor sleep before the trip using not and well, rather than denying all sleep.|Źle sypialiśmy przed podróżą.|not-well
He had not been eating enough before he felt weak.|Він їв недостатньо перед тим, як відчув слабкість.|Describe insufficient eating before his weakness, not an absence of eating.|Jadł za mało, zanim poczuł osłabienie.|not-enough
The children had not been playing outside before lunch.|Діти не гралися надворі перед обідом.|Deny outdoor play as the children's activity before lunch.|Dzieci nie bawiły się na dworze przed obiadem.|negative-background
I had not been using my phone before the accident.|Я не користувався телефоном перед аварією.|Deny phone use during the period preceding the accident.|Nie korzystałem z telefonu przed wypadkiem.|denied-cause
You had not been listening when I explained the rule.|Ти не слухав, коли я пояснював правило.|Describe your lack of listening during my earlier explanation.|Nie słuchałeś, kiedy wyjaśniałem zasadę.|overlapping-negation
It had not been raining before we left.|Перед тим, як ми вийшли, дощ не йшов.|Deny rain during the period before our departure.|Zanim wyszliśmy, nie padało.|weather-negation
Dad had not been cooking before we came home.|Тато не готував їжу перед тим, як ми повернулися додому.|Deny cooking as Dad's earlier activity before our return.|Tata nie gotował, zanim wróciliśmy do domu.|simple-negation
The dog had not been barking before the baby woke.|Собака не гавкав перед тим, як дитина прокинулася.|Deny barking as an activity preceding the baby's waking.|Pies nie szczekał, zanim dziecko się obudziło.|excluded-cause
She had not been reading for long when the light went out.|Коли світло згасло, вона читала ще недовго.|Describe a reading period that was short at the moment the light failed.|Kiedy zgasło światło, czytała jeszcze niedługo.|for-long
ROWS),
            'A2' => ppcQualityBuilderRows(<<<'ROWS'
Emma had not been practising regularly before the concert.|Перед концертом Емма репетирувала нерегулярно.|Negate regularity of Emma's practice before the concert, not practice itself.|Przed koncertem Emma ćwiczyła nieregularnie.|not-regularly
The driver had not been driving fast before the crash.|Перед зіткненням водій їхав не швидко.|Deny high speed in the driver's earlier driving.|Przed zderzeniem kierowca nie jechał szybko.|not-fast
We had not been living near the school before we moved.|До переїзду ми жили не біля школи.|Negate the location of our earlier residence.|Przed przeprowadzką nie mieszkaliśmy blisko szkoły.|location-negation
Tom had not been looking after the plants while we were away.|Том не доглядав за рослинами, поки нас не було.|Deny plant care during our absence; look after means care for.|Tom nie opiekował się roślinami podczas naszej nieobecności.|phrasal-care
I had not been saving much before I got a better job.|Я відкладав небагато грошей, перш ніж знайшов кращу роботу.|Negate the amount saved before my job improved, not all saving.|Odkładałem niewiele, zanim dostałem lepszą pracę.|not-much
The neighbours had not been making noise before midnight.|Сусіди не шуміли до півночі.|Deny noise-making in the period before midnight.|Sąsiedzi nie hałasowali przed północą.|time-bounded-denial
She had not been feeling well before the flight.|Перед польотом вона почувалася недобре.|Describe poor health before her flight through not and well.|Przed lotem nie czuła się dobrze.|health-quality
They had not been training together before the race.|Перед забігом вони тренувалися не разом.|Deny joint training, not training by each runner.|Przed biegiem nie trenowali razem.|not-together
We had not been waiting for the bus when you saw us.|Ми чекали не на автобус, коли ти нас побачив.|Reject the bus as the object of our wait when you saw us.|Nie czekaliśmy na autobus, kiedy nas zobaczyłeś.|object-scope
He had not been cleaning his room before his parents arrived.|Він не прибирав у своїй кімнаті перед приїздом батьків.|Deny room cleaning as his earlier activity.|Nie sprzątał pokoju przed przyjazdem rodziców.|specific-activity
I had not been working all day before your call.|До твого дзвінка я працював не весь день.|Negate a whole-day duration; do not say that I did no work.|Do twojego telefonu nie pracowałem przez cały dzień.|not-all-day
The children had not been talking loudly before the lesson.|Перед уроком діти розмовляли не голосно.|Negate loudness of earlier conversation, not conversation itself.|Przed lekcją dzieci nie rozmawiały głośno.|manner-scope
ROWS),
            'B1' => ppcQualityBuilderRows(<<<'ROWS'
The mechanic had not been checking the brakes before the test drive.|Перед пробною поїздкою механік перевіряв не гальма.|Reject brakes as the object of the mechanic's earlier checks.|Przed jazdą próbną mechanik nie sprawdzał hamulców.|wrong-object
We had not been preparing long enough when the deadline arrived.|Коли настав кінцевий термін, ми готувалися ще недостатньо довго.|Negate sufficient preparation time at the deadline, not the existence of preparation.|Kiedy nadszedł termin, nie przygotowywaliśmy się jeszcze wystarczająco długo.|insufficient-duration
She explained that she had not been ignoring our messages.|Вона пояснила, що не ігнорувала наші повідомлення.|Report her denial of earlier deliberate neglect of our messages.|Wyjaśniła, że nie ignorowała naszych wiadomości.|reported-denial
The staff had not been following the new instructions before the inspection.|До перевірки працівники не дотримувалися нових інструкцій.|Deny the staff's ongoing compliance before an inspection.|Przed kontrolą pracownicy nie stosowali się do nowych instrukcji.|noncompliance
I had not been sleeping badly until the noisy work began.|Я не спав погано до того моменту, коли почалися шумні роботи.|Say that poor sleep was absent before noisy work started; the negative applies to badly.|Nie sypiałem źle, dopóki nie zaczęły się hałaśliwe prace.|double-evaluation
They had not been arguing about money when we interrupted.|Вони сперечалися не про гроші, коли ми їх перебили.|Reject money as the topic of the earlier argument.|Nie kłócili się o pieniądze, kiedy im przerwaliśmy.|topic-scope
The computer had not been running smoothly before it stopped.|Перед тим, як комп'ютер зупинився, він працював зі збоями.|Negate smooth operation in the period preceding a computer shutdown.|Zanim komputer się zatrzymał, nie działał płynnie.|not-smoothly
We had not been staying at a hotel before we found the flat.|Ми жили не в готелі, перш ніж знайшли квартиру.|Reject a hotel as our temporary accommodation before finding a flat.|Nie mieszkaliśmy w hotelu, zanim znaleźliśmy mieszkanie.|temporary-location
He had not been using the correct address before the parcel returned.|До повернення посилки він користувався не правильною адресою.|Describe continued use of an incorrect address before a parcel was returned.|Przed zwrotem paczki nie korzystał z właściwego adresu.|incorrect-input
The band had not been rehearsing for weeks before the show.|Перед виступом гурт репетирував не кілька тижнів.|Reject the claim of weeks of rehearsal, without denying that rehearsal occurred.|Przed występem zespół nie ćwiczył przez całe tygodnie.|duration-claim
I had not been paying attention so I missed the announcement.|Я не стежив уважно за тим, що відбувалося, тож пропустив оголошення.|Connect my earlier lack of attention to a missed announcement.|Nie zwracałem uwagi, więc przegapiłem ogłoszenie.|negative-cause
She had not been expecting visitors before the doorbell rang.|Вона не очікувала відвідувачів перед тим, як задзвонили у двері.|Describe the absence of an ongoing expectation before an unexpected doorbell.|Nie spodziewała się gości, zanim zadzwonił dzwonek.|absent-expectation
ROWS),
            'B2' => ppcQualityBuilderRows(<<<'ROWS'
The team had not been testing real users before the product failed.|Команда тестувала продукт не на реальних користувачах перед тим, як він зазнав невдачі.|Reject real users as the subjects of the preceding tests.|Zespół nie prowadził testów na rzeczywistych użytkownikach, zanim produkt poniósł porażkę.|population-scope
Although he had not been working there long, he understood the problem.|Хоча він працював там недовго, він розумів проблему.|Concede a short work history while affirming his understanding.|Chociaż pracował tam niedługo, rozumiał problem.|short-history-concession
We discovered that the sensor had not been recording continuously.|Ми з'ясували, що датчик записував дані не безперервно.|Report gaps in recording, not an absence of all recordings.|Odkryliśmy, że czujnik nie rejestrował danych bez przerwy.|interrupted-continuity
The contractor had not been allowing for delays when calculating costs.|Обчислюючи витрати, підрядник не враховував можливих затримок.|Negate the contractor's allowance for delays during earlier cost calculation.|Obliczając koszty, wykonawca nie uwzględniał opóźnień.|phrasal-allowance
She had not been avoiding us but had been caring for her father.|Вона не уникала нас, а доглядала за батьком.|Replace an alleged earlier activity with the actual one in a contrast.|Nie unikała nas, lecz opiekowała się ojcem.|corrected-inference
The researchers had not been comparing like with like before the review.|До перевірки дослідники порівнювали не зіставні речі.|Reject comparability of the things used in the earlier analysis.|Przed przeglądem badacze nie porównywali rzeczy porównywalnych.|comparison-quality
I had not been questioning his honesty, only his methods.|Я ставив під сумнів не його чесність, а лише його методи.|Limit the object of my earlier criticism to methods, excluding honesty.|Nie kwestionowałem jego uczciwości, tylko jego metody.|restricted-object
They had not been monitoring the river closely enough before the flood.|Перед повінню вони стежили за річкою недостатньо уважно.|Negate adequate care in earlier monitoring, not monitoring itself.|Przed powodzią nie obserwowali rzeki wystarczająco uważnie.|insufficient-manner
The company had not been losing money consistently before the sale.|Перед продажем компанія зазнавала збитків не постійно.|Reject consistent losses while allowing occasional losses before the sale.|Przed sprzedażą firma nie ponosiła strat stale.|frequency-scope
He insisted that he had not been concealing information from the auditors.|Він наполягав, що не приховував інформації від аудиторів.|Report his emphatic denial of an earlier concealment practice.|Upierał się, że nie ukrywał informacji przed audytorami.|emphatic-reported-denial
We had not been relying solely on one source before the decision.|До рішення ми покладалися не лише на одне джерело.|Reject sole reliance on one source, without denying use of that source.|Przed decyzją nie polegaliśmy wyłącznie na jednym źródle.|not-solely
The medicine had not been helping much before the dose changed.|До зміни дози ліки допомагали мало.|Negate a substantial effect of the earlier medication without denying all benefit.|Przed zmianą dawki lek niewiele pomagał.|limited-benefit
ROWS),
            'C1' => ppcQualityBuilderRows(<<<'ROWS'
The witnesses had not been describing the same incident despite similar wording.|Попри схожі формулювання, свідки описували не ту саму подію.|Reject identity of the incident despite similarities in the witnesses' wording.|Mimo podobnych sformułowań świadkowie nie opisywali tego samego zdarzenia.|referent-contrast
The committee had not been postponing a decision merely for convenience.|Комітет відкладав рішення не просто заради зручності.|Reject convenience as the sole explanation for a sustained delay.|Komitet nie odkładał decyzji jedynie dla wygody.|motive-scope
She clarified that she had not been disputing the facts but their interpretation.|Вона уточнила, що оспорювала не факти, а їхнє тлумачення.|Report a precise distinction between contested facts and contested interpretation.|Wyjaśniła, że nie kwestionowała faktów, lecz ich interpretację.|clarified-object
The figures suggested that demand had not been recovering as expected.|Цифри свідчили, що попит відновлювався не так, як очікували.|Negate recovery in the expected manner, not necessarily every sign of recovery.|Liczby sugerowały, że popyt nie odradzał się zgodnie z oczekiwaniami.|expectation-gap
The staff had not been raising concerns openly for fear of retaliation.|Працівники не висловлювали занепокоєння відкрито через страх помсти.|Explain why earlier concerns were not voiced openly; do not deny private concerns.|Pracownicy nie zgłaszali obaw otwarcie ze strachu przed odwetem.|public-private-scope
We had not been overlooking the issue but had been waiting for reliable evidence.|Ми не нехтували проблемою, а чекали на надійні докази.|Correct an accusation of neglect by identifying the actual earlier activity.|Nie pomijaliśmy problemu, lecz czekaliśmy na wiarygodne dowody.|corrected-accusation
The participants had not been reacting independently of one another.|Учасники реагували не незалежно один від одного.|Reject independence of the earlier reactions, not the reactions themselves.|Uczestnicy nie reagowali niezależnie od siebie.|dependence-scope
He admitted that he had not been examining alternative explanations seriously.|Він визнав, що не розглядав альтернативні пояснення серйозно.|Report a concession about the seriousness of an earlier investigation.|Przyznał, że nie analizował alternatywnych wyjaśnień poważnie.|degree-of-engagement
The system had not been failing randomly but under peak load.|Система давала збої не випадково, а під час пікового навантаження.|Contrast random failures with failures under a specific operating condition.|System nie zawodził losowo, lecz przy szczytowym obciążeniu.|conditional-pattern
The author had not been endorsing the view, merely reporting it.|Автор не підтримував цей погляд, а лише викладав його.|Distinguish reporting a view from endorsing it in an earlier text.|Autor nie popierał tego poglądu, a jedynie go przedstawiał.|stance-distinction
They had not been negotiating in good faith before the talks collapsed.|Перед провалом переговорів вони вели їх не добросовісно.|Negate good faith of the earlier negotiations, not negotiations themselves.|Przed załamaniem rozmów nie negocjowali w dobrej wierze.|quality-of-intent
The safeguards had not been working equally well across all sites.|Запобіжні заходи працювали не однаково добре на всіх об'єктах.|Reject equal effectiveness across sites without claiming universal failure.|Zabezpieczenia nie działały równie dobrze we wszystkich lokalizacjach.|distribution-scope
ROWS),
            'C2' => ppcQualityBuilderRows(<<<'ROWS'
The panel had not been rejecting uncertainty but pretending it was measurable.|Комісія не відкидала невизначеність, а вдавала, що її можна виміряти.|Replace one account of the panel's approach to uncertainty with a more precise account.|Komisja nie odrzucała niepewności, lecz udawała, że można ją zmierzyć.|conceptual-correction
She had not been conceding the argument, only acknowledging its appeal.|Вона не визнавала аргумент правильним, а лише визнавала його привабливість.|Distinguish conceding an argument from recognising its appeal.|Nie przyznawała racji argumentowi, a jedynie uznawała jego atrakcyjność.|concession-versus-recognition
The inquiry found that losses had not been occurring only during emergencies.|Розслідування виявило, що втрати виникали не лише під час надзвичайних ситуацій.|Reject a restriction of losses to emergencies while retaining those instances.|Dochodzenie wykazało, że straty nie występowały wyłącznie podczas sytuacji nadzwyczajnych.|not-only
We had not been treating the exception as a rule until the latest revision.|До останньої редакції ми не трактували виняток як правило.|Locate the absence of an erroneous practice before a revision that changed it.|Nie traktowaliśmy wyjątku jak reguły aż do ostatniej zmiany.|negative-until
The negotiators had not been disagreeing about the goal but about who would bear the cost.|Переговорники сперечалися не про мету, а про те, хто понесе витрати.|Distinguish agreement on a goal from disagreement about allocation of costs.|Negocjatorzy nie spierali się o cel, lecz o to, kto poniesie koszty.|nested-object-contrast
The evidence did not show that the treatment had not been working at all.|Докази не свідчили про те, що лікування зовсім не діяло.|Negate an inference of total ineffectiveness without claiming proven effectiveness.|Dowody nie wskazywały, że leczenie w ogóle nie działało.|negated-inference
The advisers had not been underestimating the risk so much as misidentifying its source.|Радники не так недооцінювали ризик, як неправильно визначали його джерело.|Use a not so much as contrast to refine the diagnosis of an earlier error.|Doradcy nie tyle lekceważyli ryzyko, ile błędnie wskazywali jego źródło.|not-so-much-as
He denied that he had not been giving the proposal serious consideration.|Він заперечив, що не приділяв пропозиції серйозної уваги.|Report his denial of an allegation that he neglected the proposal; preserve both negatives.|Zaprzeczył, że nie poświęcał propozycji poważnej uwagi.|denial-of-negation
The criticism had not been undermining the policy itself, merely the claims made for it.|Критика підривала не саму політику, а лише заяви на її підтримку.|Precisely restrict the target of earlier criticism to claims made for a policy.|Krytyka nie podważała samej polityki, a jedynie twierdzenia formułowane na jej rzecz.|referential-restriction
The records showed that officials had not been applying the exemption uniformly.|Записи показали, що посадовці застосовували виняток не однаково.|Report uneven application of an exemption rather than an absence of application.|Zapisy pokazały, że urzędnicy nie stosowali zwolnienia jednolicie.|uneven-application
We had not been seeking certainty where only a range of estimates was possible.|Ми не прагнули певності там, де був можливий лише діапазон оцінок.|Deny an inappropriate earlier pursuit of certainty in a context admitting only estimates.|Nie dążyliśmy do pewności tam, gdzie możliwy był jedynie zakres szacunków.|epistemic-limit
The team had not been ignoring dissent even though the summary omitted it.|Команда не ігнорувала незгоду, хоча підсумок її не згадував.|Contrast an absence from a summary with a denial of earlier neglect.|Zespół nie ignorował sprzeciwu, chociaż podsumowanie go pomijało.|representation-versus-practice
ROWS),
        ],
    ];
}
