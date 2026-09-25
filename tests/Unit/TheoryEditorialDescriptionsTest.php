<?php

namespace Tests\Unit;

use App\Support\PageMetadata;
use App\Support\TheoryEditorialDescriptions;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TheoryEditorialDescriptionsTest extends TestCase
{
    public static function descriptions(): array
    {
        $cases = [];
        foreach (TheoryEditorialDescriptions::UK as $seeder => $description) {
            $cases[$seeder] = [$seeder, $description];
        }

        return $cases;
    }

    #[DataProvider('descriptions')]
    public function test_each_registered_identity_uses_its_exact_copy_without_changing_title(string $seeder, string $description): void
    {
        // The versioned definition establishes the page identity, not a guessed short URL slug.
        $path = base_path('database/seeders/'.str_replace('\\', '/', substr($seeder, strlen('Database\\Seeders\\'))).'/definition.json');
        $this->assertFileExists($path);
        $definition = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
        $name = $definition['page']['title'];
        $base = PageMetadata::theory($name, '', 'English introduction.');
        $actual = PageMetadata::theory($name, '', 'English introduction.', $seeder, 'uk');
        $this->assertSame($description, $actual['description']);
        $this->assertSame($base['title'], $actual['title']);
        $this->assertSame($description, PageMetadata::plain($description));
        $this->assertMatchesRegularExpression('/[а-яіїєґ]/ui', $description);
        $this->assertStringNotContainsString('Пояснення теми «', $description);
        $this->assertDoesNotMatchRegularExpression('/безкоштов|реєстраці|A1|A2|B1|B2|C1|C2|\{a\d+\}|<[^>]+>/u', $description);
        $this->assertNotSame($base['description'], $description);
    }

    public function test_intro_priority_unknown_identity_and_locale_isolation(): void
    {
        foreach (TheoryEditorialDescriptions::UK as $seeder => $description) {
            $intro = '<p>Український вступ має пріоритет.</p> <p>Наступне речення.</p>';
            $this->assertSame(PageMetadata::theory('Тема', '', $intro), PageMetadata::theory('Тема', '', $intro, $seeder, 'uk'));
            foreach (['en', 'pl', 'unknown'] as $locale) {
                $this->assertNull(TheoryEditorialDescriptions::forPageSeeder($seeder, $locale));
                $this->assertSame(PageMetadata::theory('Topic'), PageMetadata::theory('Topic', '', '', $seeder, $locale));
            }
            $this->assertNull(TheoryEditorialDescriptions::forPageSeeder(basename(str_replace('\\', '/', $seeder)), 'uk'));
        }
        $this->assertNull(TheoryEditorialDescriptions::forPageSeeder('unregistered', 'uk'));
        $this->assertSame('Пояснення теми «Unknown» в англійській граматиці.', PageMetadata::theory('Unknown', '', '', 'unregistered')['description']);
        $this->assertCount(count(TheoryEditorialDescriptions::UK), array_unique(TheoryEditorialDescriptions::UK));
    }

    public function test_similar_topics_keep_their_actual_distinguishing_content(): void
    {
        $prefix = 'Database\\Seeders\\Page_V3\\';
        $pairs = [
            ['SentenceStructure\\CleftSentencesBasicsTheorySeeder', 'контрастній інформації'],
            ['SentenceStructure\\CleftSentencesEmphasisTheorySeeder', 'When was it that'],
            ['PassiveVoice\\PassiveReportingStructuresTheorySeeder', 'to have + V3'],
            ['PassiveVoice\\ComplexPassiveImpersonalStyleTheorySeeder', 'to have been + V3'],
            ['PronounsDemonstratives\\PronounsDemonstrativesOneOnesTheorySeeder', 'однині та множині'],
            ['PronounsDemonstratives\\PronounsDemonstrativesReciprocalPronounsTheorySeeder', 'взаємну дію'],
        ];
        foreach ($pairs as [$identity, $meaning]) {
            $this->assertStringContainsString($meaning, TheoryEditorialDescriptions::forPageSeeder($prefix.$identity, 'uk'));
        }
        $this->assertStringNotContainsString('one another', TheoryEditorialDescriptions::UK[$prefix.'PronounsDemonstratives\\PronounsDemonstrativesReciprocalPronounsTheorySeeder']);
    }

    public function test_editorial_path_has_no_sql_or_session_dependency(): void
    {
        DB::enableQueryLog();
        DB::flushQueryLog();
        $before = session()->all();
        foreach (TheoryEditorialDescriptions::UK as $seeder => $description) {
            $this->assertSame($description, PageMetadata::theory('Stored title', '', '', $seeder, 'uk')['description']);
        }
        $this->assertSame([], DB::getQueryLog());
        $this->assertSame($before, session()->all());
        fwrite(STDOUT, "\nM7_EDITORIAL_COST ".json_encode(['calls' => count(TheoryEditorialDescriptions::UK), 'sql' => count(DB::getQueryLog())])."\n");
    }
}
