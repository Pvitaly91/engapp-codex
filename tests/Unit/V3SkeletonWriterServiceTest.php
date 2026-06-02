<?php

namespace Tests\Unit;

use App\Services\V3PromptGenerator\V3SeederBlueprintService;
use App\Services\V3PromptGenerator\V3SkeletonWriterService;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class V3SkeletonWriterServiceTest extends TestCase
{
    /**
     * @var array<int, string>
     */
    private array $cleanupPaths = [];

    protected function tearDown(): void
    {
        foreach (array_reverse($this->cleanupPaths) as $path) {
            if (File::isDirectory($path)) {
                File::deleteDirectory($path);
                continue;
            }

            File::delete($path);
        }

        parent::tearDown();
    }

    public function test_planned_files_follow_preview_contract(): void
    {
        $generated = $this->makeGenerated(
            topic: 'Unit Writer Planned Files',
            namespace: 'Tests\\CodexUnit\\V3Planned',
        );

        $planned = app(V3SkeletonWriterService::class)->plannedFiles($generated);

        $this->assertSame([
            $generated['preview']['seeder_absolute_path'],
            $generated['preview']['real_seeder_absolute_path'],
            $generated['preview']['definition_absolute_path'],
            $generated['preview']['localization_uk_absolute_path'],
            $generated['preview']['localization_en_absolute_path'],
            $generated['preview']['localization_pl_absolute_path'],
        ], $planned);
    }

    public function test_write_creates_fill_ready_v3_scaffold_with_localization_targeting(): void
    {
        $generated = $this->makeGenerated(
            topic: 'Unit Writer Content',
            namespace: 'Tests\\CodexUnit\\V3Content',
        );

        $written = app(V3SkeletonWriterService::class)->write($generated, true);
        $definition = json_decode(File::get($generated['preview']['definition_absolute_path']), true, 512, JSON_THROW_ON_ERROR);
        $localization = json_decode(File::get($generated['preview']['localization_en_absolute_path']), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(6, $written['count']);
        $this->assertFileExists($generated['preview']['seeder_absolute_path']);
        $this->assertFileExists($generated['preview']['real_seeder_absolute_path']);
        $this->assertSame([], $definition['questions']);
        $this->assertSame([], $definition['saved_test']['question_uuids']);
        $this->assertSame(8, $definition['saved_test']['filters']['num_questions']);
        $this->assertSame(['A1', 'B1'], $definition['saved_test']['filters']['levels']);
        $this->assertSame(
            [$generated['preview']['fully_qualified_class_name']],
            $definition['saved_test']['filters']['seeder_classes']
        );
        $this->assertSame('../definition.json', $localization['target']['definition_path']);
        $this->assertSame(
            $generated['preview']['fully_qualified_class_name'],
            $localization['target']['seeder_class']
        );
        $this->assertSame('en', $localization['locale']);
    }

    public function test_theory_page_source_persists_canonical_prompt_generator_linkage(): void
    {
        $generated = $this->makeGenerated(
            topic: 'Unit Theory Linkage',
            namespace: 'Tests\\CodexUnit\\V3TheoryLinkage',
            source: [
                'source_type' => 'theory_page',
                'source_label' => 'Theory page',
                'id' => 712,
                'slug' => 'plural-nouns-s-es-ies',
                'title' => 'Plural Nouns — Множина іменників: правила, винятки, приклади',
                'topic' => 'Plural Nouns — Множина іменників: правила, винятки, приклади',
                'category_slug_path' => 'imennyky-artykli-ta-kilkist',
                'page_seeder_class' => 'Database\\Seeders\\Page_V3\\NounsArticlesQuantity\\NounsArticlesQuantityPluralNounsTheorySeeder',
                'url' => 'https://gramlyze.com/theory/imennyky-artykli-ta-kilkist/plural-nouns-s-es-ies',
            ],
        );

        app(V3SkeletonWriterService::class)->write($generated, true);
        $definition = json_decode(File::get($generated['preview']['definition_absolute_path']), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame([
            'source_type' => 'theory_page',
            'theory_page_id' => 712,
            'theory_page_ids' => [712],
            'theory_page' => [
                'id' => 712,
                'slug' => 'plural-nouns-s-es-ies',
                'title' => 'Plural Nouns — Множина іменників: правила, винятки, приклади',
                'category_slug_path' => 'imennyky-artykli-ta-kilkist',
                'page_seeder_class' => 'Database\\Seeders\\Page_V3\\NounsArticlesQuantity\\NounsArticlesQuantityPluralNounsTheorySeeder',
                'url' => 'https://gramlyze.com/theory/imennyky-artykli-ta-kilkist/plural-nouns-s-es-ies',
            ],
        ], $definition['saved_test']['filters']['prompt_generator']);
    }

    /**
     * @param  array<string, mixed>  $source
     * @return array<string, mixed>
     */
    private function makeGenerated(
        string $topic,
        string $namespace,
        array $source = [
            'source_type' => 'manual_topic',
            'source_label' => 'Manual topic',
            'topic' => 'Unit Topic',
        ],
    ): array {
        $preview = app(V3SeederBlueprintService::class)->buildPreview($namespace, $topic);
        $this->cleanupPaths[] = $preview['seeder_absolute_path'];
        $this->cleanupPaths[] = $preview['package_absolute_path'];

        $resolvedSource = array_merge($source, [
            'topic' => $source['topic'] ?? $topic,
        ]);

        return [
            'source' => $resolvedSource,
            'preview' => $preview,
            'distribution' => ['A1' => 4, 'B1' => 4],
            'total_questions' => 8,
        ];
    }
}
