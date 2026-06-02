@php
    $courseSlug = (string) ($course['slug'] ?? '');
    $firstLessonSlug = (string) data_get($firstLesson, 'slug', '');
    $implementedLessons = collect($lessons ?? [])
        ->filter(fn ($lesson) => ! empty($lesson['slug']))
        ->values();
    $defaultRequiredAnsweredPercent = 100;
    $defaultMinimumRatingPercent = max(0, min(100, (int) ceil(((float) ($completionRating ?? 4.5)) * 20)));
    $defaultRequiredCorrectPercent = $defaultMinimumRatingPercent;
    $jsonOptions = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
    $json = static fn ($value): string => json_encode($value, $jsonOptions) ?: '{}';
    $label = static fn (string $key): string => __("frontend.polyglot.admin_debug.{$key}");
    $tooltip = static fn (string $key): string => __("frontend.polyglot.admin_debug.tooltips.{$key}");
    $debugPayload = [
        'course' => [
            'slug' => $courseSlug,
            'name' => $course['name'] ?? null,
            'route' => localized_route('courses.show', $courseSlug),
            'planned_total_lessons' => $plannedTotalLessons ?? null,
            'implemented_lessons' => $implementedLessonsCount ?? null,
        ],
        'completion_defaults' => [
            'required_answered_percent' => $defaultRequiredAnsweredPercent,
            'required_correct_percent' => $defaultRequiredCorrectPercent,
            'minimum_rating_percent' => $defaultMinimumRatingPercent,
        ],
        'lessons' => $implementedLessons
            ->map(fn ($lesson) => [
                'slug' => $lesson['slug'] ?? null,
                'lesson_order' => $lesson['lesson_order'] ?? null,
                'name' => $lesson['name'] ?? null,
                'previous_lesson_slug' => $lesson['previous_lesson_slug'] ?? null,
                'next_lesson_slug' => $lesson['next_lesson_slug'] ?? null,
                'compose_url' => $lesson['compose_url'] ?? null,
                'question_count' => $lesson['question_count'] ?? null,
            ])
            ->values()
            ->all(),
        'storage_keys' => [
            'course_progress' => "polyglot_course_progress:{$courseSlug}",
            'legacy_course_progress' => "polyglot_course_state:{$courseSlug}",
            'lesson_progress_prefix' => 'polyglot_progress:',
            'course_debug_policy_prefix' => "polyglot_debug_unlock_policy:{$courseSlug}:",
        ],
        'debug_endpoint' => localized_route('courses.progress.debug', $courseSlug, false),
    ];
@endphp

<style>
    #polyglot-course-admin-debug [data-polyglot-course-debug-action] {
        position: relative;
        transform: translateY(0) scale(1);
        transition:
            transform 120ms ease,
            box-shadow 160ms ease,
            border-color 160ms ease,
            background-color 160ms ease,
            filter 160ms ease;
        will-change: transform, box-shadow;
    }

    #polyglot-course-admin-debug [data-polyglot-course-debug-action]:hover {
        box-shadow: 0 8px 20px rgb(29 78 216 / 12%);
        filter: brightness(1.02);
    }

    #polyglot-course-admin-debug [data-polyglot-course-debug-action]:active,
    #polyglot-course-admin-debug [data-polyglot-course-debug-action].polyglot-debug-action-pressed {
        transform: translateY(1px) scale(0.97);
        box-shadow: inset 0 2px 6px rgb(15 23 42 / 16%);
    }

    #polyglot-course-admin-debug [data-polyglot-course-debug-action].polyglot-debug-action-working::after {
        content: "";
        position: absolute;
        inset: -4px;
        border: 2px solid rgb(42 112 219 / 45%);
        border-radius: 999px;
        opacity: 0;
        animation: polyglot-course-debug-button-pulse 520ms ease-out;
        pointer-events: none;
    }

    #polyglot-course-admin-debug [data-polyglot-course-debug-input] {
        transition:
            border-color 160ms ease,
            box-shadow 160ms ease,
            background-color 160ms ease;
    }

    #polyglot-course-admin-debug [data-polyglot-course-debug-input]:hover,
    #polyglot-course-admin-debug [data-polyglot-course-debug-input]:focus {
        border-color: #2a70db !important;
        box-shadow: 0 0 0 3px rgb(42 112 219 / 12%);
    }

    #polyglot-course-admin-debug .polyglot-debug-help {
        cursor: help;
    }

    @keyframes polyglot-course-debug-button-pulse {
        0% {
            opacity: 0.85;
            transform: scale(0.98);
        }
        100% {
            opacity: 0;
            transform: scale(1.08);
        }
    }
</style>

<section id="polyglot-course-admin-debug"
         class="mt-8 rounded-[24px] border p-4 shadow-card surface-card-strong sm:p-6"
         style="border-color: #f59b2f;"
         data-polyglot-course-admin-debug="1"
         data-polyglot-debug-course-slug="{{ $courseSlug }}"
         data-polyglot-debug-first-lesson-slug="{{ $firstLessonSlug }}"
         data-polyglot-debug-course-progress-key="polyglot_course_progress:{{ $courseSlug }}">
    <details>
        <summary class="cursor-pointer text-base font-extrabold sm:text-lg">
            {{ $label('course_debug_title') }}
        </summary>

        <div class="mt-5 space-y-5">
            <div class="rounded-[18px] border px-4 py-3 text-sm font-semibold"
                 style="border-color: #f59b2f; background: #fff8ec; color: #7c3f00;">
                {{ $label('course_debug_warning') }}
            </div>

            <div data-polyglot-course-debug-status
                 class="hidden rounded-[18px] border px-4 py-3 text-sm"
                 style="border-color: var(--line); color: var(--text);"></div>

            <section class="grid gap-4 lg:grid-cols-[1.1fr_0.9fr]">
                <div class="rounded-[18px] border p-4" style="border-color: var(--line);">
                    <h3 class="text-sm font-extrabold">{{ $label('course_wide_policy') }}</h3>
                    <p class="mt-2 text-sm leading-6" style="color: var(--muted);">
                        {{ $label('course_policy_note') }}
                    </p>
                    <p class="mt-2 text-xs font-semibold" style="color: var(--muted);">
                        {{ $label('first_lesson_question_count') }}: <span data-polyglot-course-debug-first-question-count>{{ data_get($implementedLessons->first(), 'question_count', '—') }}</span>
                    </p>

                    <div class="mt-4 grid gap-3 text-sm sm:grid-cols-3">
                        <label class="polyglot-debug-help grid gap-1" title="{{ $tooltip('required_answered_percent') }}">
                            <span class="font-semibold">{{ $label('required_answered_percent') }}</span>
                            <input type="number" min="0" max="100" class="rounded-[12px] border px-3 py-2" style="border-color: var(--line);" data-polyglot-course-debug-input="requiredAnsweredPercent" value="{{ $defaultRequiredAnsweredPercent }}" title="{{ $tooltip('required_answered_percent') }}" aria-label="{{ $tooltip('required_answered_percent') }}">
                            <span class="text-xs font-semibold" style="color: var(--muted);" data-polyglot-course-debug-preview="requiredAnsweredPercent"></span>
                        </label>
                        <label class="polyglot-debug-help grid gap-1" title="{{ $tooltip('required_correct_percent') }}">
                            <span class="font-semibold">{{ $label('required_correct_percent') }}</span>
                            <input type="number" min="0" max="100" class="rounded-[12px] border px-3 py-2" style="border-color: var(--line);" data-polyglot-course-debug-input="requiredCorrectPercent" value="{{ $defaultRequiredCorrectPercent }}" title="{{ $tooltip('required_correct_percent') }}" aria-label="{{ $tooltip('required_correct_percent') }}">
                            <span class="text-xs font-semibold" style="color: var(--muted);" data-polyglot-course-debug-preview="requiredCorrectPercent"></span>
                        </label>
                        <label class="polyglot-debug-help grid gap-1" title="{{ $tooltip('minimum_rating_percent') }}">
                            <span class="font-semibold">{{ $label('minimum_rating_percent') }}</span>
                            <input type="number" min="0" max="100" class="rounded-[12px] border px-3 py-2" style="border-color: var(--line);" data-polyglot-course-debug-input="minimumRatingPercent" value="{{ $defaultMinimumRatingPercent }}" title="{{ $tooltip('minimum_rating_percent') }}" aria-label="{{ $tooltip('minimum_rating_percent') }}">
                        </label>
                    </div>

                    <label class="polyglot-debug-help mt-4 flex items-center gap-2 text-sm" title="{{ $tooltip('force_unlock_next') }}">
                        <input type="checkbox" data-polyglot-course-debug-input="forceUnlockNext" title="{{ $tooltip('force_unlock_next') }}" aria-label="{{ $tooltip('force_unlock_next') }}">
                        <span>{{ $label('force_unlock_next') }}</span>
                    </label>
                </div>

                <div class="rounded-[18px] border p-4" style="border-color: var(--line);">
                    <h3 class="text-sm font-extrabold">{{ $label('reset_tools') }}</h3>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <button type="button" class="rounded-full bg-ocean px-4 py-2 text-sm font-extrabold text-white" data-polyglot-course-debug-action="apply-course-policy" title="{{ $tooltip('apply_course_policy') }}" aria-label="{{ $tooltip('apply_course_policy') }}">{{ $label('apply_course_policy') }}</button>
                        <button type="button" class="rounded-full border px-4 py-2 text-sm font-bold" style="border-color: var(--line);" data-polyglot-course-debug-action="clear-debug-overrides" title="{{ $tooltip('clear_debug_overrides') }}" aria-label="{{ $tooltip('clear_debug_overrides') }}">{{ $label('clear_debug_overrides') }}</button>
                        <button type="button" class="rounded-full border px-4 py-2 text-sm font-bold" style="border-color: var(--line);" data-polyglot-course-debug-action="reset-course-progress" title="{{ $tooltip('reset_course_progress') }}" aria-label="{{ $tooltip('reset_course_progress') }}">{{ $label('reset_course_progress') }}</button>
                        <button type="button" class="rounded-full border px-4 py-2 text-sm font-bold" style="border-color: var(--line);" data-polyglot-course-debug-action="refresh-state" title="{{ $tooltip('refresh_state') }}" aria-label="{{ $tooltip('refresh_state') }}">{{ $label('refresh_debug_state') }}</button>
                    </div>
                </div>
            </section>

            <section class="grid gap-4 lg:grid-cols-2">
                <div class="rounded-[18px] border p-4" style="border-color: var(--line);">
                    <h3 class="text-sm font-extrabold">{{ $label('lesson_info') }}</h3>
                    <dl class="mt-3 grid gap-2 text-sm">
                        <div><dt class="font-semibold">course_slug</dt><dd>{{ $courseSlug }}</dd></div>
                        <div><dt class="font-semibold">implemented_lessons</dt><dd>{{ $implementedLessonsCount }} / {{ $plannedTotalLessons }}</dd></div>
                        <div><dt class="font-semibold">first_lesson_slug</dt><dd>{{ $firstLessonSlug ?: 'n/a' }}</dd></div>
                        <div><dt class="font-semibold">debug_endpoint</dt><dd class="break-all">{{ localized_route('courses.progress.debug', $courseSlug, false) }}</dd></div>
                    </dl>
                </div>

                <div class="rounded-[18px] border p-4" style="border-color: var(--line);">
                    <h3 class="text-sm font-extrabold">{{ $label('local_storage_keys') }}</h3>
                    <dl class="mt-3 grid gap-2 text-sm">
                        @foreach($debugPayload['storage_keys'] as $key => $storageKey)
                            <div class="grid gap-1 sm:grid-cols-[13rem_1fr]">
                                <dt class="font-semibold">{{ $key }}</dt>
                                <dd class="break-all"><code>{{ $storageKey }}</code></dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
            </section>

            <section class="rounded-[18px] border p-4" style="border-color: var(--line);">
                <h3 class="text-sm font-extrabold">{{ $label('state_snapshot') }}</h3>
                <pre class="mt-3 max-h-80 overflow-auto rounded-[14px] border p-3 text-xs" style="border-color: var(--line);" data-polyglot-course-debug-state>{}</pre>
            </section>

            <section class="rounded-[18px] border p-4" style="border-color: var(--line);">
                <details>
                    <summary class="cursor-pointer text-sm font-extrabold">{{ $label('raw_json') }}</summary>
                    <pre class="mt-3 max-h-[36rem] overflow-auto rounded-[14px] border p-3 text-xs" style="border-color: var(--line);">{{ $json($debugPayload) }}</pre>
                </details>
            </section>
        </div>
    </details>
</section>

<script>
window.__POLYGLOT_COURSE_ADMIN_DEBUG__ = @json($debugPayload);
window.__POLYGLOT_COURSE_ADMIN_DEBUG_I18N__ = @json(__('frontend.polyglot.admin_debug'));
</script>
