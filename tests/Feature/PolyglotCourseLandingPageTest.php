<?php

namespace Tests\Feature;

use App\Models\Question;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\Support\PreparesPolyglotCourseFixtures;
use Tests\TestCase;

class PolyglotCourseLandingPageTest extends TestCase
{
    use PreparesPolyglotCourseFixtures;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'coming-soon.enabled' => false,
            'tests.tech_info_enabled' => false,
        ]);

        $this->preparePolyglotCourseFixtures();
    }

    public function test_private_course_fixture_restoration_is_independent_of_previous_mutations(): void
    {
        $count = Question::count();
        $first = Question::first()->id;
        $database = DB::connection()->getDatabaseName();
        $exports = config('questions.export_path');
        DB::table('questions')->where('id', $first)->delete();
        session(['course-isolation-sentinel' => 'old']);
        Cache::put('course-isolation-sentinel', 'old');
        $this->refreshApplication();
        $this->preparePolyglotCourseFixtures();
        $this->assertSame($count, Question::count());
        $this->assertTrue(Question::whereKey($first)->exists());
        $this->assertNotSame($database, DB::connection()->getDatabaseName());
        $this->assertNotSame($exports, config('questions.export_path'));
        $this->assertNull(session('course-isolation-sentinel'));
        $this->assertNull(Cache::get('course-isolation-sentinel'));
    }

    public function test_course_landing_route_works_and_renders_lessons_in_order(): void
    {
        $response = $this->get('/courses/sentence-builder-english-a1');

        $response->assertOk();
        $response->assertSee('English Sentence Builder A1');
        $response->assertSeeInOrder([
            '/test/sentence-builder-to-be-a1/step/compose',
            '/test/sentence-builder-there-is-there-are-a1/step/compose',
            '/test/sentence-builder-have-got-has-got-a1/step/compose',
            '/test/sentence-builder-present-simple-verbs-a1/step/compose',
            '/test/sentence-builder-can-cannot-a1/step/compose',
            '/test/sentence-builder-present-continuous-a1/step/compose',
            '/test/sentence-builder-past-simple-to-be-a1/step/compose',
            '/test/sentence-builder-past-simple-regular-verbs-a1/step/compose',
            '/test/sentence-builder-past-simple-irregular-verbs-a1/step/compose',
            '/test/sentence-builder-future-simple-will-a1/step/compose',
            '/test/sentence-builder-articles-a-an-the-a1/step/compose',
            '/test/sentence-builder-some-any-a1/step/compose',
            '/test/sentence-builder-much-many-a-lot-of-a1/step/compose',
            '/test/sentence-builder-comparatives-a1/step/compose',
            '/test/sentence-builder-superlatives-a1/step/compose',
            '/test/sentence-builder-final-drill-a1/step/compose',
        ], false);
    }

    public function test_lesson_urls_on_course_page_point_to_compose_routes(): void
    {
        $response = $this->get('/courses/sentence-builder-english-a1');

        $response->assertSee('/test/sentence-builder-to-be-a1/step/compose', false);
        $response->assertSee('/test/sentence-builder-there-is-there-are-a1/step/compose', false);
        $response->assertSee('/test/sentence-builder-have-got-has-got-a1/step/compose', false);
        $response->assertSee('/test/sentence-builder-present-simple-verbs-a1/step/compose', false);
        $response->assertSee('/test/sentence-builder-can-cannot-a1/step/compose', false);
        $response->assertSee('/test/sentence-builder-present-continuous-a1/step/compose', false);
        $response->assertSee('/test/sentence-builder-past-simple-to-be-a1/step/compose', false);
        $response->assertSee('/test/sentence-builder-past-simple-regular-verbs-a1/step/compose', false);
        $response->assertSee('/test/sentence-builder-past-simple-irregular-verbs-a1/step/compose', false);
        $response->assertSee('/test/sentence-builder-future-simple-will-a1/step/compose', false);
        $response->assertSee('/test/sentence-builder-articles-a-an-the-a1/step/compose', false);
        $response->assertSee('/test/sentence-builder-some-any-a1/step/compose', false);
        $response->assertSee('/test/sentence-builder-much-many-a-lot-of-a1/step/compose', false);
        $response->assertSee('/test/sentence-builder-comparatives-a1/step/compose', false);
        $response->assertSee('/test/sentence-builder-superlatives-a1/step/compose', false);
        $response->assertSee('/test/sentence-builder-final-drill-a1/step/compose', false);
    }

    public function test_course_page_renders_lesson_metadata_and_status_hooks(): void
    {
        $response = $this->get('/courses/sentence-builder-english-a1');

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
        $response->assertSee(__('frontend.tests.course.course_fully_available_title', [
            'course' => 'English Sentence Builder A1',
        ]));
    }

    public function test_course_reset_ui_is_present(): void
    {
        $response = $this->get('/courses/sentence-builder-english-a1');

        $response->assertSee('data-course-reset-progress', false);
        $response->assertSee(__('frontend.tests.course.back_to_lessons'));
    }

    public function test_home_page_renders_public_entry_points_to_polyglot_course_for_guest_user(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('/courses/sentence-builder-english-a1', false);
        $response->assertSee('/courses/sentence-builder-english-a2', false);
        $response->assertSee('/courses/sentence-builder-english-b1', false);
        $response->assertSee(__('public.nav.polyglot_course'));
        $response->assertSee(__('public.home.polyglot_title'));
        $response->assertSee(__('public.home.polyglot_a2_title'));
        $response->assertSee(__('public.home.polyglot_b1_title'));
    }

    public function test_polyglot_course_entry_point_renders_in_english_locale_without_missing_translation_keys(): void
    {
        app()->setLocale('en');

        $response = $this->withSession(['locale' => 'en'])->get('/');

        $response->assertOk();
        $response->assertSee('/courses/sentence-builder-english-a1', false);
        $response->assertSee('/courses/sentence-builder-english-a2', false);
        $response->assertSee('/courses/sentence-builder-english-b1', false);
        $response->assertSee('Sentence Builder course');
        $response->assertDontSee('public.nav.polyglot_course');
        $response->assertDontSee('public.home.polyglot_title');
        $response->assertDontSee('public.home.polyglot_a2_title');
        $response->assertDontSee('public.home.polyglot_b1_title');
    }

    public function test_a1_course_page_renders_continue_with_a2_cta_in_complete_state(): void
    {
        $response = $this->get('/courses/sentence-builder-english-a1');

        $response->assertOk();
        $response->assertSee('data-course-continue-a2-link', false);
        $response->assertSee('/courses/sentence-builder-english-a2', false);
        $response->assertSee(__('frontend.tests.course.continue_with_next_course', [
            'course' => 'English Sentence Builder A2',
        ]));
    }

    public function test_a2_course_landing_route_works_and_shows_fully_complete_sixteen_lesson_course(): void
    {
        $response = $this->get('/courses/sentence-builder-english-a2');

        $response->assertOk();
        $response->assertSee('English Sentence Builder A2');
        $response->assertSee('/test/sentence-builder-present-perfect-basic-a2/step/compose', false);
        $response->assertSee('/test/sentence-builder-present-perfect-vs-past-simple-a2/step/compose', false);
        $response->assertSee('/test/sentence-builder-first-conditional-a2/step/compose', false);
        $response->assertSee('/test/sentence-builder-be-going-to-a2/step/compose', false);
        $response->assertSee('/test/sentence-builder-should-ought-to-a2/step/compose', false);
        $response->assertSee('/test/sentence-builder-must-have-to-a2/step/compose', false);
        $response->assertSee('/test/sentence-builder-gerund-vs-infinitive-a2/step/compose', false);
        $response->assertSee('/test/sentence-builder-past-continuous-a2/step/compose', false);
        $response->assertSee('/test/sentence-builder-present-perfect-time-expressions-a2/step/compose', false);
        $response->assertSee('/test/sentence-builder-relative-clauses-a2/step/compose', false);
        $response->assertSee('/test/sentence-builder-passive-voice-basics-a2/step/compose', false);
        $response->assertSee('/test/sentence-builder-reported-speech-basics-a2/step/compose', false);
        $response->assertSee('/test/sentence-builder-used-to-a2/step/compose', false);
        $response->assertSee('/test/sentence-builder-question-tags-basics-a2/step/compose', false);
        $response->assertSee('/test/sentence-builder-second-conditional-basics-a2/step/compose', false);
        $response->assertSee('/test/sentence-builder-final-drill-a2/step/compose', false);
        $response->assertSee('data-polyglot-course-slug="polyglot-english-a2"', false);
        $response->assertSee('data-polyglot-planned-lessons="16"', false);
        $response->assertSee('data-polyglot-implemented-lessons="16"', false);
        $response->assertSee('data-polyglot-course-content-complete="1"', false);
        $response->assertSee('data-course-lesson-status="current"', false);
        $response->assertSee('data-course-lesson-status="locked"', false);
        $response->assertDontSee('data-course-lesson-status="planned"', false);
        $response->assertSee('data-course-lesson-action', false);
        $response->assertDontSee('data-course-lesson-action-disabled', false);
        $response->assertSee('16 / 16');
        $response->assertSee('data-course-content-complete-banner', false);
        $response->assertSee(__('frontend.tests.course.course_fully_available_title', [
            'course' => 'English Sentence Builder A2',
        ]));
        $response->assertSee('data-course-learner-complete-banner', false);
        $response->assertSee('data-course-continue-b1-link', false);
        $response->assertSee('/courses/sentence-builder-english-b1', false);
        $response->assertSee(__('frontend.tests.course.continue_with_next_course', [
            'course' => 'English Sentence Builder B1',
        ]));
    }

    public function test_b1_course_landing_route_works_and_shows_nine_implemented_lessons_with_planned_roadmap(): void
    {
        $response = $this->get('/courses/sentence-builder-english-b1');

        $response->assertOk();
        $response->assertSee('English Sentence Builder B1');
        $response->assertSee('/test/sentence-builder-present-perfect-continuous-basics-b1/step/compose', false);
        $response->assertSee('/test/sentence-builder-present-perfect-continuous-vs-present-perfect-b1/step/compose', false);
        $response->assertSee('/test/sentence-builder-past-perfect-basics-b1/step/compose', false);
        $response->assertSee('/test/sentence-builder-narrative-tenses-basics-b1/step/compose', false);
        $response->assertSee('/test/sentence-builder-future-continuous-basics-b1/step/compose', false);
        $response->assertSee('/test/sentence-builder-future-perfect-basics-b1/step/compose', false);
        $response->assertSee('/test/sentence-builder-passive-voice-with-modals-b1/step/compose', false);
        $response->assertSee('/test/sentence-builder-reported-questions-b1/step/compose', false);
        $response->assertSee('/test/sentence-builder-reported-commands-and-requests-b1/step/compose', false);
        $response->assertSee('data-polyglot-course-slug="polyglot-english-b1"', false);
        $response->assertSee('data-polyglot-planned-lessons="16"', false);
        $response->assertSee('data-polyglot-implemented-lessons="9"', false);
        $response->assertSee('9 / 16');
        $response->assertSee('data-course-lesson-status="current"', false);
        $response->assertSee('data-course-lesson-status="planned"', false);
        $response->assertSee('data-course-lesson-action-disabled', false);
        $response->assertSee('polyglot-past-perfect-basics-b1');
        $response->assertSee('polyglot-narrative-tenses-basics-b1');
        $response->assertSee('polyglot-future-continuous-basics-b1');
        $response->assertSee('polyglot-future-perfect-basics-b1');
        $response->assertSee('polyglot-passive-voice-with-modals-b1');
        $response->assertSee('polyglot-reported-questions-b1');
        $response->assertSee('polyglot-reported-commands-and-requests-b1');
        $response->assertSee('data-course-continue-b2-link', false);
        $response->assertSee('/courses/sentence-builder-english-b2', false);
        $response->assertSee(__('frontend.tests.course.continue_with_next_course', [
            'course' => 'English Sentence Builder B2',
        ]));
    }
}
