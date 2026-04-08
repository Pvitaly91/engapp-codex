<?php

namespace Tests\Feature;

use App\Http\Controllers\SeedRunController;
use App\Modules\SeedRunsV2\Services\SeedRunsService;
use App\Services\QuestionDeletionService;
use App\Services\SeederTestTargetResolver;
use App\Support\Database\JsonPageLocalizationManager;
use App\Support\Database\JsonTestLocalizationManager;
use Illuminate\Http\Request;
use Tests\TestCase;

class SeederFolderExecutionOrderTest extends TestCase
{
    public function test_seed_run_controller_executes_page_v2_folder_in_category_then_page_order(): void
    {
        $rootCategory = 'Database\\Seeders\\Page_v2\\PassiveVoice\\PassiveVoiceCategorySeeder';
        $childCategory = 'Database\\Seeders\\Page_v2\\PassiveVoice\\Tenses\\PassiveVoiceTensesCategorySeeder';
        $pageSeeder = 'Database\\Seeders\\Page_v2\\PassiveVoice\\Tenses\\PassiveVoicePresentSimpleTheorySeeder';

        $controller = new class(
            app(QuestionDeletionService::class),
            app(\App\Services\SeederPromptTheoryPageResolver::class),
            app(SeederTestTargetResolver::class),
            app(JsonTestLocalizationManager::class),
            app(JsonPageLocalizationManager::class),
        ) extends SeedRunController {
            public array $executedSeeders = [];

            protected function executeConcreteSeeder(string $className): void
            {
                $this->executedSeeders[] = $className;
            }
        };

        $request = Request::create('/admin/seed-runs/folders/run', 'POST', [
            'folder_label' => 'Page_v2/PassiveVoice',
            'class_names' => [
                $pageSeeder,
                $childCategory,
                $rootCategory,
            ],
        ]);
        $request->headers->set('Accept', 'application/json');

        $response = $controller->runFolder($request);

        $this->assertSame(200, $response->getStatusCode());

        $payload = $response->getData(true);

        $this->assertSame(
            [$rootCategory, $childCategory, $pageSeeder],
            $payload['executed_classes']
        );
        $this->assertSame(
            [$rootCategory, $childCategory, $pageSeeder],
            $controller->executedSeeders
        );
    }

    public function test_seed_runs_service_executes_page_v3_folder_in_category_then_page_order(): void
    {
        $rootCategory = 'Database\\Seeders\\Page_V3\\PassiveVoice\\PassiveVoiceCategorySeeder';
        $childCategory = 'Database\\Seeders\\Page_V3\\PassiveVoice\\Tenses\\PassiveVoiceTensesCategorySeeder';
        $pageSeeder = 'Database\\Seeders\\Page_V3\\PassiveVoice\\Tenses\\PassiveVoicePresentSimpleTheorySeeder';

        $service = new class(
            app(QuestionDeletionService::class),
            app(\App\Services\SeederPromptTheoryPageResolver::class),
            app(SeederTestTargetResolver::class),
            app(JsonTestLocalizationManager::class),
            app(JsonPageLocalizationManager::class),
        ) extends SeedRunsService {
            public array $executedSeeders = [];

            protected function executeConcreteSeeder(string $className): void
            {
                $this->executedSeeders[] = $className;
            }
        };

        $result = $service->runSeedersInFolder([
            $pageSeeder,
            $childCategory,
            $rootCategory,
        ], 'Page_V3/PassiveVoice');

        $this->assertTrue($result['success']);
        $this->assertSame(
            [$rootCategory, $childCategory, $pageSeeder],
            $result['executed']
        );
        $this->assertSame(
            [$rootCategory, $childCategory, $pageSeeder],
            $result['ordered']
        );
        $this->assertSame(
            [$rootCategory, $childCategory, $pageSeeder],
            $service->executedSeeders
        );
    }

    public function test_seed_runs_service_discovers_page_v3_localizations(): void
    {
        $service = app(SeedRunsService::class);
        $reflection = new \ReflectionClass($service);
        $discoverMethod = $reflection->getMethod('discoverSeederClasses');
        $discoverMethod->setAccessible(true);

        $classes = collect($discoverMethod->invoke($service, database_path('seeders/Page_V3')));

        $this->assertTrue(
            $classes->contains('Database\\Seeders\\Page_V3\\Localizations\\En\\PassiveVoiceCategoryLocalizationSeeder')
        );
    }

    public function test_seed_runs_service_executes_page_v3_localizations_last(): void
    {
        $rootCategory = 'Database\\Seeders\\Page_V3\\PassiveVoice\\PassiveVoiceCategorySeeder';
        $localizationSeeder = 'Database\\Seeders\\Page_V3\\Localizations\\En\\PassiveVoiceCategoryLocalizationSeeder';

        $service = new class(
            app(QuestionDeletionService::class),
            app(\App\Services\SeederPromptTheoryPageResolver::class),
            app(SeederTestTargetResolver::class),
            app(JsonTestLocalizationManager::class),
            app(JsonPageLocalizationManager::class),
        ) extends SeedRunsService {
            public array $appliedLocalizations = [];
            public array $executedSeeders = [];

            protected function applyVirtualLocalizationSeeder(string $className): array
            {
                $this->appliedLocalizations[] = $className;

                return ['localized_blocks' => 5];
            }

            protected function executeConcreteSeeder(string $className): void
            {
                $this->executedSeeders[] = $className;
            }

            protected function logVirtualSeederRun(string $className): void
            {
            }
        };

        $result = $service->runSeedersInFolder([
            $localizationSeeder,
            $rootCategory,
        ], 'Page_V3/PassiveVoice');

        $this->assertTrue($result['success']);
        $this->assertSame(
            [$rootCategory, $localizationSeeder],
            $result['ordered']
        );
        $this->assertSame(
            [$rootCategory, $localizationSeeder],
            $result['executed']
        );
        $this->assertSame(
            [$rootCategory],
            $service->executedSeeders
        );
        $this->assertSame(
            [$localizationSeeder],
            $service->appliedLocalizations
        );
    }

    public function test_seed_runs_service_blocks_localization_until_base_seeder_is_executed(): void
    {
        $localizationSeeder = 'Database\\Seeders\\Page_V3\\Localizations\\En\\PassiveVoiceCategoryLocalizationSeeder';

        $service = new class(
            app(QuestionDeletionService::class),
            app(\App\Services\SeederPromptTheoryPageResolver::class),
            app(SeederTestTargetResolver::class),
            app(JsonTestLocalizationManager::class),
            app(JsonPageLocalizationManager::class),
        ) extends SeedRunsService {
            public array $appliedLocalizations = [];

            protected function applyVirtualLocalizationSeeder(string $className): array
            {
                $this->appliedLocalizations[] = $className;

                return ['localized_blocks' => 1];
            }
        };

        $result = $service->runSeeder($localizationSeeder);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Спочатку виконайте основний сидер', $result['message']);
        $this->assertSame([], $service->appliedLocalizations);
    }
}
