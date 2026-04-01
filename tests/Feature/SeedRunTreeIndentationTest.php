<?php

namespace Tests\Feature;

use Tests\TestCase;

class SeedRunTreeIndentationTest extends TestCase
{
    public function test_pending_tree_nodes_use_single_indent_step_for_nested_levels(): void
    {
        $nestedHtml = view('seed-runs.partials.pending-node', [
            'node' => [
                'type' => 'folder',
                'name' => 'ChatGpt',
                'path' => 'V3/AI/ChatGpt',
                'children' => [],
                'seeder_count' => 2,
                'class_names' => [
                    'Database\\Seeders\\V3\\AI\\ChatGpt\\ExampleSeeder',
                ],
            ],
            'depth' => 5,
        ])->render();

        $rootHtml = view('seed-runs.partials.pending-node', [
            'node' => [
                'type' => 'folder',
                'name' => 'Database',
                'path' => 'Database',
                'children' => [],
                'seeder_count' => 2,
                'class_names' => [
                    'Database\\Seeders\\ExampleSeeder',
                ],
            ],
            'depth' => 0,
        ])->render();

        $this->assertStringContainsString('margin-left: 1.5rem;', $nestedHtml);
        $this->assertStringNotContainsString('margin-left: 7.5rem;', $nestedHtml);
        $this->assertStringContainsString('margin-left: 0rem;', $rootHtml);
    }

    public function test_executed_tree_nodes_use_single_indent_step_for_nested_levels(): void
    {
        $nestedHtml = view('seed-runs.partials.executed-node', [
            'node' => [
                'type' => 'folder',
                'name' => 'ChatGpt',
                'path' => 'V3/AI/ChatGpt',
                'children' => [],
                'seeder_count' => 2,
                'seed_run_ids' => [],
                'class_names' => [],
                'folder_profile' => [],
            ],
            'depth' => 4,
            'recentSeedRunOrdinals' => collect(),
        ])->render();

        $rootHtml = view('seed-runs.partials.executed-node', [
            'node' => [
                'type' => 'folder',
                'name' => 'Database',
                'path' => 'Database',
                'children' => [],
                'seeder_count' => 2,
                'seed_run_ids' => [],
                'class_names' => [],
                'folder_profile' => [],
            ],
            'depth' => 0,
            'recentSeedRunOrdinals' => collect(),
        ])->render();

        $this->assertStringContainsString('margin-left: 1.5rem;', $nestedHtml);
        $this->assertStringNotContainsString('margin-left: 6rem;', $nestedHtml);
        $this->assertStringContainsString('margin-left: 0rem;', $rootHtml);
    }
}
