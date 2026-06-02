# Sentence Structure Theory Page Tests 062

This checkpoint covers four Sentence Structure theory pages:

- `/theory/sentence-structure/cleft-sentences-basics`
- `/theory/sentence-structure/cleft-sentences-emphasis`
- `/theory/sentence-structure/ellipsis-substitution-and-reference`
- `/theory/sentence-structure/complex-noun-phrases`

Real slugs are `cleft-sentences-basics`, `cleft-sentences-emphasis`, `ellipsis-substitution-and-reference`, and `complex-noun-phrases`.

## Category State

No standalone Page_V3 `SentenceStructure` category seeder exists in this repo. The feature test seeds only the real page seeders, and those pages define/use the `sentence-structure` category directly.

## Seeders

Standard V3 question seeders:

- `Database\Seeders\V3\SentenceStructure\CleftSentencesBasicsAllLevelsV3Seeder`
- `Database\Seeders\V3\SentenceStructure\CleftSentencesEmphasisAllLevelsV3Seeder`
- `Database\Seeders\V3\SentenceStructure\EllipsisSubstitutionAndReferenceAllLevelsV3Seeder`
- `Database\Seeders\V3\SentenceStructure\ComplexNounPhrasesAllLevelsV3Seeder`

Sentence Builder / Polyglot seeders:

- `Database\Seeders\V3\Polyglot\PolyglotCleftSentencesBasicsAllLevelsLessonSeeder`
- `Database\Seeders\V3\Polyglot\PolyglotCleftSentencesEmphasisAllLevelsLessonSeeder`
- `Database\Seeders\V3\Polyglot\PolyglotEllipsisSubstitutionAndReferenceAllLevelsLessonSeeder`
- `Database\Seeders\V3\Polyglot\PolyglotComplexNounPhrasesAllLevelsLessonSeeder`

Each new seeder has 72 questions, with 12 questions per A1, A2, B1, B2, C1, and C2 level.

## Theory Links

Theory links are JSON manifest driven from:

- `database/seeders/V3/TheoryLinks/data/sentence-structure-cleft-sentences-basics-theory-links.json`
- `database/seeders/V3/TheoryLinks/data/sentence-structure-cleft-sentences-emphasis-theory-links.json`
- `database/seeders/V3/TheoryLinks/data/sentence-structure-ellipsis-substitution-and-reference-theory-links.json`
- `database/seeders/V3/TheoryLinks/data/sentence-structure-complex-noun-phrases-theory-links.json`

The thin PHP seeders extend `App\Support\Database\JsonTheoryLinksSeederBase` and only define `manifestPath()`.

## QA Commands

```bash
php artisan test tests/Feature/SentenceStructureTheoryPageTestsSeedersTest.php
php artisan test tests/Feature/PolyglotUkrainianSourceLanguageTest.php
php artisan test tests/Feature/PolyglotComposeModeTest.php
php artisan theory-pages:audit-tests-unification
```

Manual URLs:

- `http://engapp-codex.loc/theory/sentence-structure/cleft-sentences-basics`
- `http://engapp-codex.loc/theory/sentence-structure/cleft-sentences-emphasis`
- `http://engapp-codex.loc/theory/sentence-structure/ellipsis-substitution-and-reference`
- `http://engapp-codex.loc/theory/sentence-structure/complex-noun-phrases`

Check that direct Sentence Builder and Mixed A1-C2 tests show populated `Показати теорію` blocks, direct questions are compose mode, mixed tests contain V3 and compose questions, and Ukrainian prompts remain natural Ukrainian while English tokens/options remain English.
