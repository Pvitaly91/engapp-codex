<?php

namespace App\Services;

class QuestionReportIssueCatalog
{
    public const CORRECT_ANSWER_IN_VERB_HINT = 'correct_answer_in_verb_hint';
    public const MISSING_VERB_HINT = 'missing_verb_hint';
    public const WRONG_ACCEPTED_ANSWER = 'wrong_accepted_answer';
    public const WRONG_OR_EXTRA_OPTION = 'wrong_or_extra_option';
    public const MULTIPLE_VALID_ANSWERS_ONLY_ONE_ACCEPTED = 'multiple_valid_answers_only_one_accepted';
    public const TYPO_OR_BAD_TRANSLATION = 'typo_or_bad_translation';
    public const WRONG_TOPIC_OR_LEVEL = 'wrong_topic_or_level';
    public const OTHER = 'other';

    private const KEYS = [
        self::CORRECT_ANSWER_IN_VERB_HINT,
        self::MISSING_VERB_HINT,
        self::WRONG_ACCEPTED_ANSWER,
        self::WRONG_OR_EXTRA_OPTION,
        self::MULTIPLE_VALID_ANSWERS_ONLY_ONE_ACCEPTED,
        self::TYPO_OR_BAD_TRANSLATION,
        self::WRONG_TOPIC_OR_LEVEL,
        self::OTHER,
    ];

    private const LEGACY_ALIASES = [
        'verb_hint_contains_answer' => self::CORRECT_ANSWER_IN_VERB_HINT,
        'verb_hint_missing' => self::MISSING_VERB_HINT,
        'wrong_correct_answer' => self::WRONG_ACCEPTED_ANSWER,
        'multiple_correct_options' => self::MULTIPLE_VALID_ANSWERS_ONLY_ONE_ACCEPTED,
        'wrong_translation' => self::TYPO_OR_BAD_TRANSLATION,
        'typo_or_grammar' => self::TYPO_OR_BAD_TRANSLATION,
    ];

    /**
     * @return array<int, array{key: string, slug: string, label: string, description: string, prompt_instruction: string, prompt_directive: string}>
     */
    public function all(): array
    {
        return array_map(fn (string $key): array => [
            'key' => $key,
            'slug' => $key,
            'label' => __("report_question.issue.{$key}"),
            'description' => __("report_question.issue_description.{$key}"),
            'prompt_instruction' => $this->instruction($key),
            'prompt_directive' => $this->instruction($key),
        ], self::KEYS);
    }

    /**
     * @return array<int, string>
     */
    public function keys(): array
    {
        return self::KEYS;
    }

    /**
     * @return array<int, string>
     */
    public function slugs(): array
    {
        return $this->keys();
    }

    /**
     * @param  iterable<int, mixed>  $input
     * @return array<int, string>
     */
    public function normalize(iterable $input): array
    {
        $present = [];

        foreach ($input as $value) {
            $key = $this->normalizeOne($value);

            if ($key !== null) {
                $present[$key] = true;
            }
        }

        return array_values(array_filter(
            self::KEYS,
            static fn (string $key): bool => isset($present[$key])
        ));
    }

    /**
     * @param  iterable<int, mixed>  $input
     * @return array<int, string>
     */
    public function labels(iterable $input): array
    {
        return array_map(
            fn (string $key): string => (string) $this->find($key)['label'],
            $this->normalize($input)
        );
    }

    /**
     * @param  iterable<int, mixed>  $input
     * @return array<int, string>
     */
    public function instructions(iterable $input): array
    {
        return array_map(
            fn (string $key): string => $this->instruction($key),
            $this->normalize($input)
        );
    }

    /**
     * @return array{key: string, slug: string, label: string, description: string, prompt_instruction: string, prompt_directive: string}|null
     */
    public function find(string $key): ?array
    {
        $normalized = $this->normalizeOne($key);

        if ($normalized === null) {
            return null;
        }

        foreach ($this->all() as $entry) {
            if ($entry['key'] === $normalized) {
                return $entry;
            }
        }

        return null;
    }

    public function instruction(string $key): string
    {
        return match ($this->normalizeOne($key) ?? $key) {
            self::CORRECT_ANSWER_IN_VERB_HINT => 'Перевір, що verb_hint не розкриває правильну відповідь. verb_hint має бути підказкою, наприклад base verb, infinitive, tense clue або контекст, але не готовим accepted answer.',
            self::MISSING_VERB_HINT => 'Перевір питання, де без verb_hint кілька options можуть бути граматично можливими. Додай verb_hint або уточни формулювання питання, щоб правильний варіант був однозначним.',
            self::WRONG_ACCEPTED_ANSWER => 'Перевір accepted answers/question_answers. Якщо accepted answer суперечить перекладу або граматиці, виправ answer і синхронізуй options.',
            self::WRONG_OR_EXTRA_OPTION => 'Перевір options. Видали або заміни неправильні, зайві, дубльовані або confusing options.',
            self::MULTIPLE_VALID_ANSWERS_ONLY_ONE_ACCEPTED => 'Якщо кілька відповідей граматично правильні, або додай їх до accepted answers, або зміни prompt/verb_hint/options так, щоб залишився один очевидний правильний варіант.',
            self::TYPO_OR_BAD_TRANSLATION => 'Перевір source sentence, target sentence, grammar, punctuation і translation consistency.',
            self::WRONG_TOPIC_OR_LEVEL => 'Перевір, що питання відповідає темі, рівню і saved test slug. Якщо ні - перенеси або виправ seed data.',
            self::OTHER => 'Врахуй додатковий коментар report.',
            default => '',
        };
    }

    private function normalizeOne(mixed $value): ?string
    {
        $key = is_string($value) ? trim($value) : '';

        if ($key === '') {
            return null;
        }

        $key = self::LEGACY_ALIASES[$key] ?? $key;

        return in_array($key, self::KEYS, true) ? $key : null;
    }
}
