<?php

namespace App\Support;

final class AcceptedAnswerVariants
{
    public static function rules(): array
    {
        static $rules;

        return $rules ??= json_decode(file_get_contents(__DIR__.'/../../public/data/english-contractions.json'), true, 512, JSON_THROW_ON_ERROR);
    }

    private static function invertedSubject(string $tail): ?array
    {
        $boundary = '(?=\\s+(?:'.self::rules()['question_predicates'].')\\b|\\s*$)';
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
    }

    /**
     * Return spelling variants that are grammatically equivalent for input
     * checking. Stored content remains unchanged.
     *
     * @return array<int, string>
     */
    public static function for(string $answer): array
    {
        $answer = self::normalizeTypography($answer);

        if ($answer === '') {
            return [];
        }
        if (!preg_match("/'|\\b(?:am|is|are|have|has|had|not|cannot|will|would|shall|could|should|might|must|may|let)\\b/i", $answer)) {
            return [$answer];
        }

        $variants = [$answer];
        $rules = self::rules();
        $pairs = array_merge($rules['negative'], $rules['positive']);
        // Ambiguous 's/'d are resolved by the client using the surrounding
        // sentence. Do not add both possible full forms to a marker's metadata.
        for ($index = 0; $index < count($variants) && count($variants) < 128; $index++) {
            foreach ($pairs as $short => $full) {
                foreach ([[$short, $full], [$full, $short]] as [$from, $to]) {
                    $pattern = "~(?<![a-z'])".preg_quote($from, '~')."(?![a-z'])~i";
                    $value = $variants[$index];
                    $variant = preg_replace_callback($pattern, static function (array $match) use ($value, $from, $short, $to, $full, $rules): string {
                        $tail = substr($value, strpos($value, $match[0]) + strlen($match[0]));
                        if (!isset($rules['negative'][$short]) && $from === $full && preg_match('/^\\W*$/', $tail) && preg_match('/\\b(?:yes|no)[,\\s]/i', $value)) {
                            return $match[0];
                        }

                        return $to;
                    }, $value) ?? $value;
                    if (isset($rules['negative'][$short]) && $from === $short && preg_match($pattern, $value, $match, PREG_OFFSET_CAPTURE)) {
                        $tail = substr($value, $match[0][1] + strlen($match[0][0]));
                        if ($subject = self::invertedSubject($tail)) {
                            $auxiliary = $short === "aren't" && strtolower($subject[1]) === 'i' ? 'am'
                                : ($full === 'cannot' ? 'can' : str_replace(' not', '', $full));
                            $variant = substr($value, 0, $match[0][1]).$auxiliary.' '.$subject[1].' not'.substr($tail, strlen($subject[0]));
                        }
                    }
                    if (! in_array($variant, $variants, true)) {
                        $variants[] = $variant;
                    }
                }
                if (isset($rules['negative'][$short])) {
                    $auxiliary = $full === 'cannot' ? 'can' : str_replace(' not', '', $full);
                    $pattern = "/\\b".preg_quote($auxiliary, '/')." ([a-z'-]+(?: [a-z'-]+){0,4}?) not\\b/i";
                    $variant = preg_replace_callback($pattern, static function (array $match) use ($short): string {
                        return (self::invertedSubject(' '.$match[1])[1] ?? null) === $match[1] ? $short.' '.$match[1] : $match[0];
                    }, $variants[$index]);
                    if (! in_array($variant, $variants, true)) {
                        $variants[] = $variant;
                    }
                }
            }
            $variant = preg_replace('/\bam I not\b/i', "aren't I", $variants[$index]);
            if (!in_array($variant, $variants, true)) {
                $variants[] = $variant;
            }
            if (preg_match('/\bcannot\b/i', $variants[$index])) {
                $variant = preg_replace('/\bcannot\b/i', 'can not', $variants[$index]);
                if (! in_array($variant, $variants, true)) {
                    $variants[] = $variant;
                }
            }
            if (preg_match('/\bcan not\b/i', $variants[$index])) {
                $variant = preg_replace('/\bcan not\b/i', "can't", $variants[$index]);
                if (! in_array($variant, $variants, true)) {
                    $variants[] = $variant;
                }
            }
        }

        return array_values(array_unique($variants));
    }

    public static function normalizeTypography(string $answer): string
    {
        $answer = str_replace(["‘", "’", "ʼ", "`"], "'", $answer);

        return trim(preg_replace('/\s+/u', ' ', $answer) ?? $answer);
    }

    public static function matches(string $expected, string $submitted, string $after = '', string $before = ''): bool
    {
        $normalize = static fn (string $value): string => mb_strtolower(trim(preg_replace('/[.!?…]+$/u', '', self::normalizeTypography($value))));
        $submitted = $normalize($submitted);
        if ($submitted === '') {
            return false;
        }
        $rules = self::rules();
        $nextWord = static function (string $tail): string {
            $tail = preg_replace('/^(?:(?:already|just|never|always|still|really|also)\s+)+/i', '', trim($tail));
            preg_match('/^[a-z]+/i', $tail, $match);

            return strtolower($match[0] ?? '');
        };
        $participle = static fn (string $word): bool => in_array($word, $rules['participles'], true) || preg_match('/(?:ed|en)$/', $word) === 1;
        $expected = self::normalizeTypography($expected);
        $sources = [$expected];
        foreach (['s', 'd'] as $suffix) {
            $pattern = '/\b('.implode('|', $rules[$suffix.'_subjects']).")'".$suffix.'\b/i';
            if (preg_match($pattern, $expected, $match, PREG_OFFSET_CAPTURE)) {
                [$matchText, $offset] = $match[0];
                $next = $nextWord(substr($expected, $offset + strlen($matchText)).' '.$after);
                $auxiliary = $suffix === 'd'
                    ? ($next === 'better' || $participle($next) ? 'had' : 'would')
                    : (in_array($next, ['been', 'got', 'gotten'], true) ? 'has' : ($participle($next) ? null : 'is'));
                if ($next !== '' && $auxiliary !== null) {
                    $sources[] = substr_replace($expected, $match[1][0].' '.$auxiliary, $offset, strlen($matchText));
                }
            }
        }
        $variants = [];
        foreach ($sources as $source) {
            $variants = array_merge($variants, self::for($source));
        }
        foreach ($variants as $value) {
            foreach (['s' => 'is|has', 'd' => 'had|would'] as $suffix => $auxiliaries) {
                $pattern = '/\b('.implode('|', $rules[$suffix.'_subjects']).') ('.$auxiliaries.')\b/i';
                $contracted = preg_replace_callback($pattern, static function (array $match) use ($value, $after, $before, $nextWord, $participle, $suffix): string {
                    $tail = substr($value, strpos($value, $match[0]) + strlen($match[0])).' '.$after;
                    $next = $nextWord($tail);
                    if ($next === '' && preg_match('/\b(?:yes|no)[,\s]/i', $before.$value)) {
                        return $match[0];
                    }
                    $aux = strtolower($match[2]);
                    if ($next !== '' && (($aux === 'has' && !$participle($next)) || ($aux === 'had' && $next !== 'better' && !$participle($next)))) {
                        return $match[0];
                    }

                    return $match[1]."'".$suffix;
                }, $value);
                $variants[] = $contracted;
            }
        }

        if (trim($after) === '' && preg_match('/\b(?:yes|no)[,\s]/i', $before.$expected)) {
            $variants = array_filter($variants, static fn (string $value): bool => !preg_match("/'(?:m|re|ve|ll|s|d)[.!?…]*$/u", $value));
        }

        return in_array($submitted, array_map($normalize, $variants), true);
    }
}
