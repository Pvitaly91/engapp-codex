<?php

namespace App\Support;

use App\Models\Question;

/** Explicit authored compose prompts; English gap questions remain untouched. */
final class LocalizedComposeText
{
    public const PROVIDER = 'compose_prompt';

    private const REVISION_SEEDERS = [
        'Database\\Seeders\\V3\\Tenses\\PastPerfectContinuous\\PastPerfectContinuousFormsAllLevelsV3Seeder',
        'Database\\Seeders\\V3\\Tenses\\PastPerfectContinuous\\PastPerfectContinuousNegativesAllLevelsV3Seeder',
        'Database\\Seeders\\V3\\Tenses\\PastPerfectContinuous\\PastPerfectContinuousQuestionsAllLevelsV3Seeder',
        'Database\\Seeders\\V3\\Tenses\\PastPerfectContinuous\\PastPerfectContinuousTimeExpressionsAllLevelsV3Seeder',
        'Database\\Seeders\\V3\\Polyglot\\PolyglotPastPerfectContinuousFormsAllLevelsLessonSeeder',
        'Database\\Seeders\\V3\\Polyglot\\PolyglotPastPerfectContinuousNegativesAllLevelsLessonSeeder',
        'Database\\Seeders\\V3\\Polyglot\\PolyglotPastPerfectContinuousQuestionsAllLevelsLessonSeeder',
        'Database\\Seeders\\V3\\Polyglot\\PolyglotPastPerfectContinuousTimeExpressionsAllLevelsLessonSeeder',
        'Database\\Seeders\\V3\\Polyglot\\PolyglotPastPerfectContinuousBasicsB2LessonSeeder',
    ];

    public static function revisionEligible(Question $question): bool
    {
        return in_array((string) $question->seeder, self::REVISION_SEEDERS, true) && self::optedIn($question);
    }

    /** Restore the authored conjunction case without changing shared option rows. */
    public static function normalizeTokens(Question $question, array $tokens): array
    {
        $tokens = ComposeTokenCase::normalize($tokens);
        if (! self::revisionEligible($question)) {
            return $tokens;
        }

        foreach ($tokens as $index => $token) {
            if ($index > 0 && $token === 'When') {
                $tokens[$index] = 'when';
            }
        }

        return $tokens;
    }

    /** A locale-independent fingerprint of the canonical authored task. */
    public static function revision(Question $question): ?string
    {
        if (! self::revisionEligible($question)) {
            return null;
        }

        $answers = $question->answers->sort(fn ($a, $b): int => strnatcasecmp((string) $a->marker, (string) $b->marker))
            ->map(fn ($answer): array => [(string) $answer->marker, (string) ($answer->option->option ?? $answer->answer ?? '')])->values()->all();
        $options = $question->options->pluck('option')->map(fn ($value): string => (string) $value)->sort()->values()->all();
        $hints = $question->hints->map(fn ($hint): array => [
            (string) $hint->provider, self::locale((string) $hint->locale), (string) $hint->hint,
        ])->sortBy(fn (array $hint): string => json_encode($hint, JSON_UNESCAPED_UNICODE))->values()->all();
        $verbHints = $question->verbHints->map(fn ($hint): array => [
            strtolower((string) $hint->marker), self::locale((string) ($hint->locale ?? '')), (string) ($hint->option->option ?? ''),
        ])->sortBy(fn (array $hint): string => json_encode($hint, JSON_UNESCAPED_UNICODE))->values()->all();

        return hash('sha256', json_encode([
            'ppc-authored-task-v2-balanced-presentation-when-case', (string) ($question->getRawOriginal('question') ?? $question->question),
            (string) $question->type, $question->options_by_marker, $answers, $options, $hints, $verbHints,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
    }

    /** Legacy and comparison banks never opt into cache invalidation. */
    public static function cachedNeedsRefresh(array $questions): bool
    {
        $cached = collect($questions)->filter(fn ($question): bool => is_array($question));
        $ids = $cached->pluck('id')->filter()->map(fn ($id): int => (int) $id)->unique()->values()->all();
        $uuids = $cached->pluck('uuid')->filter()->unique()->values()->all();
        if ($ids === [] && $uuids === []) {
            return false;
        }

        // Do not reference the seeder column in SQL: older legacy fixtures may
        // lack it, and must retain their existing non-opt-in behavior.
        $models = Question::query()->where(function ($query) use ($ids, $uuids): void {
            if ($ids !== []) {
                $query->whereIn('id', $ids);
            }
            if ($uuids !== []) {
                $query->{$ids === [] ? 'whereIn' : 'orWhereIn'}('uuid', $uuids);
            }
        })->get()->filter(fn (Question $question): bool => in_array((string) $question->seeder, self::REVISION_SEEDERS, true));
        if ($models->isEmpty()) {
            return false;
        }
        $models->loadMissing('hints');
        $models = $models->filter(fn (Question $question): bool => self::optedIn($question));
        if ($models->isEmpty()) {
            return false;
        }
        $models->loadMissing(['answers.option', 'options', 'verbHints.option']);
        $byUuid = $models->keyBy('uuid');
        $byId = $models->keyBy('id');

        foreach ($cached as $question) {
            $uuid = trim((string) ($question['uuid'] ?? ''));
            $model = $uuid !== '' ? $byUuid->get($uuid) : $byId->get($question['id'] ?? null);
            if ($model && ($question['compose_content_revision'] ?? null) !== self::revision($model)) {
                return true;
            }
        }

        return false;
    }

    public static function storedVariantIsCurrent(Question $question, mixed $storedVariant): bool
    {
        if (! self::revisionEligible($question) || ! is_string($storedVariant) || $storedVariant === '') {
            return true;
        }
        $canonical = (string) ($question->getRawOriginal('question') ?? $question->question);
        return $storedVariant === $canonical || ($question->relationLoaded('variants') && $question->variants->contains('text', $storedVariant));
    }

    public static function locale(?string $locale = null): string
    {
        $locale = strtolower(trim($locale ?? app()->getLocale()));
        return $locale === 'ua' ? 'uk' : $locale;
    }

    public static function optedIn(Question $question): bool
    {
        return $question->relationLoaded('hints') && $question->hints->contains('provider', self::PROVIDER);
    }

    public static function source(Question $question, ?string $locale = null): string
    {
        $locale = self::locale($locale);
        $row = $question->relationLoaded('hints') ? $question->hints->first(fn ($h) => $h->provider === self::PROVIDER
            && self::locale((string) $h->locale) === $locale && filled($h->hint)) : null;
        return $row ? trim((string) $row->hint) : (string) $question->question;
    }

    public static function hint(Question $question): ?string
    {
        if (!$question->relationLoaded('hints')) { return null; }
        $row = $question->hints->first(fn ($h) => $h->provider !== self::PROVIDER
            && self::locale((string) $h->locale) === self::locale() && filled($h->hint));
        return $row ? trim((string) $row->hint) : null;
    }

    public static function explanations(Question $question): array
    {
        if (!$question->relationLoaded('chatgptExplanations')) { return []; }
        return $question->chatgptExplanations->filter(fn ($e) => self::locale((string) $e->language) === self::locale()
            && filled($e->wrong_answer) && filled($e->explanation))
            ->mapWithKeys(fn ($e) => [trim((string) $e->wrong_answer) => trim((string) $e->explanation)])->all();
    }
}
