@once
<style>
    [data-test-suggestion-option][data-test-suggestion-active="true"] {
        background: #eef2ff !important;
        color: #312e81 !important;
        outline: 2px solid #818cf8;
        outline-offset: -2px;
    }
</style>

<script>
let ACTIVE_TEST_SUGGESTION_LIST = null;

function testSuggestionOptions(context = ACTIVE_TEST_SUGGESTION_LIST) {
    if (!context?.list || !context.optionSelector) {
        return [];
    }

    return Array.from(context.list.querySelectorAll(context.optionSelector));
}

function setActiveTestSuggestion(index) {
    const context = ACTIVE_TEST_SUGGESTION_LIST;
    const options = testSuggestionOptions(context);

    if (!context || options.length === 0) {
        return;
    }

    const safeIndex = ((Number(index) % options.length) + options.length) % options.length;
    context.activeIndex = safeIndex;

    options.forEach((option, optionIndex) => {
        const active = optionIndex === safeIndex;
        option.dataset.testSuggestionActive = active ? 'true' : 'false';
        option.setAttribute('aria-selected', active ? 'true' : 'false');
    });

    const activeOption = options[safeIndex];
    context.input.setAttribute('aria-activedescendant', activeOption.id);
    activeOption.scrollIntoView({ block: 'nearest' });
}

function deactivateTestSuggestionList(input = null, list = null, hide = false) {
    const context = ACTIVE_TEST_SUGGESTION_LIST;
    if (!context) {
        if (hide && list) {
            list.classList.add('hidden');
        }
        return;
    }

    if ((input && context.input !== input) || (list && context.list !== list)) {
        if (hide && list) {
            list.classList.add('hidden');
        }
        if (input) {
            input.setAttribute('aria-expanded', 'false');
            input.removeAttribute('aria-activedescendant');
        }
        return;
    }

    testSuggestionOptions(context).forEach((option) => {
        option.dataset.testSuggestionActive = 'false';
        option.setAttribute('aria-selected', 'false');
    });
    context.input.setAttribute('aria-expanded', 'false');
    context.input.removeAttribute('aria-activedescendant');
    if (hide) {
        context.list.classList.add('hidden');
    }
    ACTIVE_TEST_SUGGESTION_LIST = null;
}

function activateTestSuggestionList(input, list, optionSelector) {
    if (!input || !list || !optionSelector) {
        return;
    }

    const previous = ACTIVE_TEST_SUGGESTION_LIST;
    if (previous && (previous.input !== input || previous.list !== list)) {
        deactivateTestSuggestionList(previous.input, previous.list, true);
    }

    const options = Array.from(list.querySelectorAll(optionSelector));
    if (options.length === 0) {
        deactivateTestSuggestionList(input, list, true);
        return;
    }

    list.classList.remove('hidden');
    list.setAttribute('role', 'listbox');
    if (!list.id) {
        list.id = `test-suggestions-${Date.now()}-${Math.random().toString(36).slice(2)}`;
    }

    input.setAttribute('role', 'combobox');
    input.setAttribute('aria-autocomplete', 'list');
    input.setAttribute('aria-controls', list.id);
    input.setAttribute('aria-expanded', 'true');
    input.removeAttribute('aria-activedescendant');

    options.forEach((option, index) => {
        option.id = option.id || `${list.id}-option-${index}`;
        option.dataset.testSuggestionOption = 'true';
        option.dataset.testSuggestionActive = 'false';
        option.setAttribute('role', 'option');
        option.setAttribute('aria-selected', 'false');
    });

    ACTIVE_TEST_SUGGESTION_LIST = {
        input,
        list,
        optionSelector,
        activeIndex: -1,
    };
}

document.addEventListener('keydown', (event) => {
    const context = ACTIVE_TEST_SUGGESTION_LIST;
    if (!context || event.target !== context.input || event.isComposing) {
        return;
    }

    const options = testSuggestionOptions(context);
    if (options.length === 0) {
        deactivateTestSuggestionList(context.input, context.list, true);
        return;
    }

    if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
        event.preventDefault();
        event.stopImmediatePropagation();
        const direction = event.key === 'ArrowDown' ? 1 : -1;
        const nextIndex = context.activeIndex < 0
            ? (direction > 0 ? 0 : options.length - 1)
            : context.activeIndex + direction;
        setActiveTestSuggestion(nextIndex);
        return;
    }

    if (event.key === 'Enter' && context.activeIndex >= 0) {
        event.preventDefault();
        event.stopImmediatePropagation();
        const selected = options[context.activeIndex];
        deactivateTestSuggestionList(context.input, context.list, true);
        selected.click();
        return;
    }

    if (event.key === 'Escape' || event.key === 'Tab') {
        deactivateTestSuggestionList(context.input, context.list, true);
    }
}, true);

document.addEventListener('pointermove', (event) => {
    const context = ACTIVE_TEST_SUGGESTION_LIST;
    if (!context || !context.list.contains(event.target)) {
        return;
    }

    const option = event.target.closest(context.optionSelector);
    if (!option) {
        return;
    }

    const index = testSuggestionOptions(context).indexOf(option);
    if (index >= 0 && index !== context.activeIndex) {
        setActiveTestSuggestion(index);
    }
});

document.addEventListener('pointerdown', (event) => {
    const context = ACTIVE_TEST_SUGGESTION_LIST;
    if (!context) {
        return;
    }

    if (context.list.contains(event.target)) {
        const option = event.target.closest(context.optionSelector);
        if (option) {
            deactivateTestSuggestionList(context.input, context.list);
        }
        return;
    }

    if (event.target !== context.input) {
        deactivateTestSuggestionList(context.input, context.list, true);
    }
}, true);
</script>
@endonce
