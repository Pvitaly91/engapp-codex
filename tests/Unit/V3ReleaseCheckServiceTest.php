<?php

namespace Tests\Unit;

use App\Services\V3PromptGenerator\V3ReleaseCheckService;
use App\Services\V3PromptGenerator\V3SeederBlueprintService;
use App\Services\V3PromptGenerator\V3SkeletonWriterService;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Tests\TestCase;

class V3ReleaseCheckServiceTest extends TestCase
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

    public function test_it_resolves_definition_package_loader_and_real_seeder_targets_to_the_same_package(): void
    {
        $generated = $this->writeGenerated(
            topic: 'Unit Release Check Resolution',
            namespace: 'Tests\\CodexUnit\\V3ReleaseResolution',
        );
        $service = app(V3ReleaseCheckService::class);
        $targets = [
            $generated['preview']['definition_relative_path'],
            $generated['preview']['package_relative_path'],
            $generated['preview']['seeder_relative_path'],
            $generated['preview']['real_seeder_relative_path'],
        ];

        foreach ($targets as $target) {
            $report = $service->run($target, 'scaffold');

            $this->assertSame(
                $generated['preview']['definition_relative_path'],
                $report['target']['definition_relative_path']
            );
            $this->assertSame(
                $generated['preview']['package_relative_path'],
                $report['target']['package_root_relative_path']
            );
            $this->assertSame(
                $this->normalizePath($generated['preview']['seeder_absolute_path']),
                $report['target']['loader_absolute_path']
            );
            $this->assertSame(
                $this->normalizePath($generated['preview']['real_seeder_absolute_path']),
                $report['target']['real_seeder_absolute_path']
            );
        }
    }

    public function test_scaffold_profile_returns_warnings_but_no_fails_for_a_fresh_v3_skeleton(): void
    {
        $generated = $this->writeGenerated(
            topic: 'Unit Release Scaffold',
            namespace: 'Tests\\CodexUnit\\V3ReleaseScaffold',
        );

        $report = app(V3ReleaseCheckService::class)->run(
            $generated['preview']['package_relative_path'],
            'scaffold'
        );

        $this->assertSame(0, $report['summary']['check_counts']['fail']);
        $this->assertGreaterThan(0, $report['summary']['check_counts']['warn']);
        $this->assertFalse($report['summary']['fully_valid']);
        $this->assertSame('warn', $this->statusForCode($report, 'v3.questions.readiness'));
        $this->assertSame('warn', $this->statusForCode($report, 'v3.localization.en.coverage'));
    }

    public function test_release_profile_fails_for_empty_scaffold_but_keeps_theory_linkage_contract_valid(): void
    {
        $generated = $this->writeGenerated(
            topic: 'Unit Release Theory Link',
            namespace: 'Tests\\CodexUnit\\V3ReleaseTheoryLink',
            source: [
                'source_type' => 'theory_page',
                'source_label' => 'Theory page',
                'id' => 712,
                'slug' => 'plural-nouns-s-es-ies',
                'title' => 'Plural Nouns',
                'topic' => 'Plural Nouns',
                'category_slug_path' => 'imennyky-artykli-ta-kilkist',
                'page_seeder_class' => 'Database\\Seeders\\Page_V3\\NounsArticlesQuantity\\NounsArticlesQuantityPluralNounsTheorySeeder',
                'url' => 'https://gramlyze.com/theory/imennyky-artykli-ta-kilkist/plural-nouns-s-es-ies',
            ],
        );

        $report = app(V3ReleaseCheckService::class)->run(
            $generated['preview']['seeder_relative_path'],
            'release'
        );

        $this->assertGreaterThan(0, $report['summary']['check_counts']['fail']);
        $this->assertSame('fail', $this->statusForCode($report, 'v3.questions.readiness'));
        $this->assertSame('pass', $this->statusForCode($report, 'v3.saved_test.contract'));
    }

    public function test_it_rejects_targets_outside_the_v3_root(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Target must stay inside database/seeders/V3.');

        app(V3ReleaseCheckService::class)->run(base_path('composer.json'));
    }

    /**
     * @param  array<string, mixed>  $source
     * @return array<string, mixed>
     */
    private function writeGenerated(
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

        $generated = [
            'source' => array_merge($source, [
                'topic' => $source['topic'] ?? $topic,
            ]),
            'preview' => $preview,
            'distribution' => ['A1' => 4, 'B1' => 4],
            'total_questions' => 8,
        ];

        app(V3SkeletonWriterService::class)->write($generated, true);

        return $generated;
    }

    /**
     * @param  array<string, mixed>  $report
     */
    private function statusForCode(array $report, string $code): ?string
    {
        foreach ((array) ($report['checks'] ?? []) as $check) {
            if (($check['code'] ?? null) === $code) {
                return (string) ($check['status'] ?? '');
            }
        }

        return null;
    }

    private function normalizePath(string $path): string
    {
        return rtrim(str_replace('\\', '/', $path), '/');
    }
}
