<?php

namespace Tests\Feature;

use App\Http\Controllers\PageController;
use App\Models\Page;
use App\Models\PageCategory;
use App\Services\TheoryCourseManifestService;
use App\Support\Database\JsonPageSeeder;
use App\Support\PageMetadata;
use App\Support\M24NativeTitleNumber;
use App\Support\M40TensesB1Package;
use App\Support\TextBlock\TextBlockUuidGenerator;
use App\Support\TheoryPageTestSlug;
use App\Support\TheoryRichContent;
use DOMDocument;
use DOMXPath;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class AuthoredNativeContentPackageTest extends TestCase
{
    use RebuildsComposeTestSchema;

    public static function lessons(): array
    {
        return [
            'perfect-comparison' => ['perfect-comparison', 'Tenses/TensesPresentPerfectVsPresentPerfectContinuousTheorySeeder'],
            'narrative-tenses' => ['narrative-tenses', 'Tenses/TensesNarrativeTensesTheorySeeder'],
            'b1-mixed-revision' => ['b1-mixed-revision', 'BasicGrammar/BasicGrammarB1MixedRevisionTheorySeeder'],
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->rebuildComposeTestSchema();
        $this->withoutVite();
        app()->setLocale('uk');
        PageCategory::create(['title' => 'Tenses', 'slug' => 'tenses', 'language' => 'uk', 'type' => 'theory']);
        PageCategory::create(['title' => 'Mixed Revision', 'slug' => 'mixed-revision', 'language' => 'uk', 'type' => 'theory']);
    }

    private function master(string $key): array
    {
        $path = base_path('docs/content/m24-authored-content.v1.json');
        $bytes = str_replace("\r\n", "\n", file_get_contents($path));
        self::assertSame('33373412ed077bf7fa0aea8dde8a6d2c211c126288b6cedfdde299ded43bf5a2', hash('sha256', $bytes));
        self::assertSame('2d7c450533795bdab3267bd029b54fb325ff7cbb', sha1('blob '.strlen($bytes)."\0".$bytes));
        return collect(json_decode($bytes, true, flags: JSON_THROW_ON_ERROR)['lessons'])->firstWhere('key', $key);
    }

    private function acceptedM24Definition(array $lesson, string $name): array
    {
        [$before, $projection] = M40TensesB1Package::load(); M40TensesB1Package::validate($before, $projection);
        $target = collect($projection['targets'])->firstWhere('identity', $lesson['seeder']);
        $original = collect($before['targets'])->firstWhere('path', $target['path'])['before'];
        self::assertSame('database/seeders/Page_V3/'.$name.'/definition.json', $target['path']);
        self::assertSame($target['after'], json_decode(file_get_contents(base_path($target['path'])), true, flags: JSON_THROW_ON_ERROR));
        return $original;
    }

    private function dom(string $html): DOMXPath
    {
        $document = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        try { $document->loadHTML('<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body>'.$html.'</body></html>', LIBXML_NONET); }
        finally { libxml_clear_errors(); libxml_use_internal_errors($previous); }
        return new DOMXPath($document);
    }

    public function test_m24_native_number_badge_is_limited_to_three_authored_pages(): void
    {
        foreach (self::lessons() as [$key]) {
            $lesson = $this->master($key);
            $block = (object) ['seeder' => $lesson['seeder']];
            foreach ($lesson['existing_blocks'] as $item) {
                if (!in_array($item['preserve_type'], ['summary-list', 'mistakes-grid'], true)) { continue; }
                $title = $item['replacement_body_json']['title'];
                preg_match('/^(\d+)\.\s*/u', $title, $match);
                self::assertSame($match[1], M24NativeTitleNumber::forBlock($block, $title));
            }
        }
        self::assertNull(M24NativeTitleNumber::forBlock((object) ['seeder' => 'OtherSeeder'], '4. A protected lesson'));
        self::assertNull(M24NativeTitleNumber::forBlock((object) ['seeder' => $this->master('b1-mixed-revision')['seeder']], 'No number'));
    }

    #[DataProvider('lessons')]
    public function test_author_master_maps_to_native_blocks_and_only_one_new_box(string $key, string $name): void
    {
        $lesson = $this->master($key);
        $source = $this->acceptedM24Definition($lesson, $name);
        $manifest = json_decode(file_get_contents(database_path('content-patches/m24-authored-native-before.json')), true, flags: JSON_THROW_ON_ERROR);
        $before = $manifest['definitions'][$lesson['seeder']];
        self::assertSame($lesson['seeder'], $source['seeder']['class']);
        self::assertSame($lesson['slug'], $source['slug']);
        self::assertSame($lesson['preserve_page_title'], $source['page']['title']);
        self::assertSame($lesson['category_path'][0], $source['page']['category']['slug']);
        self::assertSame('uk', $source['page']['locale']);
        self::assertSame($lesson['subtitle_html'], $source['page']['subtitle_html']);
        self::assertSame($lesson['subtitle_text'], $source['page']['subtitle_text']);
        self::assertCount(count($before['page']['blocks']) + 1, $source['page']['blocks']);
        $reverted = $source;
        $reverted['page']['subtitle_html'] = $before['page']['subtitle_html'];
        $reverted['page']['subtitle_text'] = $before['page']['subtitle_text'];
        foreach ($lesson['existing_blocks'] as $block) {
            $i = $block['source_index'];
            self::assertSame($block['preserve_type'], $source['page']['blocks'][$i]['type']);
            self::assertSame($block['preserve_column'], $source['page']['blocks'][$i]['column']);
            self::assertSame($block['preserve_level'], $source['page']['blocks'][$i]['level']);
            self::assertSame($block['replacement_body_json'], json_decode($source['page']['blocks'][$i]['body'], true, flags: JSON_THROW_ON_ERROR));
            $reverted['page']['blocks'][$i]['body'] = $before['page']['blocks'][$i]['body'];
        }
        $box = $lesson['append_blocks'][0];
        $actual = $source['page']['blocks'][$box['source_index']];
        self::assertSame(['type' => 'box', 'column' => $box['column'], 'heading' => $box['heading'],
            'level' => $box['level'], 'body' => $box['body_html'], 'uuid_key' => 'm24-authored-practice',
            'inherit_base_tags' => false, 'tags' => []], $actual);
        self::assertSame($box['body_sha256'], hash('sha256', $actual['body']));
        array_pop($reverted['page']['blocks']);
        self::assertSame($before, $reverted);
        self::assertNotNull(TheoryRichContent::render($box['body_html']));
        $xpath = $this->dom($box['body_html']);
        $section = '//section[@id="self-check-m24-'.$key.'"]';
        self::assertSame(1, $xpath->query($section)->length);
        self::assertSame(6, $xpath->query($section.'/ol[1]/li')->length);
        self::assertSame(6, $xpath->query($section.'/details/ol[1]/li')->length);
        self::assertSame(0, $xpath->query('//script | //iframe | //*[@onclick]')->length);
        $hero = $lesson['existing_blocks'][0]['replacement_body_json'];
        $metadata = PageMetadata::theory($lesson['preserve_page_title'], '', $hero['intro'], $lesson['seeder'], 'uk');
        self::assertSame($lesson['expected_metadata']['title'], $metadata['title']);
        self::assertSame($lesson['expected_metadata']['description'], $metadata['description']);
    }

    #[DataProvider('lessons')]
    public function test_isolated_seeder_layout_uuid_h1_routes_and_banks(string $key, string $name): void
    {
        $lesson = $this->master($key);
        $source = $this->acceptedM24Definition($lesson, $name);
        // Fixture only: the working DB never runs this destructive full seeder.
        (new class extends JsonPageSeeder
        {
            protected function definitionPath(): string { return ''; }
            public function fixture(array $source): void { $this->seedDefinition($source, $this->resolveSeederClassName($source)); }
        })->fixture($source);
        $page = Page::with('category', 'textBlocks')->where('seeder', $lesson['seeder'])->sole();
        $controller = (new \ReflectionClass(PageController::class))->newInstanceWithoutConstructor();
        $extract = new \ReflectionMethod($controller, 'extractLocalizedTitleFromSubtitle');
        self::assertSame($lesson['preserve_subtitle_strong'], $extract->invoke($controller, $lesson['subtitle_html']));
        self::assertSame($lesson['preserve_page_title'], $page->title);
        self::assertSame($lesson['subtitle_text'], $page->text);
        self::assertSame($lesson['category_path'][0], $page->category->slug);
        self::assertNull($page->category->parent_id);
        $uk = $page->textBlocks->where('locale', 'uk')->sortBy('sort_order')->values();
        self::assertCount(count($lesson['existing_blocks']) + 2, $uk);
        $box = $uk->last();
        self::assertSame('box', $box->type);
        self::assertSame(count($lesson['existing_blocks']) + 1, $box->sort_order);
        self::assertSame(TextBlockUuidGenerator::generateWithKey($lesson['seeder'].'::uk', 'm24-authored-practice'), $box->uuid);
        self::assertSame(0, DB::table('tag_text_block')->where('text_block_id', $box->id)->count());
        self::assertSame(0, DB::table('questions')->count());
        self::assertSame(0, DB::table('saved_grammar_tests')->count());
        $testSlug = TheoryPageTestSlug::forPage($page);
        self::assertSame($lesson['category_path'][0].'/'.$lesson['slug'], $testSlug);
        self::assertSame('test.show', app('router')->getRoutes()->match(Request::create('http://gramlyze.loc/test/'.$testSlug))->getName());
        $manifest = app(TheoryCourseManifestService::class);
        self::assertSame($lesson['theory_path'], $manifest->theoryUrl($lesson['category_path'][0], $lesson['slug']));
        self::assertSame('/courses/english-grammar-theory/lesson/'.$lesson['category_path'][0].'/'.$lesson['slug'],
            $manifest->lessonUrl($lesson['category_path'][0], $lesson['slug']));
        $rendered = (string) TheoryRichContent::render($lesson['append_blocks'][0]['body_html']);
        self::assertSame(6, $this->dom($rendered)->query('//section[@id="self-check-m24-'.$key.'"]/ol[1]/li')->length);
        self::assertSame(6, $this->dom($rendered)->query('//section[@id="self-check-m24-'.$key.'"]/details/ol[1]/li')->length);
    }
}
