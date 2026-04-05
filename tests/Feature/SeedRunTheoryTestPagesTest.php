<?php

namespace Tests\Feature;

use App\Http\Controllers\SeedRunController;
use App\Modules\SeedRunsV2\Services\SeedRunsService;
use App\Services\QuestionDeletionService;
use App\Services\SeederPromptTheoryPageResolver;
use App\Services\SeederTestTargetResolver;
use App\Support\Database\JsonPageLocalizationManager;
use App\Support\Database\JsonTestLocalizationManager;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Tests\TestCase;

class SeedRunTheoryTestPagesTest extends TestCase
{
    public function test_controller_groups_executed_and_pending_seeders_by_theory_page(): void
    {
        $controller = new class(
            app(QuestionDeletionService::class),
            app(SeederPromptTheoryPageResolver::class),
            app(SeederTestTargetResolver::class),
            app(JsonTestLocalizationManager::class),
            app(JsonPageLocalizationManager::class),
        ) extends SeedRunController {
            public function exposeBuildTheoryTestPages(Collection $executedSeeders, Collection $pendingSeeders): Collection
            {
                return $this->buildTheoryTestPages($executedSeeders, $pendingSeeders);
            }
        };

        $pages = $controller->exposeBuildTheoryTestPages(
            collect([$this->makeExecutedTheorySeeder()]),
            collect([$this->makePendingTheorySeeder()])
        );

        $this->assertCount(1, $pages);

        $pageGroup = $pages->first();

        $this->assertSame('Present Perfect', data_get($pageGroup, 'page.title'));
        $this->assertSame(2, $pageGroup['seeders_count']);
        $this->assertSame(1, $pageGroup['executed_seeders_count']);
        $this->assertSame(1, $pageGroup['pending_seeders_count']);
        $this->assertSame(12, $pageGroup['question_count']);
        $this->assertSame(
            ['PresentPerfectTheorySeeder', 'PresentPerfectExceptionsSeeder'],
            collect($pageGroup['seeders'])->pluck('display_class_basename')->all()
        );
    }

    public function test_seed_runs_service_groups_executed_and_pending_seeders_by_theory_page(): void
    {
        $service = new class(
            app(QuestionDeletionService::class),
            app(SeederPromptTheoryPageResolver::class),
            app(SeederTestTargetResolver::class),
            app(JsonTestLocalizationManager::class),
            app(JsonPageLocalizationManager::class),
        ) extends SeedRunsService {
            public function exposeBuildTheoryTestPages(Collection $executedSeeders, Collection $pendingSeeders): Collection
            {
                return $this->buildTheoryTestPages($executedSeeders, $pendingSeeders);
            }
        };

        $pages = $service->exposeBuildTheoryTestPages(
            collect([$this->makeExecutedTheorySeeder()]),
            collect([$this->makePendingTheorySeeder()])
        );

        $pageGroup = $pages->first();

        $this->assertSame(1, $pageGroup['executed_seeders_count']);
        $this->assertSame(1, $pageGroup['pending_seeders_count']);
        $this->assertSame(2, $pageGroup['tests_count']);
    }

    public function test_theory_test_pages_partial_renders_pending_seeders_section(): void
    {
        $html = view('seed-runs.partials.theory-test-pages', [
            'theoryTestPages' => collect([
                [
                    'page' => [
                        'label' => 'Пов’язана сторінка теорії',
                        'title' => 'Present Perfect',
                        'url' => 'https://example.test/theory/present-perfect',
                    ],
                    'seeders' => collect([$this->makeExecutedTheorySeeder(), $this->makePendingTheorySeeder()]),
                    'executed_seeders' => collect([$this->makeExecutedTheorySeeder()]),
                    'pending_seeders' => collect([$this->makePendingTheorySeeder()]),
                    'seeders_count' => 2,
                    'executed_seeders_count' => 1,
                    'pending_seeders_count' => 1,
                    'question_count' => 12,
                    'tests_count' => 2,
                ],
            ]),
        ])->render();

        $this->assertStringContainsString('Виконані сидери: 1', $html);
        $this->assertStringContainsString('Невиконані сидери: 1', $html);
        $this->assertStringContainsString('Ще не виконано', $html);
        $this->assertStringContainsString('PresentPerfectExceptionsSeeder', $html);
        $this->assertStringContainsString('Попередній перегляд', $html);
    }

    private function makeExecutedTheorySeeder(): object
    {
        return (object) [
            'class_name' => 'Database\\Seeders\\V3\\PresentPerfectTheorySeeder',
            'display_class_name' => 'V3\\PresentPerfectTheorySeeder',
            'display_class_basename' => 'PresentPerfectTheorySeeder',
            'prompt_theory_target' => [
                'label' => 'Пов’язана сторінка теорії',
                'title' => 'Present Perfect',
                'url' => 'https://example.test/theory/present-perfect',
            ],
            'test_target' => [
                'label' => 'Готовий тест',
                'title' => 'Present Perfect Test',
                'url' => 'https://example.test/tests/present-perfect',
            ],
            'question_count' => 12,
            'ran_at' => Carbon::parse('2026-04-02 10:00:00'),
        ];
    }

    private function makePendingTheorySeeder(): object
    {
        return (object) [
            'class_name' => 'Database\\Seeders\\V3\\PresentPerfectExceptionsSeeder',
            'display_class_name' => 'V3\\PresentPerfectExceptionsSeeder',
            'display_class_basename' => 'PresentPerfectExceptionsSeeder',
            'supports_preview' => true,
            'can_execute' => true,
            'prompt_theory_target' => [
                'label' => 'Пов’язана сторінка теорії',
                'title' => 'Present Perfect',
                'url' => 'https://example.test/theory/present-perfect',
            ],
            'test_target' => [
                'label' => 'Готовий тест',
                'title' => 'Present Perfect Exceptions Test',
                'url' => 'https://example.test/tests/present-perfect-exceptions',
            ],
        ];
    }
}
