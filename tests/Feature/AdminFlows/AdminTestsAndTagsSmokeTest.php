<?php

namespace Tests\Feature\AdminFlows;

use Tests\Support\AdminRouteMatrix;

class AdminTestsAndTagsSmokeTest extends SeededAdminFlowTestCase
{
    public function test_test_tags_page_renders_for_authenticated_admin(): void
    {
        $response = $this->withSession($this->adminSession())
            ->get(route('test-tags.index'));

        $this->assertOkPageWithMarkers($response, [
            'Теги тестів',
            'Агрегація тегів',
            AdminRouteMatrix::TAG_NAME,
        ]);
    }

    public function test_saved_tests_page_renders_for_authenticated_admin(): void
    {
        $response = $this->withSession($this->adminSession())
            ->get(route('saved-tests.list'));

        $this->assertOkPageWithMarkers($response, [
            'Збережені тести',
            'До фільтра / конструктора тестів',
            AdminRouteMatrix::SAVED_TEST_NAME,
        ]);
    }

    public function test_grammar_test_builder_renders_for_authenticated_admin(): void
    {
        $response = $this->withSession($this->adminSession())
            ->get(route('grammar-test'));

        $this->assertOkPageWithMarkers($response, [
            'Конструктор тесту (v2)',
            'Збережені тести',
            AdminRouteMatrix::SOURCE_NAME,
            AdminRouteMatrix::TAG_NAME,
        ]);
    }
}
