<?php

namespace Tests\Feature\AdminFlows;

use Tests\Support\AdminRouteMatrix;

class AdminSiteTreeSmokeTest extends SeededAdminFlowTestCase
{
    public function test_site_tree_management_page_renders_for_authenticated_admin(): void
    {
        $response = $this->withSession($this->adminSession())
            ->get(route('site-tree.index'));

        $this->assertOkPageWithMarkers($response, [
            'Структура сайту',
            'Новий варіант',
            AdminRouteMatrix::SITE_TREE_ROOT,
        ]);
    }
}
