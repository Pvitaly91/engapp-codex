<?php

namespace App\Services;

use App\Models\Word;

class IrregularVerbsService
{
    private const FORM1_EXCEPTIONS = [
        'be' => 'is',
        'have' => 'has',
        'do' => 'does',
        'go' => 'goes',
    ];

    private const ING_EXCEPTIONS = [
        'be' => 'being',
        'lie' => 'lying',
        'die' => 'dying',
        'tie' => 'tying',
        'see' => 'seeing',
    ];

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getVerbs(): array
    {
        $words = Word::query()
            ->whereIn('type', ['base', 'past', 'participle'])
            ->with(['translate' => fn ($query) => $query->where('lang', 'uk')])
            ->get()
            ->filter(fn ($word) => optional($word->translate)->translation);

        $grouped = [];

        /** @var \App\Models\Word $word */
        foreach ($words as $word) {
            $translation = trim((string) optional($word->translate)->translation);
            $key = mb_strtolower($translation);
            $grouped[$key] ??= [
                'translation' => $translation,
                'base' => [],
                'past' => [],
                'participle' => [],
            ];

            $grouped[$key] = $this->appendForm($grouped[$key], $word->type, $word->word);
        }

        $verbs = [];

        foreach ($grouped as $group) {
            if (empty($group['base']) || empty($group['past']) || empty($group['participle'])) {
                continue;
            }

            $baseForms = array_values(array_unique($group['base']));
            $base = $baseForms[0];

            $f2 = array_values(array_unique($group['past']));
            $f3 = array_values(array_unique($group['participle']));

            $verbs[] = [
                'base' => $base,
                'base_forms' => $baseForms,
                'translation' => $group['translation'],
                'f1' => $this->makeForm1($base),
                'f2' => $f2,
                'f3' => $f3,
                'f4' => $this->makeForm4($base),
            ];
        }

        return $verbs;
    }

    /**
     * @param array<string, mixed> $group
     * @return array<string, mixed>
     */
    private function appendForm(array $group, ?string $type, string $word): array
    {
        $normalized = trim(mb_strtolower($word));

        return match ($type) {
            'base' => $this->pushUnique($group, 'base', $normalized),
            'past' => $this->pushUnique($group, 'past', $normalized),
            'participle' => $this->pushUnique($group, 'participle', $normalized),
            default => $group,
        };
    }

    /**
     * @param array<string, mixed> $group
     * @return array<string, mixed>
     */
    private function pushUnique(array $group, string $key, string $value): array
    {
        if (! in_array($value, $group[$key], true)) {
            $group[$key][] = $value;
        }

        return $group;
    }

    private function makeForm1(string $base): string
    {
        $normalized = mb_strtolower($base);
        if (isset(self::FORM1_EXCEPTIONS[$normalized])) {
            return self::FORM1_EXCEPTIONS[$normalized];
        }

        if (preg_match('/[^aeiou]y$/u', $normalized)) {
            return preg_replace('/y$/u', 'ies', $normalized);
        }

        if (preg_match('/(s|x|z|ch|sh|o)$/u', $normalized)) {
            return $normalized . 'es';
        }

        return $normalized . 's';
    }

    private function makeForm4(string $base): string
    {
        $normalized = mb_strtolower($base);
        if (isset(self::ING_EXCEPTIONS[$normalized])) {
            return self::ING_EXCEPTIONS[$normalized];
        }

        if (preg_match('/ie$/u', $normalized)) {
            return preg_replace('/ie$/u', 'ying', $normalized);
        }

        if (preg_match('/[^e]e$/u', $normalized)) {
            return preg_replace('/e$/u', 'ing', $normalized);
        }

        if (preg_match('/([bcdfghjklmnpqrtvwxyz])([aeiou])([bcdfghjklmnpqrtvwxyz])$/u', $normalized, $matches)) {
            $lastConsonant = $matches[3];

            if (! in_array($lastConsonant, ['w', 'x', 'y'], true)) {
                return $normalized . $lastConsonant . 'ing';
            }
        }

        return $normalized . 'ing';
    }
}
