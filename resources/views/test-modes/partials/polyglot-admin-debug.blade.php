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
    $tooltip = static fn (string $key): string => __("frontend.polyglot.admin_debug.tooltips.{$key}");
    $value = static fn ($item): string => filled($item) ? (string) $item : 'n/a';
@endphp

@if($debug !== [])
<style>
    #polyglot-admin-debug [data-polyglot-debug-action] {
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

    #polyglot-admin-debug [data-polyglot-debug-action]:hover {
        box-shadow: 0 8px 20px rgb(29 78 216 / 12%);
        filter: brightness(1.02);
    }

    #polyglot-admin-debug [data-polyglot-debug-action]:active,
    #polyglot-admin-debug [data-polyglot-debug-action].polyglot-debug-action-pressed {
        transform: translateY(1px) scale(0.97);
        box-shadow: inset 0 2px 6px rgb(15 23 42 / 16%);
    }

    #polyglot-admin-debug [data-polyglot-debug-action].polyglot-debug-action-working::after {
        content: "";
        position: absolute;
        inset: -4px;
        border: 2px solid rgb(42 112 219 / 45%);
        border-radius: 999px;
        opacity: 0;
        animation: polyglot-debug-button-pulse 520ms ease-out;
        pointer-events: none;
    }

    #polyglot-admin-debug [data-polyglot-debug-input] {
        transition:
            border-color 160ms ease,
            box-shadow 160ms ease,
            background-color 160ms ease;
    }

    #polyglot-admin-debug [data-polyglot-debug-input]:hover,
    #polyglot-admin-debug [data-polyglot-debug-input]:focus {
        border-color: #2a70db !important;
        box-shadow: 0 0 0 3px rgb(42 112 219 / 12%);
    }

    #polyglot-admin-debug .polyglot-debug-help {
        cursor: help;
    }

    @keyframes polyglot-debug-button-pulse {
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

            @include('test-modes.partials.polyglot-admin-debug-controls')

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
                                <th class="px-3 py-2">{{ $label('question_runtime_stats') }}</th>
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
                                <tr style="border-bottom: 1px solid var(--line);"
                                    data-polyglot-debug-question-row
                                    data-polyglot-debug-question-id="{{ data_get($question, 'id') }}"
                                    data-polyglot-debug-question-uuid="{{ data_get($question, 'uuid') }}"
                                    data-polyglot-debug-question-position="{{ data_get($question, 'position') }}">
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
                                        <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold"
                                              style="border-color: var(--line); color: var(--muted);"
                                              data-polyglot-debug-question-stats>
                                            {{ $label('question_not_seen') }}
                                        </span>
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
