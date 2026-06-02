<?php

namespace Tests\Feature;

use Tests\TestCase;

class TheoryPageSeederUuidCollisionTest extends TestCase
{
    public function test_repaired_saved_test_uuids_do_not_reuse_completed_page_uuids(): void
    {
        foreach ($this->savedTestUuidCollisionGroups() as $groupName => $files) {
            $uuids = [];
            $slugs = [];

            foreach ($files as $file) {
                $definition = $this->jsonDefinition($file);
                $uuids[$file] = (string) ($definition['saved_test']['uuid'] ?? '');
                $slugs[$file] = (string) ($definition['saved_test']['slug'] ?? '');
            }

            $this->assertSame(count($uuids), count(array_unique($uuids)), $groupName . ': saved_test.uuid values must be unique.');
            $this->assertSame(count($slugs), count(array_unique($slugs)), $groupName . ': saved_test.slug values must be unique.');
        }
    }

    public function test_repaired_question_uuid_sets_do_not_overlap_completed_pages(): void
    {
        foreach ($this->questionUuidCollisionGroups() as $groupName => $group) {
            $targetUuids = $this->questionUuids($group['target']);
            $completedUuids = $this->questionUuids($group['completed']);

            $this->assertCount(72, $targetUuids, $groupName . ': target should still define 72 questions.');
            $this->assertCount(72, $completedUuids, $groupName . ': completed page should define 72 questions.');
            $this->assertSame([], array_values(array_intersect($targetUuids, $completedUuids)), $groupName . ': question UUIDs must not overlap.');

            $matchingPrefix = array_values(array_filter(
                $targetUuids,
                fn (string $uuid): bool => str_starts_with($uuid, $group['target_prefix'])
            ));

            $this->assertCount(72, $matchingPrefix, $groupName . ': target UUID prefix should cover every question.');
        }
    }

    public function test_repaired_theory_links_manifests_reference_new_polyglot_question_uuids(): void
    {
        $this->assertManifestReferences(
            'database/seeders/V3/TheoryLinks/data/passive-voice-future-simple-theory-links.json',
            'pv-fut-simple-poly-a1-01',
            'pvfs-poly-a1-01'
        );

        $this->assertManifestReferences(
            'database/seeders/V3/TheoryLinks/data/pronouns-demonstratives-personal-object-pronouns-theory-links.json',
            'pers-obj-poly-a1-01',
            'pop-poly-a1-01'
        );
    }

    public function test_missing_content_repair_seeders_do_not_introduce_uuid_collisions(): void
    {
        $definitions = [];
        foreach ($this->allV3DefinitionFiles() as $file) {
            $definition = $this->jsonDefinition($file);
            $definitions[$file] = $definition;
        }

        $maps = [
            'saved_test.uuid' => [],
            'saved_test.slug' => [],
            'question.uuid' => [],
        ];

        foreach ($definitions as $file => $definition) {
            $savedTest = is_array($definition['saved_test'] ?? null) ? $definition['saved_test'] : [];
            foreach (['uuid', 'slug'] as $key) {
                $value = (string) ($savedTest[$key] ?? '');
                if ($value !== '') {
                    $maps['saved_test.' . $key][$value][] = $file;
                }
            }

            foreach (($definition['questions'] ?? $definition['items'] ?? []) as $question) {
                if (! is_array($question)) {
                    continue;
                }

                $uuid = (string) ($question['uuid'] ?? '');
                if ($uuid !== '') {
                    $maps['question.uuid'][$uuid][] = $file;
                }
            }
        }

        foreach ($this->missingContentRepairDefinitionFiles() as $file) {
            $definition = $definitions[$file] ?? [];
            $questions = $definition['questions'] ?? $definition['items'] ?? [];
            $this->assertCount(72, $questions, $file . ': repair seeder should define 72 questions.');

            $savedTestQuestionUuids = array_values(array_filter(array_map('strval', (array) ($definition['saved_test']['question_uuids'] ?? []))));
            $questionUuids = array_values(array_filter(array_map(
                fn (mixed $question): string => is_array($question) ? (string) ($question['uuid'] ?? '') : '',
                $questions
            )));
            $this->assertEqualsCanonicalizing($questionUuids, $savedTestQuestionUuids, $file . ': saved_test.question_uuids should match question UUIDs.');
        }

        foreach ($maps as $mapName => $values) {
            foreach ($values as $value => $files) {
                $inRepairScope = array_values(array_intersect($files, $this->missingContentRepairDefinitionFiles()));
                if ($inRepairScope === []) {
                    continue;
                }

                $this->assertSame([$inRepairScope[0]], $files, $mapName . ' collision for ' . $value . ': ' . implode(', ', $files));
            }
        }
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function savedTestUuidCollisionGroups(): array
    {
        return [
            'passive future simple v3' => [
                'database/seeders/V3/PassiveVoice/PassiveVoiceFutureSimpleAllLevelsV3Seeder/definition.json',
                'database/seeders/V3/PassiveVoice/PassiveVoiceFormalityStyleAllLevelsV3Seeder/definition.json',
            ],
            'passive future simple polyglot' => [
                'database/seeders/V3/Polyglot/PolyglotPassiveVoiceFutureSimpleAllLevelsLessonSeeder/definition.json',
                'database/seeders/V3/Polyglot/PolyglotPassiveVoiceFormalityStyleAllLevelsLessonSeeder/definition.json',
            ],
            'present continuous forms v3' => [
                'database/seeders/V3/Tenses/PresentContinuous/PresentContinuousFormsAllLevelsV3Seeder/definition.json',
                'database/seeders/V3/Tenses/PastContinuous/PastContinuousFormsAllLevelsV3Seeder/definition.json',
            ],
            'present continuous time expressions v3' => [
                'database/seeders/V3/Tenses/PresentContinuous/PresentContinuousTimeExpressionsAllLevelsV3Seeder/definition.json',
                'database/seeders/V3/Tenses/PastContinuous/PastContinuousTimeExpressionsAllLevelsV3Seeder/definition.json',
            ],
            'present perfect forms v3' => [
                'database/seeders/V3/Tenses/PresentPerfect/PresentPerfectFormsAllLevelsV3Seeder/definition.json',
                'database/seeders/V3/Tenses/PastPerfect/PastPerfectFormsAllLevelsV3Seeder/definition.json',
            ],
        ];
    }

    /**
     * @return array<string, array{target: string, completed: string, target_prefix: string}>
     */
    private function questionUuidCollisionGroups(): array
    {
        return [
            'passive future simple v3 questions' => [
                'target' => 'database/seeders/V3/PassiveVoice/PassiveVoiceFutureSimpleAllLevelsV3Seeder/definition.json',
                'completed' => 'database/seeders/V3/PassiveVoice/PassiveVoiceFormalityStyleAllLevelsV3Seeder/definition.json',
                'target_prefix' => 'pv-fut-simple-v3-',
            ],
            'passive future simple polyglot questions' => [
                'target' => 'database/seeders/V3/Polyglot/PolyglotPassiveVoiceFutureSimpleAllLevelsLessonSeeder/definition.json',
                'completed' => 'database/seeders/V3/Polyglot/PolyglotPassiveVoiceFormalityStyleAllLevelsLessonSeeder/definition.json',
                'target_prefix' => 'pv-fut-simple-poly-',
            ],
            'personal object pronouns v3 questions' => [
                'target' => 'database/seeders/V3/PronounsDemonstratives/PersonalObjectPronounsAllLevelsV3Seeder/definition.json',
                'completed' => 'database/seeders/V3/PrepositionsAndPhrasalVerbs/PrepositionsOfPlaceAllLevelsV3Seeder/definition.json',
                'target_prefix' => 'pers-obj-v3-',
            ],
            'personal object pronouns polyglot questions' => [
                'target' => 'database/seeders/V3/Polyglot/PolyglotPersonalObjectPronounsAllLevelsLessonSeeder/definition.json',
                'completed' => 'database/seeders/V3/Polyglot/PolyglotPrepositionsOfPlaceAllLevelsLessonSeeder/definition.json',
                'target_prefix' => 'pers-obj-poly-',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function jsonDefinition(string $relativePath): array
    {
        $decoded = json_decode((string) file_get_contents(base_path($relativePath)), true);
        $this->assertIsArray($decoded, $relativePath . ': JSON definition should decode.');

        return $decoded;
    }

    /**
     * @return array<int, string>
     */
    private function questionUuids(string $relativePath): array
    {
        $definition = $this->jsonDefinition($relativePath);
        $questions = $definition['questions'] ?? $definition['items'] ?? [];
        $this->assertIsArray($questions, $relativePath . ': questions should be an array.');

        $uuids = array_values(array_filter(array_map(
            fn (mixed $question): string => is_array($question) ? (string) ($question['uuid'] ?? '') : '',
            $questions
        )));
        $savedTestUuids = array_values(array_filter(array_map('strval', (array) ($definition['saved_test']['question_uuids'] ?? []))));

        $this->assertEqualsCanonicalizing($uuids, $savedTestUuids, $relativePath . ': saved_test.question_uuids should match question UUIDs.');

        return array_values(array_unique($uuids));
    }

    private function assertManifestReferences(string $relativePath, string $expectedUuid, string $removedUuid): void
    {
        $contents = (string) file_get_contents(base_path($relativePath));

        $this->assertStringContainsString($expectedUuid, $contents, $relativePath . ': expected repaired UUID.');
        $this->assertStringNotContainsString($removedUuid, $contents, $relativePath . ': removed colliding UUID.');
    }

    /**
     * @return array<int, string>
     */
    private function missingContentRepairDefinitionFiles(): array
    {
        return [
            'database/seeders/V3/PassiveVoice/PassiveVoiceFutureContinuousAllLevelsV3Seeder/definition.json',
            'database/seeders/V3/Conditionals/ConditionalsWithUnlessProvidedAsLongAsAllLevelsV3Seeder/definition.json',
            'database/seeders/V3/Conditionals/AdvancedConditionalsAllLevelsV3Seeder/definition.json',
            'database/seeders/V3/PassiveVoice/PassiveReportingStructuresAllLevelsV3Seeder/definition.json',
            'database/seeders/V3/Conditionals/ConditionalAlternativesAndNuanceAllLevelsV3Seeder/definition.json',
            'database/seeders/V3/Polyglot/PolyglotPassiveVoiceFutureContinuousAllLevelsLessonSeeder/definition.json',
            'database/seeders/V3/Polyglot/PolyglotConditionalsWithUnlessProvidedAsLongAsAllLevelsLessonSeeder/definition.json',
            'database/seeders/V3/Polyglot/PolyglotAdvancedConditionalsAllLevelsLessonSeeder/definition.json',
            'database/seeders/V3/Polyglot/PolyglotPassiveReportingStructuresAllLevelsLessonSeeder/definition.json',
            'database/seeders/V3/Polyglot/PolyglotConditionalAlternativesAndNuanceAllLevelsLessonSeeder/definition.json',
        ];
    }

    /**
     * @return array<int, string>
     */
    private function allV3DefinitionFiles(): array
    {
        $basePath = base_path('database/seeders/V3');
        $files = [];
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($basePath));

        foreach ($iterator as $file) {
            if (! $file->isFile() || $file->getFilename() !== 'definition.json') {
                continue;
            }

            $files[] = str_replace('\\', '/', substr($file->getPathname(), strlen(base_path()) + 1));
        }

        sort($files);

        return $files;
    }
}
