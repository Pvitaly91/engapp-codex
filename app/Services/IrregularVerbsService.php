<?php

namespace App\Services;

use App\Models\Word;
use Illuminate\Support\Collection;

class IrregularVerbsService
{
    /**
     * Get all irregular verbs with their forms.
     * Returns array of verbs with structure:
     * [
     *   'base' => 'go',
     *   'translation' => 'йти / їхати',
     *   'f1' => 'goes',  // 3rd person singular
     *   'f2' => ['went'],  // past (can be multiple)
     *   'f3' => ['gone'],  // participle (can be multiple)
     *   'f4' => 'going'  // -ing form
     * ]
     */
    public function getIrregularVerbs(): array
    {
        // Fetch all verb forms from database
        $words = Word::with('translate')
            ->whereIn('type', ['base', 'past', 'participle'])
            ->get()
            ->filter(function ($word) {
                return $word->translate && $word->translate->translation;
            });

        // Group by translation (Ukrainian)
        $grouped = $words->groupBy(function ($word) {
            return $word->translate->translation;
        });

        $verbs = [];

        foreach ($grouped as $translation => $forms) {
            // Get base, past, and participle forms
            $baseForms = $forms->where('type', 'base')->pluck('word')->toArray();
            $pastForms = $forms->where('type', 'past')->pluck('word')->unique()->values()->toArray();
            $participleForms = $forms->where('type', 'participle')->pluck('word')->unique()->values()->toArray();

            // Skip if missing any essential form
            if (empty($baseForms) || empty($pastForms) || empty($participleForms)) {
                continue;
            }

            // Use first base form as primary
            $base = $baseForms[0];

            $verbs[] = [
                'base' => $base,
                'translation' => $translation,
                'f1' => $this->getThirdPersonSingular($base),
                'f2' => $pastForms,
                'f3' => $participleForms,
                'f4' => $this->getIngForm($base),
            ];
        }

        return $verbs;
    }

    /**
     * Get third person singular form (he/she/it)
     */
    private function getThirdPersonSingular(string $base): string
    {
        // Special cases
        $exceptions = [
            'be' => 'is',
            'have' => 'has',
            'do' => 'does',
            'go' => 'goes',
        ];

        if (isset($exceptions[$base])) {
            return $exceptions[$base];
        }

        // Rules for regular forms
        if (preg_match('/(s|x|z|ch|sh)$/i', $base)) {
            // add 'es' for s, x, z, ch, sh
            return $base . 'es';
        } elseif (preg_match('/[^aeiou]y$/i', $base)) {
            // consonant + y -> ies
            return substr($base, 0, -1) . 'ies';
        } else {
            // default: add 's'
            return $base . 's';
        }
    }

    /**
     * Get -ing form
     */
    private function getIngForm(string $base): string
    {
        // Special cases
        $exceptions = [
            'be' => 'being',
            'lie' => 'lying',
            'die' => 'dying',
            'tie' => 'tying',
        ];

        if (isset($exceptions[$base])) {
            return $exceptions[$base];
        }

        // Remove silent 'e' before adding 'ing'
        if (preg_match('/[^aeiou]e$/i', $base) && strlen($base) > 2) {
            return substr($base, 0, -1) . 'ing';
        }

        // Double consonant for CVC pattern (consonant-vowel-consonant)
        // but not for w, x, y endings
        if (strlen($base) >= 3 && 
            preg_match('/[^aeiou][aeiou][^aeiouxyw]$/i', $base)) {
            return $base . substr($base, -1) . 'ing';
        }

        // Default: just add 'ing'
        return $base . 'ing';
    }
}
