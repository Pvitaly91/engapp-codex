<?php

namespace Tests\Feature;

use App\Http\Controllers\SeedRunController;
use App\Modules\SeedRunsV2\Services\SeedRunsService;
use App\Services\QuestionDeletionService;
use App\Support\Database\JsonPageLocalizationManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class SeederFolderExecutionOrderTest extends TestCase
{
    public function test_seed_run_controller_executes_page_v2_folder_in_category_then_page_order(): void
    {
        $rootCategory = 'Database\\Seeders\\Page_v2\\PassiveVoice\\PassiveVoiceCategorySeeder';
        $childCategory = 'Database\\Seeders\\Page_v2\\PassiveVoice\\Tenses\\PassiveVoiceTensesCategorySeeder';
        $pageSeeder = 'Database\\Seeders\\Page_v2\\PassiveVoice\\Tenses\\PassiveVoicePresentSimpleTheorySeeder';

        Artisan::shouldReceive('call')
            ->once()
            ->ordered()
            ->with('db:seed', ['--class' => $rootCategory])
            ->andReturn(0);

        Artisan::shouldReceive('call')
            ->once()
            ->ordered()
            ->with('db:seed', ['--class' => $childCategory])
            ->andReturn(0);

        Artisan::shouldReceive('call')
            ->once()
            ->ordered()
            ->with('db:seed', ['--class' => $pageSeeder])
            ->andReturn(0);

        $request = Request::create('/admin/seed-runs/folders/run', 'POST', [
            'folder_label' => 'Page_v2/PassiveVoice',
            'class_names' => [
                $pageSeeder,
                $childCategory,
                $rootCategory,
            ],
        ]);
        $request->headers->set('Accept', 'application/json');

        $response = app(SeedRunController::class)->runFolder($request);

        $this->assertSame(200, $response->getStatusCode());

        $payload = $response->getData(true);

        $this->assertSame(
            [$rootCategory, $childCategory, $pageSeeder],
            $payload['executed_classes']
        );
    }

    public function test_seed_runs_service_executes_page_v3_folder_in_category_then_page_order(): void
    {
        $rootCategory = 'Database\\Seeders\\Page_V3\\PassiveVoice\\PassiveVoiceCategorySeeder';
        $childCategory = 'Database\\Seeders\\Page_V3\\PassiveVoice\\Tenses\\PassiveVoiceTensesCategorySeeder';
        $pageSeeder = 'Database\\Seeders\\Page_V3\\PassiveVoice\\Tenses\\PassiveVoicePresentSimpleTheorySeeder';

        Artisan::shouldReceive('call')
            ->once()
            ->ordered()
            ->with('db:seed', ['--class' => $rootCategory])
            ->andReturn(0);

        Artisan::shouldReceive('call')
            ->once()
            ->ordered()
            ->with('db:seed', ['--class' => $childCategory])
            ->andReturn(0);

        Artisan::shouldReceive('call')
            ->once()
            ->ordered()
            ->with('db:seed', ['--class' => $pageSeeder])
            ->andReturn(0);

        $result = app(SeedRunsService::class)->runSeedersInFolder([
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

        Artisan::shouldReceive('call')
            ->once()
            ->ordered()
            ->with('db:seed', ['--class' => $rootCategory])
            ->andReturn(0);

        $service = new class(
            app(QuestionDeletionService::class),
            app(JsonPageLocalizationManager::class),
        ) extends SeedRunsService {
            public array $appliedLocalizations = [];

            protected function applyVirtualLocalizationSeeder(string $className): array
            {
                $this->appliedLocalizations[] = $className;

                return ['localized_blocks' => 5];
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
            [$localizationSeeder],
            $service->appliedLocalizations
        );
    }
}
