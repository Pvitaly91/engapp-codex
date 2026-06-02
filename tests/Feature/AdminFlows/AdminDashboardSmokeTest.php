<?php

namespace Tests\Feature\AdminFlows;

class AdminDashboardSmokeTest extends SeededAdminFlowTestCase
{
    public function test_admin_dashboard_renders_for_authenticated_admin(): void
    {
        $response = $this->withSession($this->adminSession())
            ->get(route('admin.dashboard'));

        $this->assertOkPageWithMarkers($response, [
            'Оновлення сайту з репозиторію',
            'Поточна активна гілка',
            'Admin Hub',
        ]);
    }
}
