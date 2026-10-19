<?php

namespace Tests\Feature;

use App\Http\Controllers\PageController;
use App\Models\Page;
use App\Models\PageCategory;
use App\Services\TheoryCourseManifestService;
use App\Support\Database\JsonPageSeeder;
use App\Support\PageMetadata;
use App\Support\TheoryPageTestSlug;
use App\Support\TheoryRichContent;
use DOMDocument;
use DOMXPath;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class AuthoredRevisionContentPackageTest extends TestCase
{
    use RebuildsComposeTestSchema;

    public static function lessons(): array
    {
        return [
            'nominal-style' => ['nominal-style', 'FormalEnglish/NominalStyleAndInformationDensityTheorySeeder'],
            'c1-review' => ['c1-review', 'BasicGrammar/C1MixedRevisionTheorySeeder'],
            'c2-review' => ['c2-review', 'BasicGrammar/C2MixedRevisionTheorySeeder'],
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->rebuildComposeTestSchema();
        $this->withoutVite();
        app()->setLocale('uk');
        PageCategory::create(['title' => 'Formal English', 'slug' => 'formal-english', 'language' => 'uk', 'type' => 'theory']);
        PageCategory::create(['title' => 'Mixed Revision', 'slug' => 'mixed-revision', 'language' => 'uk', 'type' => 'theory']);
    }

    private function master(string $key): array
    {
        $path = base_path('docs/content/m23-authored-content.v1.json');
        self::assertSame('eae0e632ae528e3500eeb40188d4b61d275703bfe722cd2fa9fe495f1d2b1ff6', hash_file('sha256', $path));
        $master = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);

        return collect($master['lessons'])->firstWhere('key', $key);
    }

    private function definition(string $name): array
    {
        return json_decode(file_get_contents(database_path('seeders/Page_V3/'.$name.'/definition.json')), true, flags: JSON_THROW_ON_ERROR);
    }

    private function before(string $seeder): array
    {
        $data = json_decode(file_get_contents(database_path('content-patches/m23-authored-revision-before.json')), true, flags: JSON_THROW_ON_ERROR);

        return $data['definitions'][$seeder];
    }

    private function dom(string $html): DOMXPath
    {
        $document = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        try {
            $document->loadHTML('<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body>'.$html.'</body></html>', LIBXML_NONET);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }

        return new DOMXPath($document);
    }

    #[DataProvider('lessons')]
    public function test_author_master_maps_to_exact_fields_without_changing_identity(string $key, string $name): void
    {
        $lesson = $this->master($key);
        $source = $this->definition($name);
        $before = $this->before($lesson['seeder']);
        self::assertSame($lesson['seeder'], $source['seeder']['class']);
        self::assertSame($lesson['slug'], $source['slug']);
        self::assertSame($lesson['preserve_page_title'], $source['page']['title']);
        self::assertSame($lesson['category_path'][0], $source['page']['category']['slug']);
        self::assertSame($lesson['locale'], $source['page']['locale']);
        self::assertSame($lesson['level'], $source['page']['blocks'][0]['level']);
        self::assertSame($lesson['subtitle_html'], $source['page']['subtitle_html']);
        self::assertSame($lesson['subtitle_text'], $source['page']['subtitle_text']);
        self::assertSame($lesson['hero'], json_decode($source['page']['blocks'][0]['body'], true, flags: JSON_THROW_ON_ERROR));
        self::assertSame($lesson['box_heading'], $source['page']['blocks'][1]['heading']);
        self::assertSame($lesson['body_html'], $source['page']['blocks'][1]['body']);
        self::assertSame($lesson['body_sha256'], hash('sha256', $source['page']['blocks'][1]['body']));
        $immutable = static function (array $value): array {
            unset($value['page']['subtitle_html'], $value['page']['subtitle_text']);
            foreach ($value['page']['blocks'] as &$block) {
                unset($block['heading'], $block['body']);
            }
            unset($block);

            return $value;
        };
        self::assertSame($immutable($before), $immutable($source));
        self::assertSame(['hero', 'box'], array_column($source['page']['blocks'], 'type'));
        self::assertNotNull(TheoryRichContent::render($lesson['body_html']));
        $xpath = $this->dom($lesson['body_html']);
        $section = '//section[@id="self-check-'.$lesson['slug'].'"]';
        self::assertSame(1, $xpath->query($section)->length);
        self::assertSame(6, $xpath->query($section.'/ol[1]/li')->length);
        self::assertSame(6, $xpath->query($section.'/details/ol[1]/li')->length);
        self::assertSame($key === 'c2-review' ? 0 : 1, $xpath->query('//table')->length);
        self::assertSame(0, $xpath->query('//script | //iframe | //*[@onclick]')->length);
        self::assertSame($lesson['expected_metadata']['title'], PageMetadata::theory($lesson['preserve_page_title'], '', $lesson['hero']['intro'], $lesson['seeder'], 'uk')['title']);
        self::assertSame($lesson['expected_metadata']['description'], PageMetadata::theory($lesson['preserve_page_title'], '', $lesson['hero']['intro'], $lesson['seeder'], 'uk')['description']);
        foreach ($xpath->query('//a[@href]') as $link) {
            $href = $link->getAttribute('href');
            if (str_starts_with($href, '/theory/')) {
                self::assertSame('theory.show', app('router')->getRoutes()->match(Request::create('http://gramlyze.loc'.$href))->getName());
            } else {
                self::assertTrue(str_starts_with($href, 'https://'));
            }
        }
    }

    #[DataProvider('lessons')]
    public function test_isolated_import_preserves_controller_h1_routes_and_existing_banks(string $key, string $name): void
    {
        $lesson = $this->master($key);
        $path = database_path('seeders/Page_V3/'.$name.'/definition.json');
        (new class($path) extends JsonPageSeeder
        {
            public function __construct(private readonly string $path) {}

            protected function definitionPath(): string
            {
                return $this->path;
            }
        })->run();
        $page = Page::with('category', 'textBlocks')->where('seeder', $lesson['seeder'])->sole();
        $controller = (new \ReflectionClass(PageController::class))->newInstanceWithoutConstructor();
        $titleExtractor = new \ReflectionMethod($controller, 'extractLocalizedTitleFromSubtitle');
        $before = $this->before($lesson['seeder']);
        self::assertSame($lesson['preserve_subtitle_strong'], $titleExtractor->invoke($controller, $lesson['subtitle_html']));
        self::assertSame($titleExtractor->invoke($controller, $before['page']['subtitle_html']), $titleExtractor->invoke($controller, $lesson['subtitle_html']));
        self::assertSame($lesson['preserve_page_title'], $page->title);
        self::assertSame($lesson['subtitle_text'], $page->text);
        self::assertSame($lesson['category_path'][0], $page->category->slug);
        self::assertNull($page->category->parent_id);
        self::assertSame(3, $page->textBlocks->where('locale', 'uk')->count());
        self::assertSame(0, DB::table('questions')->count());
        self::assertSame(0, DB::table('saved_grammar_tests')->count());
        $testSlug = TheoryPageTestSlug::forPage($page);
        self::assertSame($lesson['category_path'][0].'/'.$lesson['slug'], $testSlug);
        self::assertSame('test.show', app('router')->getRoutes()->match(Request::create('http://gramlyze.loc/test/'.$testSlug))->getName());
        $manifest = app(TheoryCourseManifestService::class);
        self::assertSame($lesson['theory_path'], $manifest->theoryUrl($lesson['category_path'][0], $lesson['slug']));
        self::assertSame('/courses/english-grammar-theory/lesson/'.$lesson['category_path'][0].'/'.$lesson['slug'], $manifest->lessonUrl($lesson['category_path'][0], $lesson['slug']));
        $rendered = (string) TheoryRichContent::render($lesson['body_html']);
        self::assertSame(6, $this->dom($rendered)->query('//section[@id="self-check-'.$lesson['slug'].'"]/ol[1]/li')->length);
        self::assertSame(6, $this->dom($rendered)->query('//section[@id="self-check-'.$lesson['slug'].'"]/details/ol[1]/li')->length);
    }
}
