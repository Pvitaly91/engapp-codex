<?php

namespace Tests\Unit;

use App\Support\AcceptedAnswerVariants;
use App\Support\ComposeTokenCase;
use PHPUnit\Framework\TestCase;

class PresentPerfectTimeExpressionsContentQualityTest extends TestCase
{
    private const LEVELS = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'];

    private const STANDARD_DEFINITION =
        'database/seeders/V3/Tenses/PresentPerfect/PresentPerfectTimeExpressionsAllLevelsV3Seeder/definition.json';

    private const COMPOSE_DEFINITION =
        'database/seeders/V3/Polyglot/PolyglotPresentPerfectTimeExpressionsAllLevelsLessonSeeder/definition.json';

    private const LEGACY_DEFINITION =
        'database/seeders/V3/Polyglot/PolyglotPresentPerfectTimeExpressionsLessonSeeder/definition.json';

    private const THEORY_DEFINITION =
        'database/seeders/Page_V3/Tenses/PresentPerfect/PresentPerfectTimeExpressionsTheorySeeder/definition.json';

    private const THEORY_LOCALIZATION_DIRECTORY =
        'database/seeders/Page_V3/Tenses/PresentPerfect/PresentPerfectTimeExpressionsTheorySeeder/localizations';

    private const TOPIC_TAGS = [
        'ever',
        'never',
        'already',
        'just',
        'yet',
        'recently_lately',
        'so_far',
        'for_since',
        'unfinished_time',
        'past_simple_contrast',
        'typical_mistakes',
    ];

    private function rootPath(): string
    {
        return dirname(__DIR__, 2);
    }

    private function readJson(string $relativePath): array
    {
        return json_decode(
            (string) file_get_contents($this->rootPath().'/'.$relativePath),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
    }

    /** @param array<int, array<string, mixed>> $questions */
    private function assertBalancedLevels(array $questions, string $bank): void
    {
        $this->assertCount(72, $questions, "{$bank} must contain 72 questions");
        $counts = array_count_values(array_column($questions, 'level'));
        ksort($counts);

        $this->assertSame(self::LEVELS, array_keys($counts), "{$bank} has unexpected levels");
        foreach (self::LEVELS as $level) {
            $this->assertSame(12, $counts[$level] ?? 0, "{$bank} must contain 12 {$level} questions");
        }
    }

    /** @return list<string> */
    private function orderedAnswers(array $question): array
    {
        $answers = $question['answers'] ?? [];
        uksort($answers, 'strnatcmp');

        return array_values(array_map('strval', $answers));
    }

    /** @return list<string> */
    private function composeDistractors(array $question): array
    {
        $options = array_values(array_map('strval', $question['options'] ?? []));
        $answers = $this->orderedAnswers($question);

        foreach (array_unique($answers) as $answer) {
            $this->assertContains($answer, $options, "Missing correct token {$answer} in {$question['uuid']}");
        }

        $correctLookup = array_fill_keys(array_map('mb_strtolower', $answers), true);

        return array_values(array_filter(
            $options,
            static fn (string $option): bool => ! isset($correctLookup[mb_strtolower($option)])
        ));
    }

    /** @param array<int, array<string, mixed>> $questions */
    private function averageAnswerLength(array $questions, string $level): float
    {
        $counts = [];
        foreach ($questions as $question) {
            if (($question['level'] ?? null) === $level) {
                $counts[] = count($this->orderedAnswers($question));
            }
        }

        $this->assertNotEmpty($counts, "No {$level} Sentence Builder questions available");

        return array_sum($counts) / count($counts);
    }

    public function test_standard_questions_are_unique_well_formed_and_helpful(): void
    {
        $definition = $this->readJson(self::STANDARD_DEFINITION);
        $this->assertSame('British English', $definition['saved_test']['filters']['english_standard'] ?? null);
        $questions = $definition['questions'];
        $this->assertBalancedLevels($questions, 'Standard bank');
        $this->assertCount(72, array_unique(array_column($questions, 'question')), 'Standard questions repeat.');

        $completed = [];
        $coveredTopics = [];

        foreach ($questions as $question) {
            $uuid = (string) $question['uuid'];
            $this->assertSame(['a1'], array_keys($question['markers'] ?? []), "{$uuid} must have one marker");
            $this->assertSame(1, substr_count((string) $question['question'], '{a1}'), "Wrong marker count in {$uuid}");
            $this->assertMatchesRegularExpression('/[.!?]$/u', (string) $question['question'], "Missing punctuation in {$uuid}");
            $this->assertSame([$question['question']], $question['variants'] ?? null, "Stale variant in {$uuid}");

            $marker = $question['markers']['a1'];
            $answer = trim((string) ($marker['answer'] ?? ''));
            $options = array_values(array_map(
                static fn ($option): string => trim((string) $option),
                $marker['options'] ?? []
            ));

            $this->assertNotSame('', $answer, "Empty answer in {$uuid}");
            $this->assertCount(5, $options, "{$uuid} must have five options");
            $this->assertSame($answer, $options[0] ?? null, "Correct option must be authored first in {$uuid}");
            $this->assertCount(5, array_unique($options), "Duplicate option in {$uuid}");

            $normalizedOptions = array_map(
                static fn (string $option): string => mb_strtolower(AcceptedAnswerVariants::normalizeTypography($option)),
                $options
            );
            $this->assertCount(5, array_unique($normalizedOptions), "Equivalent options in {$uuid}");

            $accepted = array_map('mb_strtolower', AcceptedAnswerVariants::for($answer));
            foreach (array_slice($normalizedOptions, 1) as $distractor) {
                $this->assertNotContains($distractor, $accepted, "Accepted alternative is a distractor in {$uuid}");
            }

            $hint = trim((string) ($marker['verb_hint'] ?? ''));
            $this->assertMatchesRegularExpression('/[А-Яа-яІіЇїЄєҐґ]/u', $hint, "Hint is not Ukrainian in {$uuid}");
            $this->assertStringNotContainsStringIgnoringCase($answer, $hint, "Hint reveals the answer in {$uuid}");
            $this->assertDoesNotMatchRegularExpression(
                '/\b(?:use|choose|insert)\s+(?:ever|never|already|just|yet|recently|lately|since|for)\b/iu',
                $hint,
                "Hint directly names the marker in {$uuid}"
            );

            $topics = array_values(array_intersect($question['tag_keys'] ?? [], self::TOPIC_TAGS));
            $this->assertNotEmpty($topics, "Missing time-expression topic tag in {$uuid}");
            $coveredTopics = array_merge($coveredTopics, $topics);
            $completed[] = str_replace('{a1}', $answer, (string) $question['question']);
        }

        $this->assertCount(72, array_unique($completed), 'Completed standard questions repeat.');
        foreach (self::TOPIC_TAGS as $topic) {
            $this->assertContains($topic, $coveredTopics, "Standard bank does not cover {$topic}");
        }

        $content = json_encode($questions, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        foreach ([
            '/Use Present Perfect/iu',
            '/Do not use yesterday/iu',
            '/Use (?:ever|never|already|just|yet|since|for)\b/iu',
            '/So far means/iu',
            '/My friends have already finished my homework/iu',
            '/Have you \{a1\} visited (?:London|Paris|Berlin|New York|Singapore|Geneva)\?/iu',
            '/(?:She|Tom) has \{a1\} missed a deadline/iu',
            '/I have been sleeping badly \{a1\}/iu',
            '/I have waited here \{a1\} half an hour/iu',
            '/the full text is appearing on the website now/iu',
            '/critique the doctrine has \{a1\} attracted/iu',
        ] as $pattern) {
            $this->assertDoesNotMatchRegularExpression($pattern, $content, "Legacy standard content matched {$pattern}");
        }
    }

    public function test_sentence_builder_is_unique_atomic_localized_and_progressive(): void
    {
        $definition = $this->readJson(self::COMPOSE_DEFINITION);
        $this->assertSame('British English', $definition['saved_test']['filters']['english_standard'] ?? null);
        $questions = $definition['questions'];
        $this->assertBalancedLevels($questions, 'Sentence Builder bank');
        $this->assertCount(72, array_unique(array_column($questions, 'question')), 'Ukrainian prompts repeat.');

        foreach ($questions as $question) {
            $uuid = (string) $question['uuid'];
            $this->assertSame(4, $question['type'] ?? null, "Wrong compose type in {$uuid}");
            $this->assertMatchesRegularExpression('/[А-Яа-яІіЇїЄєҐґ]/u', (string) $question['question'], "Prompt is not Ukrainian in {$uuid}");
            $this->assertMatchesRegularExpression('/[.!?]$/u', (string) $question['question'], "Missing prompt punctuation in {$uuid}");

            $answers = $this->orderedAnswers($question);
            $this->assertNotEmpty($answers, "Missing correct tokens in {$uuid}");
            $this->assertSame($answers, ComposeTokenCase::normalize($answers), "Wrong token case in {$uuid}");

            foreach (($question['options'] ?? []) as $option) {
                $option = trim((string) $option);
                $this->assertNotSame('', $option, "Empty option in {$uuid}");
                $this->assertDoesNotMatchRegularExpression('/\s/u', $option, "Multiword option in {$uuid}: {$option}");
            }

            $distractors = $this->composeDistractors($question);
            $this->assertGreaterThanOrEqual(4, count($distractors), "Too few distractors in {$uuid}");
            $this->assertLessThanOrEqual(8, count($distractors), "Too many distractors in {$uuid}");
            $normalizedDistractors = array_map('mb_strtolower', $distractors);
            $this->assertCount(count($normalizedDistractors), array_unique($normalizedDistractors), "Duplicate distractors in {$uuid}");
            $normalizedAnswers = array_map('mb_strtolower', $answers);
            if (in_array('lately', $normalizedAnswers, true)) {
                $this->assertNotContains('recently', $normalizedDistractors, "Recently duplicates lately in {$uuid}");
            }
            if (in_array('recently', $normalizedAnswers, true)) {
                $this->assertNotContains('lately', $normalizedDistractors, "Lately duplicates recently in {$uuid}");
            }

            $hints = array_filter(
                $question['verb_hints'] ?? [],
                static fn ($hint): bool => trim((string) $hint) !== ''
            );
            $this->assertNotEmpty($hints, "Missing verb hint in {$uuid}");
            foreach ($hints as $marker => $hint) {
                $hint = trim((string) $hint);
                $this->assertMatchesRegularExpression('/[А-Яа-яІіЇїЄєҐґ]/u', $hint, "Hint is not Ukrainian in {$uuid}");
                $answer = trim((string) ($question['answers'][$marker] ?? ''));
                if ($answer !== '') {
                    $this->assertStringNotContainsStringIgnoringCase(
                        $answer,
                        $hint,
                        "Hint reveals the token for {$marker} in {$uuid}"
                    );
                }
                $this->assertDoesNotMatchRegularExpression(
                    '/\b(?:have|has|ever|never|already|just|yet|recently|lately|since|for|today)\b|\b(?:so far|up to now|this (?:week|month|year|morning))\b/iu',
                    $hint,
                    "Compose hint reveals a target marker in {$uuid}"
                );
            }
        }

        $previousAverage = null;
        foreach (self::LEVELS as $level) {
            $average = $this->averageAnswerLength($questions, $level);
            if ($previousAverage !== null) {
                $this->assertGreaterThan(
                    $previousAverage,
                    $average,
                    "{$level} Sentence Builder targets are not structurally richer than the previous level."
                );
            }
            $previousAverage = $average;
        }

        $content = json_encode($questions, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        foreach ([
            '/відвідував\s+(?:Лондоні|Парижі|Берліні|Нью-Йорку|Сінгапурі|Женеві)/iu',
            '/Том[^.!?]{0,80}(?:пропускала|відповіла)/u',
            '/виконали десять завдань досі/iu',
            '/Мої друзі вже завершили домашнє завдання/iu',
        ] as $pattern) {
            $this->assertDoesNotMatchRegularExpression($pattern, $content, "Legacy Sentence Builder content matched {$pattern}");
        }
    }

    public function test_legacy_sentence_builder_has_context_hints_and_canonical_theory_url(): void
    {
        $definition = $this->readJson(self::LEGACY_DEFINITION);
        $questions = $definition['questions'];

        $this->assertCount(24, $questions);
        $this->assertCount(24, array_unique(array_column($questions, 'question')), 'Legacy prompts repeat.');
        $this->assertSame(
            'https://gramlyze.com/theory/tenses/present-perfect/present-perfect-time-expressions',
            $definition['saved_test']['filters']['prompt_generator']['theory_page']['url'] ?? null
        );
        $this->assertSame('British English', $definition['saved_test']['filters']['english_standard'] ?? null);

        foreach ($questions as $question) {
            $uuid = (string) $question['uuid'];
            $this->assertMatchesRegularExpression('/[.!?]$/u', (string) $question['question'], "Missing punctuation in {$uuid}");
            $hints = array_filter(
                $question['verb_hints'] ?? [],
                static fn ($hint): bool => trim((string) $hint) !== ''
            );
            $this->assertNotEmpty($hints, "Missing verb hint in {$uuid}");
            foreach ($hints as $hint) {
                $this->assertMatchesRegularExpression(
                    '/[А-Яа-яІіЇїЄєҐґ]/u',
                    trim((string) $hint),
                    "Hint is not Ukrainian in {$uuid}"
                );
            }
            foreach (($question['options'] ?? []) as $option) {
                $this->assertDoesNotMatchRegularExpression('/\s/u', trim((string) $option), "Multiword option in {$uuid}");
            }
            $distractors = $this->composeDistractors($question);
            $this->assertGreaterThanOrEqual(3, count($distractors), "Too few distractors in {$uuid}");
            $this->assertLessThanOrEqual(8, count($distractors), "Too many distractors in {$uuid}");
            $normalizedAnswers = array_map('mb_strtolower', $this->orderedAnswers($question));
            $normalizedDistractors = array_map('mb_strtolower', $distractors);
            if (in_array('lately', $normalizedAnswers, true)) {
                $this->assertNotContains('recently', $normalizedDistractors, "Recently duplicates lately in {$uuid}");
            }
            if (in_array('recently', $normalizedAnswers, true)) {
                $this->assertNotContains('lately', $normalizedDistractors, "Lately duplicates recently in {$uuid}");
            }
        }

        $content = json_encode($questions, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        foreach ([
            '/заощадили багато грошей досі/iu',
            '/^.*Сьогодні йшов дощ\?.*$/imu',
            '/^.*Що ти зробив сьогодні\?.*$/imu',
            '/Has he studied English for three years\?/iu',
        ] as $pattern) {
            $this->assertDoesNotMatchRegularExpression($pattern, $content, "Legacy A2 content matched {$pattern}");
        }
    }

    public function test_theory_covers_the_test_scope_and_navigation_uses_canonical_routes(): void
    {
        $definition = $this->readJson(self::THEORY_DEFINITION);
        $this->assertMatchesRegularExpression('/[А-Яа-яІіЇїЄєҐґ]/u', (string) ($definition['page']['title'] ?? ''));

        $baseContent = mb_strtolower(json_encode(
            $definition['page']['blocks'] ?? [],
            JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
        ));

        foreach (['a1', 'c2', 'just', 'already', 'yet', 'ever', 'never', 'for', 'since', 'recently', 'lately', 'so far', 'up to now', 'today', 'this week', 'past simple'] as $term) {
            $this->assertMatchesRegularExpression(
                '/(?<![a-z])'.preg_quote($term, '/').'(?![a-z])/u',
                $baseContent,
                "Theory does not cover {$term}"
            );
        }
        $this->assertMatchesRegularExpression('/(?:ще не заверш|триває)/u', $baseContent, 'Theory does not explain unfinished periods.');
        $this->assertMatchesRegularExpression('/(?:після|між)[^.!?]{0,80}(?:have|has)/u', $baseContent, 'Theory does not explain marker position.');
        $this->assertStringContainsString('британськ', $baseContent, 'Theory does not declare the British English exercise standard.');

        $navigation = collect($definition['page']['blocks'] ?? [])->firstWhere('type', 'navigation-chips');
        $this->assertIsArray($navigation);
        $navigationBody = json_decode((string) $navigation['body'], true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame([
            '/theory/tenses/present-perfect/present-perfect-forms',
            '/theory/tenses/present-perfect/present-perfect-negatives',
            '/theory/tenses/present-perfect/present-perfect-questions',
            '/theory/tenses/present-perfect/present-perfect-time-expressions',
        ], array_column($navigationBody['items'] ?? [], 'url'));
        $this->assertSame(1, count(array_filter(
            $navigationBody['items'] ?? [],
            static fn (array $item): bool => (bool) ($item['current'] ?? false)
        )));

        foreach (['en', 'pl'] as $locale) {
            $localization = $this->readJson(self::THEORY_LOCALIZATION_DIRECTORY.'/'.$locale.'.json');
            $localizedContent = mb_strtolower(json_encode($localization, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
            foreach (['recently', 'lately', 'so far', 'today', 'this week', 'past simple'] as $term) {
                $this->assertStringContainsString($term, $localizedContent, strtoupper($locale)." theory does not cover {$term}");
            }
            $this->assertStringContainsString(
                $locale === 'en' ? 'british english' : 'brytyjsk',
                $localizedContent,
                strtoupper($locale).' theory does not declare the British English exercise standard.'
            );

            $localizedNavigation = collect($localization['blocks'] ?? [])->firstWhere('index', 8);
            $this->assertIsArray($localizedNavigation, "Missing {$locale} navigation block");
            $localizedNavigationBody = json_decode((string) $localizedNavigation['body'], true, 512, JSON_THROW_ON_ERROR);
            $this->assertSame(array_map(
                static fn (string $slug): string => "/{$locale}/theory/tenses/present-perfect/{$slug}",
                [
                    'present-perfect-forms',
                    'present-perfect-negatives',
                    'present-perfect-questions',
                    'present-perfect-time-expressions',
                ]
            ), array_column($localizedNavigationBody['items'] ?? [], 'url'));
            $this->assertSame(1, count(array_filter(
                $localizedNavigationBody['items'] ?? [],
                static fn (array $item): bool => (bool) ($item['current'] ?? false)
            )));
        }
    }
}
