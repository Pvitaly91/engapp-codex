<?php
declare(strict_types=1);

namespace App\Support;

use RuntimeException;

/** Final projection for registered finite builders; authored reorder groups remain untouched. */
final class FiniteBuilderWordSlots
{
    /** Explicit closed grammatical classes; never supply a random noun for an auxiliary. */
    private const WORD_FAMILIES = [
        ['a', 'an', 'the', 'some', 'any', 'this', 'that', 'these', 'those', 'each', 'every', 'no', 'another', 'either', 'neither', 'all', 'both'],
        ['I', 'you', 'he', 'she', 'it', 'we', 'they', 'me', 'him', 'us', 'them', 'myself', 'yourself', 'herself', 'ourselves', 'themselves'],
        ['my', 'your', 'his', 'her', 'its', 'our', 'their', 'mine', 'yours', 'hers', 'ours', 'theirs', 'whose'],
        ['am', 'is', 'are', 'was', 'were', 'be', 'been', 'being', "isn't", "aren't", "wasn't", "weren't"],
        ['have', 'has', 'had', "haven't", "hasn't", "hadn't", 'do', 'does', 'did', "don't", "doesn't", "didn't"],
        ['can', 'could', 'will', 'would', 'shall', 'should', 'may', 'might', 'must', "can't", "couldn't", "won't", "wouldn't", "shouldn't", 'cannot'],
        ['to', 'from', 'for', 'of', 'in', 'on', 'at', 'by', 'with', 'without', 'into', 'onto', 'over', 'under', 'through', 'across', 'between', 'among', 'about', 'during'],
        ['and', 'but', 'or', 'nor', 'so', 'yet', 'because', 'although', 'though', 'unless', 'whereas', 'while', 'if', 'whether'],
        ['not', 'never', 'always', 'often', 'sometimes', 'already', 'still', 'just', 'also', 'usually', 'rarely', 'only', 'even', 'almost'],
        ['who', 'whom', 'what', 'which', 'where', 'when', 'why', 'how', 'whoever', 'whatever', 'wherever', 'whenever'],
        ['Ben', 'Anna', 'Leo', 'Luca', 'Emma', 'Max', 'Mia', 'Nora', 'Sam', 'Tom', 'Alex', 'Olena', 'Mark', 'Iryna', 'Olia', 'Oleh', 'Danylo', 'Sofia', 'Marta', 'Taras', 'Nina', 'Lena', 'Ivan', 'Maria', 'Petro', 'Alice', 'John', 'Mary', 'Jane', 'Paul', 'Kate', 'Iva'],
        ['red', 'blue', 'green', 'black', 'white', 'yellow', 'silver', 'golden', 'brown', 'pink', 'grey', 'gray', 'purple', 'orange'],
        ['English', 'German', 'French', 'Spanish', 'Italian', 'Ukrainian', 'Polish', 'Portuguese', 'Japanese', 'Chinese'],
        ['Italy', 'Canada', 'Ukraine', 'France', 'Germany', 'Poland', 'Spain', 'Japan', 'Brazil', 'Portugal'],
        ['Kyiv', 'London', 'Paris', 'Berlin', 'Rome', 'Madrid', 'Tokyo', 'Prague', 'Warsaw', 'Lviv'],
        ['Danube', 'Dnipro', 'Thames', 'Seine', 'Rhine', 'Nile', 'Amazon', 'Volga'],
        ['bike', 'car', 'bus', 'train', 'tram', 'taxi', 'van', 'bicycle', 'plane', 'boat', 'ship'],
        ['teacher', 'friend', 'manager', 'student', 'doctor', 'artist', 'baker', 'driver', 'neighbour', 'nurse', 'worker', 'visitor'],
        ['dog', 'cat', 'bird', 'horse', 'cow', 'rabbit', 'lion', 'tiger', 'bear', 'monkey', 'duck', 'chicken'],
        ['day', 'week', 'month', 'year', 'hour', 'minute', 'second', 'morning', 'afternoon', 'evening', 'night'],
        ['book', 'letter', 'report', 'document', 'map', 'notebook', 'folder', 'newspaper', 'magazine', 'message', 'card', 'poster'],
        ['milk', 'bread', 'water', 'rice', 'cheese', 'coffee', 'tea', 'juice', 'sugar', 'salt', 'butter', 'honey'],
    ];

    /** Beginner-safe fallbacks, used only if a matching reviewed level/role pool is exhausted. */
    private const LEXICAL_FAMILIES = [
        'noun' => ['bike', 'car', 'bag', 'book', 'box', 'chair', 'table', 'ticket', 'cup', 'door', 'key', 'room', 'map', 'letter', 'window', 'phone', 'train', 'bus', 'desk', 'picture', 'child', 'teacher', 'school', 'sister', 'brother', 'friend', 'dog', 'cat', 'person', 'water', 'bread', 'milk', 'music', 'food'],
        'plural' => ['bikes', 'cars', 'bags', 'books', 'boxes', 'chairs', 'tables', 'tickets', 'cups', 'doors', 'keys', 'rooms', 'maps', 'letters', 'windows', 'phones', 'children', 'teachers', 'schools', 'friends', 'dogs', 'cats', 'people', 'men', 'women'],
        'adjective' => ['small', 'large', 'new', 'old', 'young', 'cold', 'warm', 'quiet', 'hungry', 'thirsty', 'clear', 'safe', 'useful', 'helpful', 'different', 'simple', 'happy', 'sad', 'easy', 'difficult', 'bright', 'dark', 'heavy', 'light', 'clean', 'dirty', 'good', 'bad', 'friendly', 'lovely', 'final'],
        'adverb' => ['quietly', 'slowly', 'quickly', 'carefully', 'well', 'badly', 'clearly', 'safely', 'happily', 'easily', 'politely', 'loudly'],
        'verb' => ['need', 'like', 'know', 'want', 'see', 'find', 'bring', 'take', 'read', 'write', 'work', 'play', 'call', 'walk', 'open', 'close', 'help', 'wait', 'watch', 'visit', 'cook', 'ride', 'drive', 'study', 'live', 'buy', 'give', 'make', 'get', 'go', 'say', 'tell', 'leave', 'meet', 'use'],
        'verb_s' => ['needs', 'likes', 'knows', 'wants', 'sees', 'finds', 'brings', 'takes', 'reads', 'writes', 'works', 'plays', 'calls', 'walks', 'opens', 'closes', 'helps', 'waits', 'watches', 'visits', 'cooks', 'rides', 'drives', 'studies', 'lives', 'buys', 'gives', 'makes', 'gets', 'goes', 'says', 'tells', 'leaves', 'meets', 'uses'],
        'past' => ['needed', 'liked', 'wanted', 'worked', 'played', 'called', 'walked', 'opened', 'closed', 'helped', 'waited', 'watched', 'visited', 'cooked', 'found', 'brought', 'took', 'wrote', 'rode', 'drove', 'gave', 'made', 'left', 'said', 'met', 'went', 'saw'],
        'participle' => ['needed', 'liked', 'wanted', 'worked', 'played', 'called', 'opened', 'closed', 'helped', 'watched', 'visited', 'cooked', 'found', 'brought', 'taken', 'written', 'ridden', 'driven', 'given', 'made', 'left', 'said', 'met', 'seen', 'known', 'broken', 'built', 'bought', 'checked', 'sent', 'done'],
        'ing' => ['working', 'playing', 'reading', 'writing', 'calling', 'walking', 'opening', 'closing', 'helping', 'waiting', 'watching', 'visiting', 'cooking', 'riding', 'driving', 'studying', 'living', 'buying', 'giving', 'making', 'going', 'taking'],
    ];

    // Finite spelling lists, not a suffix guess: told/kept/put/paid must never
    // fall through to the noun pool simply because they do not end in -ed.
    private const IRREGULAR_PAST = ['arose', 'awoke', 'became', 'began', 'bent', 'bet', 'bit', 'blew', 'broke',
        'brought', 'built', 'burnt', 'bought', 'caught', 'chose', 'came', 'cost', 'cut', 'dealt', 'dug', 'did', 'drew',
        'drank', 'drove', 'ate', 'fell', 'fed', 'felt', 'fought', 'found', 'flew', 'forgot', 'forgave', 'froze', 'got',
        'gave', 'went', 'grew', 'hung', 'had', 'heard', 'hid', 'hit', 'held', 'hurt', 'kept', 'knew', 'laid', 'led',
        'left', 'lent', 'let', 'lay', 'lit', 'lost', 'made', 'meant', 'met', 'paid', 'put', 'ran', 'read', 'rode',
        'rang', 'rose', 'said', 'saw', 'sold', 'sent', 'set', 'shook', 'shone', 'shot', 'showed', 'shut', 'sang',
        'sank', 'sat', 'slept', 'spoke', 'spent', 'split', 'spread', 'stood', 'stole', 'stuck', 'struck', 'swam',
        'swept', 'swung', 'took', 'taught', 'tore', 'told', 'thought', 'threw', 'understood', 'woke', 'wore', 'won', 'wrote'];
    private const IRREGULAR_PARTICIPLES = ['been', 'done', 'gone', 'seen', 'taken', 'given', 'written', 'eaten', 'drunk',
        'known', 'had', 'made', 'said', 'told', 'thought', 'bought', 'brought', 'caught', 'taught', 'found', 'felt', 'kept',
        'left', 'lost', 'met', 'paid', 'read', 'run', 'sent', 'sat', 'slept', 'sold', 'spent', 'stood', 'understood',
        'won', 'worn', 'broken', 'chosen', 'driven', 'fallen', 'flown', 'forgotten', 'frozen', 'grown', 'hidden',
        'ridden', 'risen', 'spoken', 'stolen', 'swum', 'thrown', 'woken', 'got', 'gotten', 'come', 'become', 'begun',
        'cut', 'put', 'set', 'built', 'held', 'heard', 'arisen', 'awoken', 'bent', 'bet', 'bitten', 'blown', 'burnt',
        'dealt', 'dug', 'drawn', 'fed', 'forgiven', 'hung', 'laid', 'led', 'lent', 'lit', 'meant', 'rung', 'shaken',
        'shone', 'shot', 'shown', 'shut', 'sung', 'sunk', 'stuck', 'struck', 'swept', 'swung', 'torn', 'hurt', 'hit',
        'let', 'spread', 'split', 'cost'];

    public static function clean(string $value): string
    {
        $value = str_replace(['’', '‘', 'ʼ', '`'], "'", trim($value));
        // Apostrophes and word-internal hyphens are spelling, not sentence punctuation.
        $value = preg_replace('/^[\s.,!?;:"()\[\]{}<>«»“”„…]+|[\s.,!?;:"()\[\]{}<>«»“”„…]+$/u', '', $value);
        return preg_match("/^'[^']+'$/u", $value) ? substr($value, 1, -1) : $value;
    }

    public static function words(string $sentence): array
    {
        return array_values(array_filter(preg_split('/\s+/u', trim($sentence)),
            static fn (string $word): bool => preg_match('/[\p{L}\p{N}]/u', $word) === 1));
    }

    private static function key(string $word): string
    {
        // Request-local only: the same authored word occurs in many roles and
        // option pools. No source data or compiled definition is cached here.
        static $keys = [];
        return $keys[$word] ??= mb_strtolower(self::clean($word));
    }

    private static function wordFamily(string $word): array
    {
        static $families = null;
        if ($families === null) {
            $families = [];
            foreach (self::WORD_FAMILIES as $family) {
                foreach ($family as $member) {
                    // Match the previous first-family-wins scan exactly.
                    $families[self::key($member)] ??= $family;
                }
            }
        }
        return $families[self::key($word)] ?? [];
    }

    private static function lexicalRole(string $word, int $index, array $sentence, ?string $role): string
    {
        $key = self::key($word); $before = self::key($sentence[$index - 1] ?? '');
        if (in_array($role, ['adjective', 'adj', 'predicative_adjective', 'evaluative_adjective'], true)) { return 'adjective'; }
        if (in_array($role, ['adverb', 'manner'], true)) { return 'adverb'; }
        if (in_array($role, ['gerund', 'transitive_gerund'], true)) { return 'ing'; }
        if (in_array($role, ['participle', 'past_participle', 'passive_participle'], true)) { return 'participle'; }
        if (in_array($role, ['past_verb', 'past_reporting'], true)) { return 'past'; }
        if (in_array($role, ['finite_verb_singular'], true)) { return 'verb_s'; }
        if (in_array($role, ['base_verb', 'base_transitive', 'transitive_base'], true)) { return 'verb'; }
        if (in_array($key, self::LEXICAL_FAMILIES['adjective'], true)) { return 'adjective'; }
        $afterAuxiliary = in_array($before, ['have', 'has', 'had', 'be', 'been', 'is', 'are', 'was', 'were', 'being'], true);
        if (in_array($key, self::IRREGULAR_PARTICIPLES, true)
            && ($afterAuxiliary || !in_array($key, self::IRREGULAR_PAST, true))) {
            // These three spellings are also ordinary base verbs. Keep that
            // form unless an explicit role/preceding auxiliary established V3.
            return !$afterAuxiliary && in_array($key, ['run', 'come', 'become'], true) ? 'verb' : 'participle';
        }
        if (in_array($key, self::IRREGULAR_PAST, true) && $key !== 'read') { return 'past'; }
        if (preg_match('/ly$/u', $key)) { return 'adverb'; }
        if (preg_match('/ing$/u', $key)) { return 'ing'; }
        if (in_array($key, self::LEXICAL_FAMILIES['participle'], true) || preg_match('/(?:ed|en)$/u', $key)) {
            return in_array($before, ['have', 'has', 'had', 'be', 'been', 'is', 'are', 'was', 'were', 'being'], true) ? 'participle' : 'past';
        }
        if (preg_match('/(?:ical|ional|ous|ive|able|ible|ful|less|ary|ory|ic)$/u', $key)) { return 'adjective'; }
        if (in_array($key, self::LEXICAL_FAMILIES['verb_s'], true)) { return 'verb_s'; }
        if (in_array($key, self::LEXICAL_FAMILIES['verb'], true)) { return 'verb'; }
        if (in_array($key, self::LEXICAL_FAMILIES['plural'], true) || ($role === 'noun_plural')) { return 'plural'; }
        if (in_array($key, self::LEXICAL_FAMILIES['noun'], true)) { return 'noun'; }
        if (preg_match('/s$/u', $key) && !preg_match('/(?:ss|ness|ics)$/u', $key)) { return 'plural'; }
        return 'noun';
    }

    /** Run only after reviewed phrase choices have been compiled and checked. */
    public static function finalize(array $definition, array $source = []): array
    {
        $levelPools = [];
        foreach ($definition['questions'] as $row) {
            if ((string) ($row['type'] ?? '') !== '4') { continue; }
            $sentence = self::words($row['target_text']); $index = 0;
            foreach ($row['markers'] as $marker => $data) {
                $role = $row['slot_roles'][$marker] ?? null;
                foreach (self::words($data['answer']) as $word) {
                    if (!self::wordFamily($word)) {
                        $family = self::lexicalRole($word, $index, $sentence, $role);
                        $levelPools[$row['level']][$family][self::key($word)] = self::clean($word);
                    }
                    ++$index;
                }
            }
        }
        foreach ($definition['questions'] as &$row) {
            if ((string) ($row['type'] ?? '') !== '4') { continue; }
            $oldMarkers = $row['markers']; $markers = []; $choices = []; $roles = []; $banned = []; $used = [];
            foreach ($row['accepted_target_texts'] ?? [$row['target_text']] as $sentence) {
                foreach (self::words($sentence) as $word) { $banned[self::key($word)] = true; }
            }
            $position = 0;
            foreach ($oldMarkers as $oldKey => $oldMarker) {
                $originalOptions = $row['options_by_marker'][$oldKey] ?? $oldMarker['options'] ?? [];
                $role = $row['slot_roles'][$oldKey] ?? null;
                foreach (self::words($oldMarker['answer']) as $part => $answer) {
                    $key = 'a'.(++$position); $aligned = [];
                    $slotWords = self::words($oldMarker['answer']);
                    $lexical = self::lexicalRole($answer, $position - 1, self::words($row['target_text']), $role);
                    // Only explicitly authored role pools qualify as reviewed
                    // choices. Older generic whole-bank options may mix levels.
                    $reviewed = !empty($source['choice_pools']) ? array_merge($originalOptions, $source['choice_pools'][$role] ?? []) : [];
                    foreach ($reviewed as $phrase) {
                        $phraseWords = self::words($phrase);
                        // A phrase of a different width can shift a noun into
                        // its adjective's position; it is not an aligned choice.
                        if (count($phraseWords) !== count($slotWords)) { continue; }
                        $candidate = $phraseWords[$part] ?? null;
                        if ($candidate !== null) {
                            $candidateRole = self::lexicalRole($candidate, $part, $phraseWords, count($slotWords) === 1 ? $role : null);
                            if ($candidateRole === $lexical || (in_array($candidateRole, ['past', 'participle'], true) && in_array($lexical, ['past', 'participle'], true))) {
                                $aligned[] = self::clean($candidate);
                            }
                        }
                    }
                    $family = self::wordFamily($answer);
                    $candidatePools = $family ? [$family] : [$aligned,
                        array_values($levelPools[$row['level']][$lexical] ?? []), self::LEXICAL_FAMILIES[$lexical]];
                    $wrong = [];
                    foreach ([false, true] as $allowUsed) {
                        foreach ($candidatePools as $priority => $candidates) {
                            $candidates = array_values(array_unique($candidates));
                            if (!$candidates) { continue; }
                            $offset = hexdec(substr(hash('sha256', $row['uuid'].'|'.$key.'|word|'.$priority), 0, 7)) % count($candidates);
                            for ($step = 0; $step < count($candidates) && count($wrong) < 3; ++$step) {
                                $candidate = $candidates[($offset + $step) % count($candidates)]; $normalized = self::key($candidate);
                                if ($normalized === '' || preg_match('/\s|[<>{}]/u', $candidate)
                                    || isset($banned[$normalized]) || (!$allowUsed && isset($used[$normalized]))
                                    || isset($wrong[$normalized])) { continue; }
                                if (preg_match('/^\p{Lu}/u', $answer) && preg_match('/^\p{Ll}/u', $candidate)) {
                                    $candidate = mb_strtoupper(mb_substr($candidate, 0, 1)).mb_substr($candidate, 1);
                                }
                                $wrong[$normalized] = $candidate; $used[$normalized] = true;
                            }
                            if (count($wrong) === 3) { break; }
                        }
                        if (count($wrong) === 3) { break; }
                    }
                    if (count($wrong) !== 3) { throw new RuntimeException('Finite word-choice pool exhausted: '.$row['uuid'].'/'.$key); }
                    $choices[$key] = array_merge([self::clean($answer)], array_values($wrong));
                    $markers[$key] = ['answer' => $answer, 'verb_hint' => $oldMarker['verb_hint'] ?? $row['verb_hint'], 'options' => $choices[$key]];
                    if ($role !== null) { $roles[$key] = $role; }
                }
            }
            $words = array_column($markers, 'answer');
            if ($words !== self::words($row['target_text'])) { throw new RuntimeException('Finite word slots change target: '.$row['uuid']); }
            $aliases = [];
            foreach ($row['accepted_target_texts'] as $sentence) {
                $alternative = self::words($sentence);
                if (count($alternative) !== count($words)) { continue; }
                foreach ($words as $index => $word) {
                    if (self::key($word) !== self::key($alternative[$index])) { $aliases['a'.($index + 1)][] = $alternative[$index]; }
                }
            }
            $row['markers'] = $markers;
            $row['tokens_correct'] = $words;
            $row['options_by_marker'] = $choices;
            $row['options'] = array_values(array_unique(array_merge(...array_values($choices))));
            $row['answer_synonyms'] = $row['display_answer_synonyms'] = array_map(static fn (array $values) => array_values(array_unique($values)), $aliases);
            if (isset($row['slot_roles'])) { $row['slot_roles'] = $roles; }
            $row['builder_word_slots_version'] = 1;
        }
        unset($row);
        return $definition;
    }

    public static function decorate(array $payload, array $source): array
    {
        if (($source['builder_word_slots_version'] ?? null) !== 1 || (string) ($source['type'] ?? '') !== '4'
            || (string) ($payload['type'] ?? '') !== '4' || ($payload['presentation'] ?? '') === 'sentence_reorder'
            || ($payload['uuid'] ?? null) !== $source['uuid'] || ($payload['question'] ?? null) !== $source['question']
            || ($payload['answers'] ?? null) !== array_column($source['markers'], 'answer')) { return $payload; }
        $revision = hash('sha256', json_encode($source, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        if (($payload['psp_content_revision'] ?? null) !== $revision) { return $payload; }
        static $previous;
        $previous ??= json_decode(file_get_contents(__DIR__.'/../../docs/content/finite-builder-word-slots.previous.v1.json'), true, 512, JSON_THROW_ON_ERROR);
        $payload['finite_word_slots'] = ['schema' => 1, 'uuid' => $source['uuid'], 'revision' => $revision,
            'question' => $source['question'], 'answers' => array_column($source['markers'], 'answer'),
            'sentences' => $source['accepted_target_texts'], 'options_by_marker' => $source['options_by_marker'],
            'previous_versions' => $previous[$source['uuid']] ?? []];
        return $payload;
    }
}
