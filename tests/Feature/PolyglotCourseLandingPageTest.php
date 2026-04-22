<?php

namespace Tests\Feature;

use Database\Seeders\V2\Polyglot\PolyglotHaveGotHasGotLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotArticlesAAnTheLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotBeGoingToLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotCanCannotLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotComparativesLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotFinalDrillLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotShouldOughtToLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotFutureSimpleWillLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotPastSimpleIrregularVerbsLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotFirstConditionalLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotPresentPerfectBasicLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotPresentPerfectVsPastSimpleLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotSuperlativesLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotSomeAnyLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotMuchManyALotOfLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotPresentContinuousLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotPastSimpleRegularVerbsLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotPastSimpleToBeLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotPresentSimpleVerbsLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotThereIsThereAreLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotToBeLessonSeeder;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class PolyglotCourseLandingPageTest extends TestCase
{
    use RebuildsComposeTestSchema;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'coming-soon.enabled' => false,
            'tests.tech_info_enabled' => false,
        ]);

        $this->rebuildComposeTestSchema();
        $this->seed(PolyglotToBeLessonSeeder::class);
        $this->seed(PolyglotThereIsThereAreLessonSeeder::class);
        $this->seed(PolyglotHaveGotHasGotLessonSeeder::class);
        $this->seed(PolyglotPresentSimpleVerbsLessonSeeder::class);
        $this->seed(PolyglotCanCannotLessonSeeder::class);
        $this->seed(PolyglotPresentContinuousLessonSeeder::class);
        $this->seed(PolyglotPastSimpleToBeLessonSeeder::class);
        $this->seed(PolyglotPastSimpleRegularVerbsLessonSeeder::class);
        $this->seed(PolyglotPastSimpleIrregularVerbsLessonSeeder::class);
        $this->seed(PolyglotFutureSimpleWillLessonSeeder::class);
        $this->seed(PolyglotArticlesAAnTheLessonSeeder::class);
        $this->seed(PolyglotSomeAnyLessonSeeder::class);
        $this->seed(PolyglotMuchManyALotOfLessonSeeder::class);
        $this->seed(PolyglotComparativesLessonSeeder::class);
        $this->seed(PolyglotSuperlativesLessonSeeder::class);
        $this->seed(PolyglotFinalDrillLessonSeeder::class);
        $this->seed(PolyglotPresentPerfectBasicLessonSeeder::class);
        $this->seed(PolyglotPresentPerfectVsPastSimpleLessonSeeder::class);
        $this->seed(PolyglotFirstConditionalLessonSeeder::class);
        $this->seed(PolyglotBeGoingToLessonSeeder::class);
        $this->seed(PolyglotShouldOughtToLessonSeeder::class);
    }

    public function test_course_landing_route_works_and_renders_lessons_in_order(): void
    {
        $response = $this->get('/courses/polyglot-english-a1');

        $response->assertOk();
        $response->assertSee('Polyglot English A1');
        $response->assertSeeInOrder([
            '/test/polyglot-to-be-a1/step/compose',
            '/test/polyglot-there-is-there-are-a1/step/compose',
            '/test/polyglot-have-got-has-got-a1/step/compose',
            '/test/polyglot-present-simple-verbs-a1/step/compose',
            '/test/polyglot-can-cannot-a1/step/compose',
            '/test/polyglot-present-continuous-a1/step/compose',
            '/test/polyglot-past-simple-to-be-a1/step/compose',
            '/test/polyglot-past-simple-regular-verbs-a1/step/compose',
            '/test/polyglot-past-simple-irregular-verbs-a1/step/compose',
            '/test/polyglot-future-simple-will-a1/step/compose',
            '/test/polyglot-articles-a-an-the-a1/step/compose',
            '/test/polyglot-some-any-a1/step/compose',
            '/test/polyglot-much-many-a-lot-of-a1/step/compose',
            '/test/polyglot-comparatives-a1/step/compose',
            '/test/polyglot-superlatives-a1/step/compose',
            '/test/polyglot-final-drill-a1/step/compose',
        ], false);
    }

    public function test_lesson_urls_on_course_page_point_to_compose_routes(): void
    {
        $response = $this->get('/courses/polyglot-english-a1');

        $response->assertSee('/test/polyglot-to-be-a1/step/compose', false);
        $response->assertSee('/test/polyglot-there-is-there-are-a1/step/compose', false);
        $response->assertSee('/test/polyglot-have-got-has-got-a1/step/compose', false);
        $response->assertSee('/test/polyglot-present-simple-verbs-a1/step/compose', false);
        $response->assertSee('/test/polyglot-can-cannot-a1/step/compose', false);
        $response->assertSee('/test/polyglot-present-continuous-a1/step/compose', false);
        $response->assertSee('/test/polyglot-past-simple-to-be-a1/step/compose', false);
        $response->assertSee('/test/polyglot-past-simple-regular-verbs-a1/step/compose', false);
        $response->assertSee('/test/polyglot-past-simple-irregular-verbs-a1/step/compose', false);
        $response->assertSee('/test/polyglot-future-simple-will-a1/step/compose', false);
        $response->assertSee('/test/polyglot-articles-a-an-the-a1/step/compose', false);
        $response->assertSee('/test/polyglot-some-any-a1/step/compose', false);
        $response->assertSee('/test/polyglot-much-many-a-lot-of-a1/step/compose', false);
        $response->assertSee('/test/polyglot-comparatives-a1/step/compose', false);
        $response->assertSee('/test/polyglot-superlatives-a1/step/compose', false);
        $response->assertSee('/test/polyglot-final-drill-a1/step/compose', false);
    }

    public function test_course_page_renders_lesson_metadata_and_status_hooks(): void
    {
        $response = $this->get('/courses/polyglot-english-a1');

        $response->assertSee('data-polyglot-course-root', false);
        $response->assertSee('data-polyglot-course-slug="polyglot-english-a1"', false);
        $response->assertSee('data-lesson-order="1"', false);
        $response->assertSee('data-lesson-order="2"', false);
        $response->assertSee('data-lesson-order="3"', false);
        $response->assertSee('data-lesson-order="4"', false);
        $response->assertSee('data-lesson-order="5"', false);
        $response->assertSee('data-lesson-order="6"', false);
        $response->assertSee('data-lesson-order="7"', false);
        $response->assertSee('data-lesson-order="8"', false);
        $response->assertSee('data-lesson-order="9"', false);
        $response->assertSee('data-lesson-order="10"', false);
        $response->assertSee('data-lesson-order="11"', false);
        $response->assertSee('data-lesson-order="12"', false);
        $response->assertSee('data-lesson-order="13"', false);
        $response->assertSee('data-lesson-order="14"', false);
        $response->assertSee('data-lesson-order="15"', false);
        $response->assertSee('data-lesson-order="16"', false);
        $response->assertSee('data-course-lesson-status="current"', false);
        $response->assertSee('data-course-lesson-status="locked"', false);
        $response->assertDontSee('data-course-lesson-status="planned"', false);
        $response->assertSee('data-course-lesson-card', false);
        $response->assertSee('data-course-status-badge', false);
        $response->assertSee('data-course-lesson-action', false);
        $response->assertDontSee('data-course-lesson-action-disabled', false);
        $response->assertSee('data-polyglot-planned-lessons="16"', false);
        $response->assertSee('data-polyglot-implemented-lessons="16"', false);
        $response->assertSee('data-polyglot-course-content-complete="1"', false);
        $response->assertSee('data-polyglot-course-learner-complete="0"', false);
        $response->assertSee('data-course-content-complete-banner', false);
        $response->assertSee('data-course-learner-complete-banner', false);
        $response->assertSee('data-course-repeat-link', false);
        $response->assertSee('data-course-reset-progress-secondary', false);
        $response->assertSee('16 / 16');
        $response->assertSee(__('frontend.tests.course.course_fully_available_title'));
    }

    public function test_course_reset_ui_is_present(): void
    {
        $response = $this->get('/courses/polyglot-english-a1');

        $response->assertSee('data-course-reset-progress', false);
        $response->assertSee(__('frontend.tests.course.back_to_lessons'));
    }

    public function test_home_page_renders_public_entry_points_to_polyglot_course_for_guest_user(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('/courses/polyglot-english-a1', false);
        $response->assertSee('/courses/polyglot-english-a2', false);
        $response->assertSee(__('public.nav.polyglot_course'));
        $response->assertSee(__('public.home.polyglot_title'));
        $response->assertSee(__('public.home.polyglot_a2_title'));
    }

    public function test_polyglot_course_entry_point_renders_in_english_locale_without_missing_translation_keys(): void
    {
        app()->setLocale('en');

        $response = $this->withSession(['locale' => 'en'])->get('/');

        $response->assertOk();
        $response->assertSee('/courses/polyglot-english-a1', false);
        $response->assertSee('/courses/polyglot-english-a2', false);
        $response->assertSee('Polyglot course');
        $response->assertDontSee('public.nav.polyglot_course');
        $response->assertDontSee('public.home.polyglot_title');
        $response->assertDontSee('public.home.polyglot_a2_title');
    }

    public function test_a1_course_page_renders_continue_with_a2_cta_in_complete_state(): void
    {
        $response = $this->get('/courses/polyglot-english-a1');

        $response->assertOk();
        $response->assertSee('data-course-continue-a2-link', false);
        $response->assertSee('/courses/polyglot-english-a2', false);
        $response->assertSee(__('frontend.tests.course.continue_with_polyglot_a2'));
    }

    public function test_a2_course_landing_route_works_and_shows_five_implemented_lessons_with_planned_roadmap(): void
    {
        $response = $this->get('/courses/polyglot-english-a2');

        $response->assertOk();
        $response->assertSee('Polyglot English A2');
        $response->assertSee('/test/polyglot-present-perfect-basic-a2/step/compose', false);
        $response->assertSee('/test/polyglot-present-perfect-vs-past-simple-a2/step/compose', false);
        $response->assertSee('/test/polyglot-first-conditional-a2/step/compose', false);
        $response->assertSee('/test/polyglot-be-going-to-a2/step/compose', false);
        $response->assertSee('/test/polyglot-should-ought-to-a2/step/compose', false);
        $response->assertSee('data-polyglot-course-slug="polyglot-english-a2"', false);
        $response->assertSee('data-polyglot-planned-lessons="16"', false);
        $response->assertSee('data-polyglot-implemented-lessons="5"', false);
        $response->assertSee('data-polyglot-course-content-complete="0"', false);
        $response->assertSee('data-course-lesson-status="current"', false);
        $response->assertSee('data-course-lesson-status="locked"', false);
        $response->assertSee('data-course-lesson-status="planned"', false);
        $response->assertSee('data-course-lesson-action', false);
        $response->assertSee('data-course-lesson-action-disabled', false);
        $response->assertSee('5 / 16');
        $response->assertSee(__('frontend.tests.course.planned_remaining', ['count' => 11]));
        $response->assertDontSee('data-course-content-complete-banner', false);
        $response->assertDontSee('data-course-continue-a2-link', false);
    }
}
