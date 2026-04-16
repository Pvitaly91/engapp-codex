<?php

namespace App\Support;

class TheoryTagLabel
{
    private const MAPS = [
        'uk' => [
            'basic-grammar' => 'Базова граматика',
            'imennyky-artykli-ta-kilkist' => 'Іменники, артиклі й кількість',
            'zaimennyky-ta-vkazivni-slova' => 'Займенники та вказівні слова',
            'maibutni-formy' => 'Майбутні форми',
            'pytalni-rechennia-ta-zaperechennia' => 'Питальні речення та заперечення',
            'prykmetniky-ta-pryslinknyky' => 'Прикметники та прислівники',
            'some-any' => 'Some / Any',
            'tenses' => 'Часи',
            'passive-voice' => 'Пасивний стан',
            'modal-verbs' => 'Модальні дієслова',
            'conditionals' => 'Умовні речення',
            'reported-speech' => 'Непряма мова',
            'clauses-and-linking-words' => "Підрядні речення та слова-зв'язки",
            'prepositions-and-phrasal-verbs' => 'Прийменники та фразові дієслова',
            'common-mistakes' => 'Типові помилки',
            'sentence-transformations' => 'Трансформації речень',
            'verb-patterns' => 'Дієслівні моделі',
        ],
        'en' => [
            'basic-grammar' => 'Basic Grammar',
            'imennyky-artykli-ta-kilkist' => 'Nouns / Articles / Quantity',
            'zaimennyky-ta-vkazivni-slova' => 'Pronouns / Demonstratives',
            'maibutni-formy' => 'Future Forms',
            'pytalni-rechennia-ta-zaperechennia' => 'Questions and Negations',
            'prykmetniky-ta-pryslinknyky' => 'Adjectives and Adverbs',
            'some-any' => 'Some / Any',
            'tenses' => 'Tenses',
            'passive-voice' => 'Passive Voice',
            'modal-verbs' => 'Modal Verbs',
            'conditionals' => 'Conditionals',
            'reported-speech' => 'Reported Speech',
            'clauses-and-linking-words' => 'Clauses and Linking Words',
            'prepositions-and-phrasal-verbs' => 'Prepositions and Phrasal Verbs',
            'common-mistakes' => 'Common Mistakes',
            'sentence-transformations' => 'Sentence Transformations',
            'verb-patterns' => 'Verb Patterns',
        ],
        'pl' => [
            'basic-grammar' => 'Podstawy gramatyki',
            'imennyky-artykli-ta-kilkist' => 'Rzeczowniki / Przedimki / Ilość',
            'zaimennyky-ta-vkazivni-slova' => 'Zaimki i słowa wskazujące',
            'maibutni-formy' => 'Formy przyszłości',
            'pytalni-rechennia-ta-zaperechennia' => 'Pytania i przeczenia',
            'prykmetniky-ta-pryslinknyky' => 'Przymiotniki i przysłówki',
            'some-any' => 'Some / Any',
            'tenses' => 'Czasy',
            'passive-voice' => 'Strona bierna',
            'modal-verbs' => 'Czasowniki modalne',
            'conditionals' => 'Okresy warunkowe',
            'reported-speech' => 'Mowa zależna',
            'clauses-and-linking-words' => 'Zdania podrzędne i wyrazy łączące',
            'prepositions-and-phrasal-verbs' => 'Przyimki i czasowniki frazowe',
            'common-mistakes' => 'Typowe błędy',
            'sentence-transformations' => 'Transformacje zdań',
            'verb-patterns' => 'Schematy czasownikowe',
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
