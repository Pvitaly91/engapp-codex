<?php

namespace App\Http\Controllers;

use App\Models\Word;
use Illuminate\Http\Request;

class WordSearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->query('q', '');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $locale = app()->getLocale();

        $words = Word::with('translate')
            ->where('word', 'like', $query . '%')
            ->orderBy('word')
            ->limit(10)
            ->get();

        $results = $words->map(function ($word) use ($locale) {
            $translation = optional($word->translate)->translation;
            $translationLang = optional($word->translate)->lang ?? $locale;

            $forms = [];
            if ($word->type) {
                $forms = Word::whereHas('translate', function ($q) use ($translation, $locale) {
                        $q->where('lang', $locale)->where('translation', $translation);
                    })
                    ->get()
                    ->groupBy('type')
                    ->map(fn($group) => $group->pluck('word')->all())
                    ->toArray();

                if (isset($forms[$word->type])) {
                    $forms[$word->type] = array_values(array_filter(
                        $forms[$word->type],
                        fn($w) => $w !== $word->word
                    ));
                    if (empty($forms[$word->type])) {
                        unset($forms[$word->type]);
                    }
                }
            }

            return [
                'word' => $word->word,
                'translation' => $translation,
                'translation_lang' => $translationLang,
                'forms' => [
                    'base' => $forms['base'] ?? [],
                    'past' => $forms['past'] ?? [],
                    'participle' => $forms['participle'] ?? [],
                ],
            ];
        })->values();

        return response()->json($results);
    }
}

