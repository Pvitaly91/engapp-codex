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

    public function test_plaintext_host_password_must_not_override_the_isolated_hash_fixture(): void
    {
        $this->assertNull(config('admin.password'));
        $credentials = [
            'username' => AdminRouteMatrix::ADMIN_USERNAME,
            'password' => AdminRouteMatrix::ADMIN_PASSWORD,
        ];

        // Reproduce the historical isolation mechanism with synthetic credentials only:
        // the controller intentionally gives an explicit plaintext password precedence.
        config(['admin.password' => 'unrelated-host-password']);
        $this->post(route('login.perform'), $credentials)
            ->assertSessionHasErrors('username')
            ->assertSessionMissing('admin_authenticated');

        config(['admin.password' => null]);
        $this->post(route('login.perform'), $credentials)
            ->assertRedirect(route('admin.dashboard'))
            ->assertSessionHas('admin_authenticated', true);
    }
}
