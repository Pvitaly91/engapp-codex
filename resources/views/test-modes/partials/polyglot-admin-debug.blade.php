@php
    $debug = is_array($debugPayload ?? null) ? $debugPayload : [];
    $lesson = is_array($debug['lesson'] ?? null) ? $debug['lesson'] : [];
    $course = is_array($debug['course'] ?? null) ? $debug['course'] : [];
    $theory = is_array($debug['theory'] ?? null) ? $debug['theory'] : [];
    $completion = is_array($debug['completion'] ?? null) ? $debug['completion'] : [];
    $storageKeys = is_array($debug['storage_keys'] ?? null) ? $debug['storage_keys'] : [];
    $questions = is_array($debug['questions'] ?? null) ? $debug['questions'] : [];
    $jsonOptions = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
    $json = static fn ($value): string => json_encode($value, $jsonOptions) ?: '{}';
    $label = static fn (string $key): string => __("frontend.polyglot.admin_debug.{$key}");
    $value = static fn ($item): string => filled($item) ? (string) $item : 'n/a';
@endphp

@if($debug !== [])
<section id="polyglot-admin-debug"
         class="mx-auto mt-8 w-full max-w-5xl rounded-[20px] border p-4 surface-card-strong sm:rounded-[28px] sm:p-6"
         style="border-color: #f59b2f;"
         data-polyglot-admin-debug="1"
         data-polyglot-debug-lesson-slug="{{ data_get($lesson, 'slug') }}"
         data-polyglot-debug-course-slug="{{ data_get($course, 'slug') }}"
         data-polyglot-debug-next-lesson-slug="{{ data_get($lesson, 'next_lesson_slug') }}"
         data-polyglot-debug-current-progress-key="{{ data_get($storageKeys, 'lesson_progress') }}"
         data-polyglot-debug-course-progress-key="{{ data_get($storageKeys, 'course_progress') }}">
    <details>
        <summary class="cursor-pointer text-base font-extrabold sm:text-lg">
            {{ $label('debug_title') }}
        </summary>

        <div class="mt-5 space-y-6">
            <div class="rounded-[18px] border px-4 py-3 text-sm font-semibold"
                 style="border-color: #f59b2f; background: #fff8ec; color: #7c3f00;">
                {{ $label('contains_answers_warning') }}
            </div>

            <div data-polyglot-debug-status
                 class="hidden rounded-[18px] border px-4 py-3 text-sm"
                 style="border-color: var(--line); color: var(--text);"></div>

            <section class="grid gap-4 lg:grid-cols-2">
                <div class="rounded-[18px] border p-4" style="border-color: var(--line);">
                    <h3 class="text-sm font-extrabold">{{ $label('lesson_info') }}</h3>
                    <dl class="mt-3 grid gap-2 text-sm">
                        <div><dt class="font-semibold">course_slug</dt><dd>{{ $value(data_get($course, 'slug')) }}</dd></div>
                        <div><dt class="font-semibold">lesson_slug</dt><dd>{{ $value(data_get($lesson, 'slug')) }}</dd></div>
                        <div><dt class="font-semibold">lesson_order</dt><dd>{{ $value(data_get($lesson, 'order')) }}</dd></div>
                        <div><dt class="font-semibold">title</dt><dd>{{ $value(data_get($lesson, 'title')) }}</dd></div>
                        <div><dt class="font-semibold">topic / level</dt><dd>{{ $value(data_get($lesson, 'topic')) }} / {{ $value(data_get($lesson, 'level')) }}</dd></div>
                        <div><dt class="font-semibold">mode / question_type</dt><dd>{{ $value(data_get($lesson, 'mode')) }} / {{ $value(data_get($lesson, 'question_type')) }}</dd></div>
                        <div><dt class="font-semibold">total_questions</dt><dd>{{ count($questions) }}</dd></div>
                    </dl>
                </div>

                <div class="rounded-[18px] border p-4" style="border-color: var(--line);">
                    <h3 class="text-sm font-extrabold">{{ $label('course_unlock_info') }}</h3>
                    <dl class="mt-3 grid gap-2 text-sm">
                        <div><dt class="font-semibold">previous_lesson_slug</dt><dd>{{ $value(data_get($lesson, 'previous_lesson_slug')) }}</dd></div>
                        <div><dt class="font-semibold">next_lesson_slug</dt><dd>{{ $value(data_get($lesson, 'next_lesson_slug')) }}</dd></div>
                        <div><dt class="font-semibold">compose_route</dt><dd class="break-all">{{ $value(data_get($lesson, 'compose_route')) }}</dd></div>
                        <div><dt class="font-semibold">course_route</dt><dd class="break-all">{{ $value(data_get($course, 'route')) }}</dd></div>
                        <div><dt class="font-semibold">theory_route</dt><dd class="break-all">{{ $value(data_get($theory, 'route')) }}</dd></div>
                        <div><dt class="font-semibold">theory_binding</dt><dd>{{ $value(data_get($theory, 'title')) }} ({{ $value(data_get($theory, 'page_slug')) }})</dd></div>
                        <div><dt class="font-semibold">completion</dt><dd><code>{{ $json($completion) }}</code></dd></div>
                    </dl>
                </div>
            </section>

            <section class="rounded-[18px] border p-4" style="border-color: var(--line);">
                <h3 class="text-sm font-extrabold">{{ $label('local_storage_keys') }}</h3>
                <dl class="mt-3 grid gap-2 text-sm">
                    @foreach($storageKeys as $key => $storageKey)
                        <div class="grid gap-1 sm:grid-cols-[14rem_1fr]">
                            <dt class="font-semibold">{{ $key }}</dt>
                            <dd class="break-all"><code>{{ $value($storageKey) }}</code></dd>
                        </div>
                    @endforeach
                </dl>
            </section>

            <section class="rounded-[18px] border p-4" style="border-color: var(--line);">
                <h3 class="text-sm font-extrabold">{{ $label('questions_and_answers') }}</h3>
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full border-collapse text-left text-sm">
                        <thead>
                            <tr style="border-bottom: 1px solid var(--line);">
                                <th class="px-3 py-2">#</th>
                                <th class="px-3 py-2">{{ $label('source_text') }}</th>
                                <th class="px-3 py-2">{{ $label('correct_answer') }}</th>
                                <th class="px-3 py-2">{{ $label('tokens') }}</th>
                                <th class="px-3 py-2">{{ $label('distractors') }}</th>
                                <th class="px-3 py-2">{{ $label('meta') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($questions as $question)
                                @php
                                    $correctTokens = is_array($question['correct_token_values'] ?? null) ? $question['correct_token_values'] : [];
                                    $correctTokenIds = is_array($question['correct_token_ids'] ?? null) ? $question['correct_token_ids'] : [];
                                    $distractors = is_array($question['distractors'] ?? null) ? $question['distractors'] : [];
                                    $tags = is_array($question['grammar_tags'] ?? null) ? $question['grammar_tags'] : [];
                                @endphp
                                <tr style="border-bottom: 1px solid var(--line);">
                                    <td class="px-3 py-3 align-top font-semibold">{{ data_get($question, 'position') }}</td>
                                    <td class="px-3 py-3 align-top">
                                        <div>{{ $value(data_get($question, 'source_text_uk')) }}</div>
                                        <div class="mt-1 text-xs" style="color: var(--muted);">{{ $value(data_get($question, 'uuid')) }}</div>
                                    </td>
                                    <td class="px-3 py-3 align-top">
                                        <div class="font-semibold" data-polyglot-debug-correct-answer>{{ $value(data_get($question, 'target_text')) }}</div>
                                    </td>
                                    <td class="px-3 py-3 align-top">
                                        <div class="flex max-w-md flex-wrap gap-1.5">
                                            @foreach($correctTokens as $token)
                                                <span class="rounded-[10px] border px-2 py-1 text-xs font-semibold" style="border-color: var(--line);">
                                                    {{ $loop->iteration }}. {{ $token }}
                                                    @if(!empty($correctTokenIds[$loop->index]))
                                                        <span style="color: var(--muted);">({{ $correctTokenIds[$loop->index] }})</span>
                                                    @endif
                                                </span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="px-3 py-3 align-top">
                                        <div class="flex max-w-md flex-wrap gap-1.5">
                                            @forelse($distractors as $token)
                                                <span class="rounded-[10px] border px-2 py-1 text-xs" style="border-color: var(--line);">
                                                    {{ data_get($token, 'value') }}
                                                    @if(filled(data_get($token, 'id')))
                                                        <span style="color: var(--muted);">({{ data_get($token, 'id') }})</span>
                                                    @endif
                                                </span>
                                            @empty
                                                <span style="color: var(--muted);">n/a</span>
                                            @endforelse
                                        </div>
                                    </td>
                                    <td class="px-3 py-3 align-top">
                                        @if(filled(data_get($question, 'hint_uk')))
                                            <div><span class="font-semibold">{{ $label('hint') }}:</span> {{ data_get($question, 'hint_uk') }}</div>
                                        @endif
                                        @if($tags !== [])
                                            <div class="mt-1"><span class="font-semibold">{{ $label('tags') }}:</span> {{ implode(', ', $tags) }}</div>
                                        @endif
                                        <details class="mt-2">
                                            <summary class="cursor-pointer text-xs font-semibold">{{ $label('raw_json') }}</summary>
                                            <pre class="mt-2 max-h-80 overflow-auto rounded-[14px] border p-3 text-xs" style="border-color: var(--line);">{{ $json($question) }}</pre>
                                        </details>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="grid gap-4 lg:grid-cols-2">
                <div class="rounded-[18px] border p-4" style="border-color: var(--line);">
                    <h3 class="text-sm font-extrabold">{{ $label('unlock_tools') }}</h3>
                    <div class="mt-4 grid gap-3 text-sm">
                        <label class="grid gap-1">
                            <span class="font-semibold">{{ $label('required_answered') }}</span>
                            <input type="number" min="0" class="rounded-[12px] border px-3 py-2" style="border-color: var(--line);" data-polyglot-debug-input="answered" value="{{ max(1, count($questions)) }}">
                        </label>
                        <label class="grid gap-1">
                            <span class="font-semibold">{{ $label('score_rating_percent') }}</span>
                            <input type="number" min="0" max="100" class="rounded-[12px] border px-3 py-2" style="border-color: var(--line);" data-polyglot-debug-input="ratingPercent" value="100">
                        </label>
                        <label class="grid gap-1">
                            <span class="font-semibold">{{ $label('required_correct') }}</span>
                            <input type="number" min="0" class="rounded-[12px] border px-3 py-2" style="border-color: var(--line);" data-polyglot-debug-input="requiredCorrect" value="{{ max(1, count($questions)) }}">
                        </label>
                        <label class="grid gap-1">
                            <span class="font-semibold">{{ $label('minimum_rating_percent') }}</span>
                            <input type="number" min="0" max="100" class="rounded-[12px] border px-3 py-2" style="border-color: var(--line);" data-polyglot-debug-input="minimumRatingPercent" value="90">
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" data-polyglot-debug-input="forceUnlockNext">
                            <span>{{ $label('force_unlock_next') }}</span>
                        </label>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" class="rounded-full bg-ocean px-4 py-2 text-sm font-extrabold text-white" data-polyglot-debug-action="simulate-progress">{{ $label('simulate_progress') }}</button>
                            <button type="button" class="rounded-full bg-ocean px-4 py-2 text-sm font-extrabold text-white" data-polyglot-debug-action="mark-complete">{{ $label('mark_complete') }}</button>
                            <button type="button" class="rounded-full border px-4 py-2 text-sm font-bold" style="border-color: var(--line);" data-polyglot-debug-action="unlock-next">{{ $label('unlock_next') }}</button>
                            <button type="button" class="rounded-full border px-4 py-2 text-sm font-bold" style="border-color: var(--line);" data-polyglot-debug-action="apply-lesson-policy">{{ $label('apply_lesson_policy') }}</button>
                            <button type="button" class="rounded-full border px-4 py-2 text-sm font-bold" style="border-color: var(--line);" data-polyglot-debug-action="apply-course-policy">{{ $label('apply_course_policy') }}</button>
                            <button type="button" class="rounded-full border px-4 py-2 text-sm font-bold" style="border-color: var(--line);" data-polyglot-debug-action="clear-debug-overrides">{{ $label('clear_debug_overrides') }}</button>
                        </div>
                    </div>
                </div>

                <div class="rounded-[18px] border p-4" style="border-color: var(--line);">
                    <h3 class="text-sm font-extrabold">{{ $label('reset_tools') }}</h3>
                    <div class="mt-4 grid gap-3 text-sm">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" data-polyglot-debug-input="clearPolicyOnReset" checked>
                            <span>{{ $label('reset_policy_with_lesson') }}</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" data-polyglot-debug-input="removeNextProgress">
                            <span>{{ $label('remove_next_progress') }}</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" data-polyglot-debug-input="allPolyglotCourses">
                            <span>{{ $label('all_polyglot_courses') }}</span>
                        </label>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" class="rounded-full border px-4 py-2 text-sm font-bold" style="border-color: var(--line);" data-polyglot-debug-action="reset-current-lesson">{{ $label('reset_current_lesson') }}</button>
                            <button type="button" class="rounded-full border px-4 py-2 text-sm font-bold" style="border-color: var(--line);" data-polyglot-debug-action="reset-current-completion">{{ $label('reset_current_completion') }}</button>
                            <button type="button" class="rounded-full border px-4 py-2 text-sm font-bold" style="border-color: var(--line);" data-polyglot-debug-action="reset-next-unlock">{{ $label('reset_next_unlock') }}</button>
                            <button type="button" class="rounded-full border px-4 py-2 text-sm font-bold" style="border-color: var(--line);" data-polyglot-debug-action="reset-course-progress">{{ $label('reset_course_progress') }}</button>
                            <button type="button" class="rounded-full border px-4 py-2 text-sm font-bold" style="border-color: var(--line);" data-polyglot-debug-action="clear-debug-keys">{{ $label('clear_all_debug_keys') }}</button>
                            <button type="button" class="rounded-full border px-4 py-2 text-sm font-bold" style="border-color: var(--line);" data-polyglot-debug-action="refresh-state">{{ $label('refresh_debug_state') }}</button>
                        </div>
                    </div>
                </div>
            </section>

            <section class="rounded-[18px] border p-4" style="border-color: var(--line);">
                <h3 class="text-sm font-extrabold">{{ $label('state_snapshot') }}</h3>
                <pre class="mt-3 max-h-80 overflow-auto rounded-[14px] border p-3 text-xs" style="border-color: var(--line);" data-polyglot-debug-state>{}</pre>
            </section>

            <section class="rounded-[18px] border p-4" style="border-color: var(--line);">
                <details>
                    <summary class="cursor-pointer text-sm font-extrabold">{{ $label('raw_json') }}</summary>
                    <pre class="mt-3 max-h-[36rem] overflow-auto rounded-[14px] border p-3 text-xs" style="border-color: var(--line);">{{ $json($debug) }}</pre>
                </details>
            </section>
        </div>
    </details>
</section>

<script>
window.__POLYGLOT_ADMIN_DEBUG__ = @json($debug);
window.__POLYGLOT_ADMIN_DEBUG_I18N__ = @json(__('frontend.polyglot.admin_debug'));
</script>
<script type="module" src="{{ asset('js/polyglot/admin-debug.js') }}"></script>
@endif
