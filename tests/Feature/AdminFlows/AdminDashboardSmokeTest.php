<?php

namespace Tests\Feature\AdminFlows;

use Illuminate\Support\Facades\Http;

class AdminDashboardSmokeTest extends SeededAdminFlowTestCase
{
    public function test_admin_dashboard_renders_for_authenticated_admin(): void
    {
        // The shell dashboard only reads local branch metadata on this GET.
        // API mode intentionally redirects to a different dashboard.
        config(['git-deployment.git_mode' => 'ssh']);
        Http::preventStrayRequests();
        $response = $this->withSession($this->adminSession())
            ->get(route('admin.dashboard'));

        $this->assertOkPageWithMarkers($response, [
            'Оновлення сайту з репозиторію',
            'Поточна активна гілка',
            'Admin Hub',
        ]);
        Http::assertNothingSent();
    }

    public function test_api_backend_redirects_to_its_own_dashboard_without_network_requests(): void
    {
        config(['git-deployment.git_mode' => 'api']);
        Http::preventStrayRequests();
        $this->withSession($this->adminSession())
            ->get(route('admin.dashboard'))
            ->assertRedirect(route('deployment.native.index'));
        Http::assertNothingSent();
    }
}
