<?php

namespace App\Http\Controllers;

use App\Models\Word;
use Illuminate\Http\Request;

class WordSearchController extends Controller
{
    public function search(Request $request)
    {
        $query = trim((string) $request->query('q', ''));

        if (mb_strlen($query) < 2) {
            return response()->json([]);
        }

        $locale = strtolower((string) ($request->route('lang') ?: app()->getLocale()));
        $locale = $locale === 'ua' ? 'uk' : $locale;
        if (! in_array($locale, ['uk', 'en', 'pl'], true)) {
            $locale = 'uk';
        }

        $escapedQuery = addcslashes(mb_substr($query, 0, 64), '\\%_');

        $words = Word::query()
            ->with(['translates' => fn ($builder) => $builder
                ->where('lang', $locale)
                ->orderBy('id')])
            ->where('word', 'like', $escapedQuery . '%')
            ->orderBy('word')
            ->limit(10)
            ->get();

        $translations = $words
            ->map(fn (Word $word) => $word->translates->first()?->translation)
            ->filter(fn ($translation) => filled($translation))
            ->unique()
            ->values();

        $formsByTranslation = $translations->isEmpty()
            ? collect()
            : Word::query()
                ->join('translates', 'translates.word_id', '=', 'words.id')
                ->where('translates.lang', $locale)
                ->whereIn('translates.translation', $translations)
                ->whereNotNull('words.type')
                ->get([
                    'words.word',
                    'words.type',
                    'translates.translation',
                ])
                ->groupBy('translation')
                ->map(fn ($rows) => $rows
                    ->groupBy('type')
                    ->map(fn ($forms) => $forms->pluck('word')->unique()->values()->all())
                    ->all());

        $results = $words->map(function (Word $word) use ($formsByTranslation, $locale) {
            $translationModel = $word->translates->first();
            $translation = $translationModel?->translation;
            $translationLang = $translationModel?->lang ?? $locale;

            $forms = $word->type && filled($translation)
                ? ($formsByTranslation->get($translation, []))
                : [];

            if ($word->type) {
                if (isset($forms[$word->type])) {
                    $forms[$word->type] = array_values(array_filter(
                        $forms[$word->type],
                        fn ($form) => $form !== $word->word
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

        return response()
            ->json($results)
            ->setPublic()
            ->setMaxAge(300)
            ->setSharedMaxAge(300);
    }
}

