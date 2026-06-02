<?php

namespace Tests\Feature\AdminFlows;

use Tests\Support\AdminRouteMatrix;

class AdminSeedRunsSmokeTest extends SeededAdminFlowTestCase
{
    public function test_seed_runs_index_renders_for_authenticated_admin(): void
    {
        $response = $this->withSession($this->adminSession())
            ->get(route('seed-runs.index'));

        $this->assertOkPageWithMarkers($response, [
            'Seed Runs',
            'Невиконані сидери',
            'Виконані сидери',
            'Основні сидери',
        ]);
    }
}
