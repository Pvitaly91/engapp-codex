<section class="grid gap-4 lg:grid-cols-2">
    <div class="rounded-[18px] border p-4" style="border-color: var(--line);">
        <h3 class="text-sm font-extrabold">{{ $label('unlock_tools') }}</h3>
        <p class="mt-2 text-xs font-semibold" style="color: var(--muted);">
            {{ $label('total_questions_in_lesson') }}: <span data-polyglot-debug-total-questions>{{ count($questions) }}</span>
        </p>
        @if(count($questions) > 0)
        <details class="mt-1">
            <summary class="cursor-pointer text-xs font-semibold" style="color: var(--muted);">{{ $label('questions_list') }}</summary>
            <ol class="mt-2 max-h-48 overflow-y-auto space-y-0.5 text-xs" style="color: var(--muted);">
                @foreach($questions as $q)
                    <li class="flex gap-2 py-0.5">
                        <span class="shrink-0 font-semibold" style="min-width: 1.5rem;">{{ data_get($q, 'position') }}.</span>
                        <span>{{ data_get($q, 'source_text_uk', 'n/a') }}</span>
                    </li>
                @endforeach
            </ol>
        </details>
        @endif
        <div class="mt-4 grid gap-3 text-sm">
            <label class="polyglot-debug-help grid gap-1" title="{{ $tooltip('required_answered_percent') }}">
                <span class="font-semibold">{{ $label('required_answered_percent') }}</span>
                <input type="number" min="0" max="100" class="rounded-[12px] border px-3 py-2" style="border-color: var(--line);" data-polyglot-debug-input="answeredPercent" value="100" title="{{ $tooltip('required_answered_percent') }}" aria-label="{{ $tooltip('required_answered_percent') }}">
                <span class="text-xs font-semibold" style="color: var(--muted);" data-polyglot-debug-preview="answeredPercent"></span>
            </label>
            <label class="polyglot-debug-help grid gap-1" title="{{ $tooltip('score_rating_percent') }}">
                <span class="font-semibold">{{ $label('score_rating_percent') }}</span>
                <input type="number" min="0" max="100" class="rounded-[12px] border px-3 py-2" style="border-color: var(--line);" data-polyglot-debug-input="ratingPercent" value="100" title="{{ $tooltip('score_rating_percent') }}" aria-label="{{ $tooltip('score_rating_percent') }}">
            </label>
            <label class="polyglot-debug-help grid gap-1" title="{{ $tooltip('required_correct_percent') }}">
                <span class="font-semibold">{{ $label('required_correct_percent') }}</span>
                <input type="number" min="0" max="100" class="rounded-[12px] border px-3 py-2" style="border-color: var(--line);" data-polyglot-debug-input="requiredCorrectPercent" value="100" title="{{ $tooltip('required_correct_percent') }}" aria-label="{{ $tooltip('required_correct_percent') }}">
                <span class="text-xs font-semibold" style="color: var(--muted);" data-polyglot-debug-preview="requiredCorrectPercent"></span>
            </label>
            <label class="polyglot-debug-help grid gap-1" title="{{ $tooltip('minimum_rating_percent') }}">
                <span class="font-semibold">{{ $label('minimum_rating_percent') }}</span>
                <input type="number" min="0" max="100" class="rounded-[12px] border px-3 py-2" style="border-color: var(--line);" data-polyglot-debug-input="minimumRatingPercent" value="90" title="{{ $tooltip('minimum_rating_percent') }}" aria-label="{{ $tooltip('minimum_rating_percent') }}">
            </label>
            <label class="polyglot-debug-help flex items-center gap-2" title="{{ $tooltip('force_unlock_next') }}">
                <input type="checkbox" data-polyglot-debug-input="forceUnlockNext" title="{{ $tooltip('force_unlock_next') }}" aria-label="{{ $tooltip('force_unlock_next') }}">
                <span>{{ $label('force_unlock_next') }}</span>
            </label>
            <div class="flex flex-wrap gap-2">
                <button type="button" class="rounded-full bg-ocean px-4 py-2 text-sm font-extrabold text-white" data-polyglot-debug-action="simulate-progress" title="{{ $tooltip('simulate_progress') }}" aria-label="{{ $tooltip('simulate_progress') }}">{{ $label('simulate_progress') }}</button>
                <button type="button" class="rounded-full bg-ocean px-4 py-2 text-sm font-extrabold text-white" data-polyglot-debug-action="mark-complete" title="{{ $tooltip('mark_complete') }}" aria-label="{{ $tooltip('mark_complete') }}">{{ $label('mark_complete') }}</button>
                <button type="button" class="rounded-full border px-4 py-2 text-sm font-bold" style="border-color: var(--line);" data-polyglot-debug-action="unlock-next" title="{{ $tooltip('unlock_next') }}" aria-label="{{ $tooltip('unlock_next') }}">{{ $label('unlock_next') }}</button>
                <button type="button" class="rounded-full border px-4 py-2 text-sm font-bold" style="border-color: var(--line);" data-polyglot-debug-action="apply-lesson-policy" title="{{ $tooltip('apply_lesson_policy') }}" aria-label="{{ $tooltip('apply_lesson_policy') }}">{{ $label('apply_lesson_policy') }}</button>
                <button type="button" class="rounded-full border px-4 py-2 text-sm font-bold" style="border-color: var(--line);" data-polyglot-debug-action="apply-course-policy" title="{{ $tooltip('apply_course_policy') }}" aria-label="{{ $tooltip('apply_course_policy') }}">{{ $label('apply_course_policy') }}</button>
                <button type="button" class="rounded-full border px-4 py-2 text-sm font-bold" style="border-color: var(--line);" data-polyglot-debug-action="clear-debug-overrides" title="{{ $tooltip('clear_debug_overrides') }}" aria-label="{{ $tooltip('clear_debug_overrides') }}">{{ $label('clear_debug_overrides') }}</button>
            </div>
        </div>
    </div>

    <div class="rounded-[18px] border p-4" style="border-color: var(--line);">
        <h3 class="text-sm font-extrabold">{{ $label('reset_tools') }}</h3>
        <div class="mt-4 grid gap-3 text-sm">
            <label class="polyglot-debug-help flex items-center gap-2" title="{{ $tooltip('reset_policy_with_lesson') }}">
                <input type="checkbox" data-polyglot-debug-input="clearPolicyOnReset" checked title="{{ $tooltip('reset_policy_with_lesson') }}" aria-label="{{ $tooltip('reset_policy_with_lesson') }}">
                <span>{{ $label('reset_policy_with_lesson') }}</span>
            </label>
            <label class="polyglot-debug-help flex items-center gap-2" title="{{ $tooltip('remove_next_progress') }}">
                <input type="checkbox" data-polyglot-debug-input="removeNextProgress" title="{{ $tooltip('remove_next_progress') }}" aria-label="{{ $tooltip('remove_next_progress') }}">
                <span>{{ $label('remove_next_progress') }}</span>
            </label>
            <label class="polyglot-debug-help flex items-center gap-2" title="{{ $tooltip('all_polyglot_courses') }}">
                <input type="checkbox" data-polyglot-debug-input="allPolyglotCourses" title="{{ $tooltip('all_polyglot_courses') }}" aria-label="{{ $tooltip('all_polyglot_courses') }}">
                <span>{{ $label('all_polyglot_courses') }}</span>
            </label>
            <div class="flex flex-wrap gap-2">
                <button type="button" class="rounded-full border px-4 py-2 text-sm font-bold" style="border-color: var(--line);" data-polyglot-debug-action="reset-current-lesson" title="{{ $tooltip('reset_current_lesson') }}" aria-label="{{ $tooltip('reset_current_lesson') }}">{{ $label('reset_current_lesson') }}</button>
                <button type="button" class="rounded-full border px-4 py-2 text-sm font-bold" style="border-color: var(--line);" data-polyglot-debug-action="reset-current-completion" title="{{ $tooltip('reset_current_completion') }}" aria-label="{{ $tooltip('reset_current_completion') }}">{{ $label('reset_current_completion') }}</button>
                <button type="button" class="rounded-full border px-4 py-2 text-sm font-bold" style="border-color: var(--line);" data-polyglot-debug-action="reset-next-unlock" title="{{ $tooltip('reset_next_unlock') }}" aria-label="{{ $tooltip('reset_next_unlock') }}">{{ $label('reset_next_unlock') }}</button>
                <button type="button" class="rounded-full border px-4 py-2 text-sm font-bold" style="border-color: var(--line);" data-polyglot-debug-action="reset-course-progress" title="{{ $tooltip('reset_course_progress') }}" aria-label="{{ $tooltip('reset_course_progress') }}">{{ $label('reset_course_progress') }}</button>
                <button type="button" class="rounded-full border px-4 py-2 text-sm font-bold" style="border-color: var(--line);" data-polyglot-debug-action="clear-debug-keys" title="{{ $tooltip('clear_debug_keys') }}" aria-label="{{ $tooltip('clear_debug_keys') }}">{{ $label('clear_all_debug_keys') }}</button>
                <button type="button" class="rounded-full border px-4 py-2 text-sm font-bold" style="border-color: var(--line);" data-polyglot-debug-action="refresh-state" title="{{ $tooltip('refresh_state') }}" aria-label="{{ $tooltip('refresh_state') }}">{{ $label('refresh_debug_state') }}</button>
            </div>
        </div>
    </div>
</section>
