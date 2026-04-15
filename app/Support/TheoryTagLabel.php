<?php

namespace App\Support;

class TheoryTagLabel
{
    private const MAPS = [
        'pl' => [
            'Prepositions and Phrasal Verbs' => 'Przyimki i czasowniki frazowe',
            'Prepositions of Time' => 'Przyimki czasu',
            'Prepositions of Place' => 'Przyimki miejsca',
            'Prepositions of Movement' => 'Przyimki ruchu',
            'Dependent Prepositions' => 'Stałe połączenia z przyimkami',
            'Phrasal Verbs' => 'Czasowniki frazowe',
            'Phrasal Verbs Basics' => 'Podstawy czasowników frazowych',
            'Separable and Inseparable Phrasal Verbs' => 'Rozdzielalne i nierozdzielalne czasowniki frazowe',
            'Common Phrasal Verbs by Topic' => 'Popularne czasowniki frazowe według tematów',
            'Daily Routine' => 'Codzienna rutyna',
            'Work' => 'Praca',
            'Communication' => 'Komunikacja',
            'Adjective Preposition' => 'Przymiotnik + przyimek',
            'Verb Preposition' => 'Czasownik + przyimek',
            'Noun Preposition' => 'Rzeczownik + przyimek',
            'Verb Particle' => 'Czasownik + partykuła',
            'Object Position' => 'Pozycja dopełnienia',
            'Pronoun Placement' => 'Miejsce zaimka',
            'Movement' => 'Ruch',
            'Direction' => 'Kierunek',
            'Location' => 'Położenie',
            'Time Expressions' => 'Wyrażenia czasu',
            'Theory' => 'Teoria',
            'At On In' => 'At / On / In',
            'In On At' => 'In / On / At',
        ],
    ];

    public static function display(?string $value, ?string $locale = null): string
    {
        $label = trim((string) $value);

        if ($label === '') {
            return '';
        }

        $locale = trim((string) ($locale ?: app()->getLocale()));

        return self::MAPS[$locale][$label] ?? $label;
    }
}
