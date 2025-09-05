<?php

namespace App\Http\Controllers;

use App\Models\Word;
use Illuminate\Http\Request;

class IrregularVerbController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->query('q', '');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $bases = Word::with('translate')
            ->where('type', 'base')
            ->where('word', 'like', $query . '%')
            ->get();

        $results = $bases->map(function ($base) {
            $translation = optional($base->translate)->translation;

            $forms = Word::whereHas('translate', function ($q) use ($translation) {
                    $q->where('lang', 'uk')->where('translation', $translation);
                })
                ->get()
                ->groupBy('type')
                ->map(fn($group) => $group->pluck('word')->all());

            return [
                'base' => $base->word,
                'past' => $forms['past'] ?? [],
                'participle' => $forms['participle'] ?? [],
            ];
        })->values();

        return response()->json($results);
    }
}

