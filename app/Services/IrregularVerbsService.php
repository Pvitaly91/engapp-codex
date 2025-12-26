<?php

namespace App\Services;

use App\Models\Word;
use Illuminate\Support\Collection;

class IrregularVerbsService
{
    /**
     * Exceptions for third person singular (Form 1).
     * base form => third person singular
     */
    private const F1_EXCEPTIONS = [
        'have' => 'has',
        'do' => 'does',
        'go' => 'goes',
        'be' => 'is',
    ];

    /**
     * Exceptions for -ing form (Form 4).
     * base form => -ing form
     */
    private const F4_EXCEPTIONS = [
        'be' => 'being',
        'lie' => 'lying',
        'die' => 'dying',
        'tie' => 'tying',
        'see' => 'seeing',
        'flee' => 'fleeing',
        'agree' => 'agreeing',
        'free' => 'freeing',
    ];

    /**
     * Get all irregular verbs formatted for the test.
     *
     * @param string $lang Translation language (default: uk)
     * @return Collection
     */
    public function getVerbs(string $lang = 'uk'): Collection
    {
        // Fetch all verb forms from DB
        $words = Word::query()
            ->whereIn('type', ['base', 'past', 'participle'])
            ->with(['translates' => fn ($q) => $q->where('lang', $lang)])
            ->get();

        // Group by translation
        $grouped = $words
            ->filter(fn ($word) => $word->translates->isNotEmpty() && !empty($word->translates->first()->translation))
            ->groupBy(fn ($word) => trim($word->translates->first()->translation));

        // Build verb cards
        $verbs = $grouped->map(function ($group, $translation) {
            $forms = [
                'base' => [],
                'past' => [],
                'participle' => [],
            ];

            foreach ($group as $word) {
                $type = $word->type;
                if (isset($forms[$type])) {
                    $forms[$type][] = $word->word;
                }
            }

            // Filter out groups without all forms
            if (empty($forms['base']) || empty($forms['past']) || empty($forms['participle'])) {
                return null;
            }

            $base = $forms['base'][0]; // Use first base form

            return [
                'base' => $base,
                'translation' => $translation,
                'f1' => $this->generateF1($base),
                'f2' => array_values(array_unique($forms['past'])),
                'f3' => array_values(array_unique($forms['participle'])),
                'f4' => $this->generateF4($base),
            ];
        })
        ->filter()
        ->values();

        return $verbs;
    }

    /**
     * Generate third person singular form (Form 1).
     *
     * Rules:
     * - Exceptions first (have -> has, do -> does, go -> goes, be -> is)
     * - Words ending in s, ss, sh, ch, x, z, o: add -es
     * - Words ending in consonant + y: change y to -ies
     * - All other words: add -s
     */
    public function generateF1(string $base): string
    {
        $base = strtolower(trim($base));

        // Check exceptions first
        if (isset(self::F1_EXCEPTIONS[$base])) {
            return self::F1_EXCEPTIONS[$base];
        }

        // Words ending in s, ss, sh, ch, x, z, o: add -es
        if (preg_match('/(s|ss|sh|ch|x|z|o)$/', $base)) {
            return $base . 'es';
        }

        // Words ending in consonant + y: change y to -ies
        if (preg_match('/[bcdfghjklmnpqrstvwxyz]y$/', $base)) {
            return substr($base, 0, -1) . 'ies';
        }

        // Default: add -s
        return $base . 's';
    }

    /**
     * Generate -ing form (Form 4).
     *
     * Rules:
     * - Exceptions first (be -> being, lie -> lying, etc.)
     * - Words ending in -ie: change to -ying
     * - Words ending in -ee: add -ing
     * - Words ending in consonant + e: drop e, add -ing
     * - Words ending in single vowel + single consonant (except w, x, y): double consonant, add -ing
     * - All other words: add -ing
     */
    public function generateF4(string $base): string
    {
        $base = strtolower(trim($base));

        // Check exceptions first
        if (isset(self::F4_EXCEPTIONS[$base])) {
            return self::F4_EXCEPTIONS[$base];
        }

        // Words ending in -ie: change to -ying (except exceptions above)
        if (preg_match('/ie$/', $base)) {
            return substr($base, 0, -2) . 'ying';
        }

        // Words ending in -ee, -ye, -oe: keep e, add -ing
        if (preg_match('/(ee|ye|oe)$/', $base)) {
            return $base . 'ing';
        }

        // Words ending in consonant + e: drop e, add -ing
        if (preg_match('/[bcdfghjklmnpqrstvwxyz]e$/', $base)) {
            return substr($base, 0, -1) . 'ing';
        }

        // Words ending in single vowel + single consonant (except w, x, y):
        // For common short verbs, double the consonant
        // This is a simplified rule - doesn't handle all cases perfectly
        if (preg_match('/^[a-z]{2,4}$/', $base) && preg_match('/[aeiou][bcdfghjklmnpqrstvz]$/', $base)) {
            return $base . substr($base, -1) . 'ing';
        }

        // Default: add -ing
        return $base . 'ing';
    }
}
