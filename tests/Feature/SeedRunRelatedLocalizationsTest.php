<?php

namespace Tests\Feature;

use App\Http\Controllers\SeedRunController;
use App\Modules\SeedRunsV2\Services\SeedRunsService;
use App\Services\QuestionDeletionService;
use App\Services\SeederTestTargetResolver;
use App\Support\Database\JsonPageLocalizationManager;
use App\Support\Database\JsonTestLocalizationManager;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Tests\TestCase;

class SeedRunRelatedLocalizationsTest extends TestCase
{
    public function test_controller_builds_related_localization_map_for_executed_seeders(): void
    {
        $controller = new class(
            app(QuestionDeletionService::class),
            app(SeederTestTargetResolver::class),
            app(JsonTestLocalizationManager::class),
            app(JsonPageLocalizationManager::class),
        ) extends SeedRunController {
            public function exposeRelatedLocalizationMap(Collection $executedSeeders): Collection
            {
                return $this->buildRelatedLocalizationMap($executedSeeders);
            }

            protected function localizationTargetSeederClass(string $className): ?string
            {
                return match ($className) {
                    'Database\\Seeders\\V3\\Localizations\\En\\ExampleLocalizationSeeder',
                    'Database\\Seeders\\V3\\Localizations\\Pl\\ExampleLocalizationSeeder' => 'Database\\Seeders\\V3\\ExampleSeeder',
                    default => null,
                };
            }

            protected function localizationDescriptorForClass(string $className): ?array
            {
                return match ($className) {
                    'Database\\Seeders\\V3\\Localizations\\En\\ExampleLocalizationSeeder' => ['locale' => 'en'],
                    'Database\\Seeders\\V3\\Localizations\\Pl\\ExampleLocalizationSeeder' => ['locale' => 'pl'],
                    default => null,
                };
            }
        };

        $map = $controller->exposeRelatedLocalizationMap($this->fakeExecutedSeeders());

        $this->assertCount(1, $map);
        $this->assertTrue($map->has('Database\\Seeders\\V3\\ExampleSeeder'));

        $items = $map->get('Database\\Seeders\\V3\\ExampleSeeder');

        $this->assertInstanceOf(Collection::class, $items);
        $this->assertCount(2, $items);
        $this->assertSame(['EN', 'PL'], $items->pluck('locale_label')->all());
        $this->assertSame('Локалізація питань', $items->first()['type_label']);
    }

    public function test_seed_runs_v2_service_builds_related_localization_map_for_executed_seeders(): void
    {
        $service = new class(
            app(QuestionDeletionService::class),
            app(SeederTestTargetResolver::class),
            app(JsonTestLocalizationManager::class),
            app(JsonPageLocalizationManager::class),
        ) extends SeedRunsService {
            public function exposeRelatedLocalizationMap(Collection $executedSeeders): Collection
            {
                return $this->buildRelatedLocalizationMap($executedSeeders);
            }

            protected function localizationTargetSeederClass(string $className): ?string
            {
                return match ($className) {
                    'Database\\Seeders\\V3\\Localizations\\En\\ExampleLocalizationSeeder',
                    'Database\\Seeders\\V3\\Localizations\\Pl\\ExampleLocalizationSeeder' => 'Database\\Seeders\\V3\\ExampleSeeder',
                    default => null,
                };
            }

            protected function localizationDescriptorForClass(string $className): ?array
            {
                return match ($className) {
                    'Database\\Seeders\\V3\\Localizations\\En\\ExampleLocalizationSeeder' => ['locale' => 'en'],
                    'Database\\Seeders\\V3\\Localizations\\Pl\\ExampleLocalizationSeeder' => ['locale' => 'pl'],
                    default => null,
                };
            }
        };

        $map = $service->exposeRelatedLocalizationMap($this->fakeExecutedSeeders(true));
        $items = $map->get('Database\\Seeders\\V3\\ExampleSeeder');

        $this->assertCount(2, $items);
        $this->assertSame('2026-04-01 11:00:00', $items->first()['ran_at']);
        $this->assertSame('2026-04-01 12:00:00', $items->last()['ran_at']);
    }

    public function test_controller_builds_available_localization_map_for_pending_seeders(): void
    {
        $controller = new class(
            app(QuestionDeletionService::class),
            app(SeederTestTargetResolver::class),
            app(JsonTestLocalizationManager::class),
            app(JsonPageLocalizationManager::class),
        ) extends SeedRunController {
            public function exposeAvailableLocalizationMap(iterable $targetSeeders): Collection
            {
                return $this->buildAvailableLocalizationMap($targetSeeders);
            }

            protected function virtualLocalizationClasses(): array
            {
                return [
                    'Database\\Seeders\\V3\\Localizations\\En\\ExampleLocalizationSeeder',
                    'Database\\Seeders\\V3\\Localizations\\Pl\\ExampleLocalizationSeeder',
                ];
            }

            protected function virtualLocalizationType(string $className): ?string
            {
                return match ($className) {
                    'Database\\Seeders\\V3\\Localizations\\En\\ExampleLocalizationSeeder',
                    'Database\\Seeders\\V3\\Localizations\\Pl\\ExampleLocalizationSeeder' => 'question_localizations',
                    default => null,
                };
            }

            protected function localizationTargetSeederClass(string $className): ?string
            {
                return match ($className) {
                    'Database\\Seeders\\V3\\Localizations\\En\\ExampleLocalizationSeeder',
                    'Database\\Seeders\\V3\\Localizations\\Pl\\ExampleLocalizationSeeder' => 'Database\\Seeders\\V3\\ExampleSeeder',
                    default => null,
                };
            }

            protected function localizationDescriptorForClass(string $className): ?array
            {
                return match ($className) {
                    'Database\\Seeders\\V3\\Localizations\\En\\ExampleLocalizationSeeder' => ['locale' => 'en'],
                    'Database\\Seeders\\V3\\Localizations\\Pl\\ExampleLocalizationSeeder' => ['locale' => 'pl'],
                    default => null,
                };
            }
        };

        $map = $controller->exposeAvailableLocalizationMap([
            'Database\\Seeders\\V3\\ExampleSeeder',
        ]);

        $this->assertCount(1, $map);
        $this->assertTrue($map->has('Database\\Seeders\\V3\\ExampleSeeder'));
        $this->assertSame(['EN', 'PL'], $map->get('Database\\Seeders\\V3\\ExampleSeeder')->pluck('locale_label')->all());
    }

    public function test_seed_runs_v2_service_builds_available_localization_map_for_pending_seeders(): void
    {
        $service = new class(
            app(QuestionDeletionService::class),
            app(SeederTestTargetResolver::class),
            app(JsonTestLocalizationManager::class),
            app(JsonPageLocalizationManager::class),
        ) extends SeedRunsService {
            public function exposeAvailableLocalizationMap(iterable $targetSeeders): Collection
            {
                return $this->buildAvailableLocalizationMap($targetSeeders);
            }

            protected function virtualLocalizationClasses(): array
            {
                return [
                    'Database\\Seeders\\Page_V3\\Localizations\\En\\ExamplePageLocalizationSeeder',
                ];
            }

            protected function virtualLocalizationType(string $className): ?string
            {
                return match ($className) {
                    'Database\\Seeders\\Page_V3\\Localizations\\En\\ExamplePageLocalizationSeeder' => 'page_localizations',
                    default => null,
                };
            }

            protected function localizationTargetSeederClass(string $className): ?string
            {
                return match ($className) {
                    'Database\\Seeders\\Page_V3\\Localizations\\En\\ExamplePageLocalizationSeeder' => 'Database\\Seeders\\Page_V3\\ExampleSeeder',
                    default => null,
                };
            }

            protected function localizationDescriptorForClass(string $className): ?array
            {
                return match ($className) {
                    'Database\\Seeders\\Page_V3\\Localizations\\En\\ExamplePageLocalizationSeeder' => ['locale' => 'en'],
                    default => null,
                };
            }
        };

        $map = $service->exposeAvailableLocalizationMap([
            (object) ['class_name' => 'Database\\Seeders\\Page_V3\\ExampleSeeder'],
        ]);

        $items = $map->get('Database\\Seeders\\Page_V3\\ExampleSeeder');

        $this->assertCount(1, $items);
        $this->assertSame('EN', $items->first()['locale_label']);
        $this->assertSame('Локалізація сторінки', $items->first()['type_label']);
    }

    public function test_seed_runs_v2_hierarchy_keeps_related_localizations_in_seed_run_payload(): void
    {
        $service = new class(
            app(QuestionDeletionService::class),
            app(SeederTestTargetResolver::class),
            app(JsonTestLocalizationManager::class),
            app(JsonPageLocalizationManager::class),
        ) extends SeedRunsService {
            public function exposeBuildSeederHierarchy(Collection $seedRuns): Collection
            {
                return $this->buildSeederHierarchy($seedRuns);
            }
        };

        $seedRun = (object) [
            'id' => 101,
            'class_name' => 'Database\\Seeders\\ExampleSeeder',
            'display_class_name' => 'ExampleSeeder',
            'display_class_namespace' => null,
            'display_class_basename' => 'ExampleSeeder',
            'ran_at_formatted' => '2026-04-01 10:00:00',
            'question_count' => 0,
            'theory_target' => null,
            'test_target' => null,
            'seeder_tab' => 'main',
            'related_localizations' => [
                [
                    'seed_run_id' => 201,
                    'display_name' => 'V3\\Localizations\\En\\ExampleLocalizationSeeder',
                    'locale_label' => 'EN',
                    'type_label' => 'Локалізація питань',
                ],
            ],
            'related_localizations_count' => 1,
        ];

        $hierarchy = $service->exposeBuildSeederHierarchy(collect([$seedRun]));
        $node = $hierarchy->first();

        $this->assertSame('seeder', $node['type']);
        $this->assertSame(1, $node['seed_run']['related_localizations_count']);
        $this->assertCount(1, $node['seed_run']['related_localizations']);
        $this->assertSame('EN', $node['seed_run']['related_localizations'][0]['locale_label']);
    }

    public function test_seed_runs_v2_pending_hierarchy_keeps_available_localizations_in_pending_payload(): void
    {
        $service = new class(
            app(QuestionDeletionService::class),
            app(SeederTestTargetResolver::class),
            app(JsonTestLocalizationManager::class),
            app(JsonPageLocalizationManager::class),
        ) extends SeedRunsService {
            public function exposeBuildPendingSeederHierarchy(Collection $pendingSeeders): Collection
            {
                return $this->buildPendingSeederHierarchy($pendingSeeders);
            }
        };

        $pendingSeeder = (object) [
            'class_name' => 'Database\\Seeders\\ExampleSeeder',
            'display_class_name' => 'ExampleSeeder',
            'display_class_namespace' => null,
            'display_class_basename' => 'ExampleSeeder',
            'supports_preview' => false,
            'data_type' => 'questions',
            'seeder_tab' => 'main',
            'required_base_seeder' => null,
            'required_base_display_name' => null,
            'can_execute' => true,
            'execution_block_reason' => null,
            'available_localizations' => [
                [
                    'class_name' => 'Database\\Seeders\\V3\\Localizations\\En\\ExampleLocalizationSeeder',
                    'display_name' => 'V3\\Localizations\\En\\ExampleLocalizationSeeder',
                    'locale_label' => 'EN',
                    'type_label' => 'Локалізація питань',
                ],
            ],
            'available_localizations_count' => 1,
        ];

        $hierarchy = $service->exposeBuildPendingSeederHierarchy(collect([$pendingSeeder]));
        $node = $hierarchy->first();

        $this->assertSame('seeder', $node['type']);
        $this->assertSame(1, $node['pending_seeder']['available_localizations_count']);
        $this->assertCount(1, $node['pending_seeder']['available_localizations']);
        $this->assertSame('EN', $node['pending_seeder']['available_localizations'][0]['locale_label']);
    }

    public function test_classic_executed_node_renders_related_localizations_actions(): void
    {
        $html = view('seed-runs.partials.executed-node', [
            'node' => [
                'type' => 'seeder',
                'name' => 'ExampleSeeder',
                'seed_run' => (object) [
                    'id' => 101,
                    'class_name' => 'Database\\Seeders\\V3\\ExampleSeeder',
                    'display_class_name' => 'V3\\ExampleSeeder',
                    'display_class_basename' => 'ExampleSeeder',
                    'question_count' => 0,
                    'ran_at' => Carbon::parse('2026-04-01 10:00:00'),
                    'data_profile' => ['type' => 'questions'],
                    'related_localizations' => [
                        [
                            'seed_run_id' => 201,
                            'display_name' => 'V3\\Localizations\\En\\ExampleLocalizationSeeder',
                            'locale_label' => 'EN',
                            'type_label' => 'Локалізація питань',
                            'ran_at' => '2026-04-01 11:00:00',
                        ],
                    ],
                ],
                'data_profile' => ['type' => 'questions'],
            ],
            'depth' => 0,
            'recentSeedRunOrdinals' => collect(),
            'activeSeederTab' => 'main',
        ])->render();

        $this->assertStringContainsString('Виконані локалізації', $html);
        $this->assertStringContainsString('Видалити з БД', $html);
        $this->assertStringContainsString('data-reload-after-success="true"', $html);
    }

    public function test_livewire_executed_node_renders_related_localizations_actions(): void
    {
        $html = view('seed-runs-v2::livewire.partials.executed-node', [
            'node' => [
                'type' => 'seeder',
                'name' => 'ExampleSeeder',
                'seed_run' => [
                    'id' => 101,
                    'class_name' => 'Database\\Seeders\\V3\\ExampleSeeder',
                    'display_class_name' => 'V3\\ExampleSeeder',
                    'question_count' => 0,
                    'related_localizations' => [
                        [
                            'seed_run_id' => 201,
                            'display_name' => 'V3\\Localizations\\En\\ExampleLocalizationSeeder',
                            'locale_label' => 'EN',
                            'type_label' => 'Локалізація питань',
                            'ran_at' => '2026-04-01 11:00:00',
                        ],
                    ],
                ],
                'data_profile' => ['type' => 'questions'],
            ],
            'depth' => 0,
            'recentSeedRunOrdinals' => [],
            'searchQuery' => '',
        ])->render();

        $this->assertStringContainsString('Виконані локалізації', $html);
        $this->assertStringContainsString('confirmDeleteLocalizationFromDatabase', $html);
        $this->assertStringContainsString('Видалити з БД', $html);
    }

    public function test_classic_pending_node_renders_existing_localizations(): void
    {
        $html = view('seed-runs.partials.pending-node', [
            'node' => [
                'type' => 'seeder',
                'name' => 'ExampleSeeder',
                'pending_seeder' => (object) [
                    'class_name' => 'Database\\Seeders\\V3\\ExampleSeeder',
                    'display_class_name' => 'V3\\ExampleSeeder',
                    'display_class_namespace' => 'V3',
                    'display_class_basename' => 'ExampleSeeder',
                    'supports_preview' => true,
                    'data_type' => 'questions',
                    'can_execute' => true,
                    'available_localizations' => [
                        [
                            'display_name' => 'V3\\Localizations\\En\\ExampleLocalizationSeeder',
                            'locale_label' => 'EN',
                            'type_label' => 'Локалізація питань',
                        ],
                    ],
                ],
            ],
            'depth' => 0,
            'activeSeederTab' => 'main',
        ])->render();

        $this->assertStringContainsString('Існуючі локалізації', $html);
        $this->assertStringContainsString('V3\\Localizations\\En\\ExampleLocalizationSeeder', $html);
        $this->assertStringContainsString('Локалізація питань', $html);
    }

    public function test_livewire_pending_node_renders_existing_localizations(): void
    {
        $html = view('seed-runs-v2::livewire.partials.pending-node', [
            'node' => [
                'type' => 'seeder',
                'name' => 'ExampleSeeder',
                'pending_seeder' => [
                    'class_name' => 'Database\\Seeders\\Page_V3\\ExampleSeeder',
                    'display_class_name' => 'Page_V3\\ExampleSeeder',
                    'display_class_namespace' => 'Page_V3',
                    'display_class_basename' => 'ExampleSeeder',
                    'supports_preview' => false,
                    'data_type' => 'pages',
                    'can_execute' => true,
                    'available_localizations' => [
                        [
                            'display_name' => 'Page_V3\\Localizations\\En\\ExamplePageLocalizationSeeder',
                            'locale_label' => 'EN',
                            'type_label' => 'Локалізація сторінки',
                        ],
                    ],
                ],
            ],
            'depth' => 0,
        ])->render();

        $this->assertStringContainsString('Існуючі локалізації', $html);
        $this->assertStringContainsString('Page_V3\\Localizations\\En\\ExamplePageLocalizationSeeder', $html);
        $this->assertStringContainsString('Локалізація сторінки', $html);
    }

    private function fakeExecutedSeeders(bool $withFormattedTime = false): Collection
    {
        $baseSeeder = (object) [
            'id' => 101,
            'class_name' => 'Database\\Seeders\\V3\\ExampleSeeder',
            'display_class_name' => 'V3\\ExampleSeeder',
            'display_class_basename' => 'ExampleSeeder',
            'data_profile' => ['type' => 'questions'],
            'ran_at' => Carbon::parse('2026-04-01 10:00:00'),
        ];

        $localizationEn = (object) [
            'id' => 201,
            'class_name' => 'Database\\Seeders\\V3\\Localizations\\En\\ExampleLocalizationSeeder',
            'display_class_name' => 'V3\\Localizations\\En\\ExampleLocalizationSeeder',
            'display_class_basename' => 'ExampleLocalizationSeeder',
            'data_profile' => ['type' => 'question_localizations'],
            'ran_at' => Carbon::parse('2026-04-01 11:00:00'),
        ];

        $localizationPl = (object) [
            'id' => 202,
            'class_name' => 'Database\\Seeders\\V3\\Localizations\\Pl\\ExampleLocalizationSeeder',
            'display_class_name' => 'V3\\Localizations\\Pl\\ExampleLocalizationSeeder',
            'display_class_basename' => 'ExampleLocalizationSeeder',
            'data_profile' => ['type' => 'question_localizations'],
            'ran_at' => Carbon::parse('2026-04-01 12:00:00'),
        ];

        if ($withFormattedTime) {
            $localizationEn->ran_at_formatted = '2026-04-01 11:00:00';
            $localizationPl->ran_at_formatted = '2026-04-01 12:00:00';
        }

        return collect([$baseSeeder, $localizationEn, $localizationPl]);
    }
}
