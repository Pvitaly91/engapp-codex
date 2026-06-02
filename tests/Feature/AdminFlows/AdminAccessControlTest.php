<?php

namespace Tests\Feature\AdminFlows;

use Tests\Support\AdminRouteMatrix;

class AdminAccessControlTest extends SeededAdminFlowTestCase
{
    public function test_admin_content_management_routes_redirect_to_login_when_unauthenticated(): void
    {
        foreach (AdminRouteMatrix::protectedPaths() as $path) {
            $response = $this->get($path);

            $this->assertRedirectsToLogin($response, $path);
        }
    }

    public function test_valid_admin_login_redirects_to_the_intended_admin_route(): void
    {
        $this->get(AdminRouteMatrix::SITE_TREE_PATH)
            ->assertRedirect(route('login.show'));

        $response = $this->post(route('login.perform'), [
            'username' => AdminRouteMatrix::ADMIN_USERNAME,
            'password' => AdminRouteMatrix::ADMIN_PASSWORD,
        ]);

        $response->assertRedirect(route('site-tree.index'));
        $response->assertSessionHas('admin_authenticated', true);
    }
}
