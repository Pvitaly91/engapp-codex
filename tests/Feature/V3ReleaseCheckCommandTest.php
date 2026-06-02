<?php

namespace Tests\Feature;

use App\Services\V3PromptGenerator\V3SeederBlueprintService;
use App\Services\V3PromptGenerator\V3SkeletonWriterService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class V3ReleaseCheckCommandTest extends TestCase
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

    public function test_scaffold_profile_human_output_can_write_a_report(): void
    {
        Storage::fake('local');
        $generated = $this->writeGenerated(
            topic: 'Feature V3 Release Scaffold',
            namespace: 'Tests\\CodexFeature\\V3ReleaseScaffold',
        );

        $exitCode = Artisan::call('v3:release-check', [
            'target' => $generated['preview']['package_relative_path'],
            '--profile' => 'scaffold',
            '--write-report' => true,
        ]);

        $output = str_replace("\r\n", "\n", Artisan::output());
        $reportFiles = Storage::disk('local')->allFiles('release-checks/v3');

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Profile: scaffold', $output);
        $this->assertStringContainsString('[WARN] The base V3 definition has questions ready for seeding', $output);
        $this->assertStringContainsString('Summary:', $output);
        $this->assertStringContainsString('Report:', $output);
        $this->assertCount(1, $reportFiles);
    }

    public function test_release_profile_json_output_fails_for_an_empty_scaffold(): void
    {
        $generated = $this->writeGenerated(
            topic: 'Feature V3 Release Empty',
            namespace: 'Tests\\CodexFeature\\V3ReleaseEmpty',
        );

        $exitCode = Artisan::call('v3:release-check', [
            'target' => $generated['preview']['seeder_relative_path'],
            '--json' => true,
        ]);

        $payload = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(1, $exitCode);
        $this->assertSame($generated['preview']['definition_relative_path'], $payload['target']['definition_relative_path']);
        $this->assertGreaterThan(0, $payload['summary']['check_counts']['fail']);
        $this->assertSame('fail', $this->statusForCode($payload, 'v3.questions.readiness'));
    }

    public function test_strict_mode_makes_scaffold_warnings_fatal(): void
    {
        $generated = $this->writeGenerated(
            topic: 'Feature V3 Release Strict',
            namespace: 'Tests\\CodexFeature\\V3ReleaseStrict',
        );

        $exitCode = Artisan::call('v3:release-check', [
            'target' => $generated['preview']['real_seeder_relative_path'],
            '--profile' => 'scaffold',
            '--strict' => true,
        ]);

        $output = Artisan::output();

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('Summary:', $output);
        $this->assertStringContainsString('WARN', $output);
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
            'topic' => 'Feature Topic',
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
}
