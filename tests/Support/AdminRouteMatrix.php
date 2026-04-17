<?php

namespace Tests\Support;

final class AdminRouteMatrix
{
    public const ADMIN_USERNAME = 'admin';

    public const ADMIN_PASSWORD = 'secret-admin';

    public const DASHBOARD_PATH = '/admin';

    public const SITE_TREE_PATH = '/admin/site-tree';

    public const SEED_RUNS_PATH = '/admin/seed-runs';

    public const TEST_TAGS_PATH = '/admin/test-tags/';

    public const SAVED_TESTS_PATH = '/admin/tests';

    public const GRAMMAR_TEST_PATH = '/admin/grammar-test';

    public const SEEDER_CLASS = 'Database\\Seeders\\Admin\\AdminSmokeSeeder';

    public const TAG_NAME = 'Admin Smoke Tag';

    public const TAG_CATEGORY = 'Tenses';

    public const SOURCE_NAME = 'Admin Smoke Source';

    public const QUESTION_CATEGORY = 'Admin Smoke Category';

    public const PAGE_CATEGORY_TITLE = 'Admin Smoke Theory';

    public const PAGE_CATEGORY_SLUG = 'admin-smoke-theory';

    public const PAGE_TITLE = 'Admin Smoke Theory Page';

    public const PAGE_SLUG = 'admin-smoke-theory-page';

    public const LEGACY_TEST_NAME = 'Admin Legacy Test';

    public const LEGACY_TEST_SLUG = 'admin-legacy-test';

    public const SAVED_TEST_NAME = 'Admin Saved Test';

    public const SAVED_TEST_SLUG = 'admin-saved-test';

    public const SAVED_TEST_UUID = '33333333-3333-4333-8333-333333333333';

    public const QUESTION_UUID = '44444444-4444-4444-8444-444444444444';

    public const SITE_TREE_ROOT = 'Admin Grammar Section';

    public const SITE_TREE_CHILD = 'Admin Lesson Node';

    public static function protectedPaths(): array
    {
        return [
            self::DASHBOARD_PATH,
            self::SITE_TREE_PATH,
            self::SEED_RUNS_PATH,
            self::TEST_TAGS_PATH,
            self::SAVED_TESTS_PATH,
            self::GRAMMAR_TEST_PATH,
        ];
    }
}
