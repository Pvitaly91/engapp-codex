const assert = require('node:assert/strict');
const vm = require('node:vm');

// Narrow answer/grade fixtures extract individual Step functions rather than
// booting the page. Supply the additional focus lifecycle scope from the same
// production script. The standalone autocomplete suite boots the full scope
// and tests actual scheduling/transport instead of the no-network stub here.
module.exports = function installStepManualAutocompleteScope(source, context) {
    const state = source.match(/^let (?:manualWordSearch|manualAnswerFocusTimer) = null;$/gm);
    if (!state) return; // Non-Step mode or the frozen pre-debounce source.

    assert.equal(state.length, 2);
    const focus = ['isCurrentManualAnswerInput', 'cancelManualAnswerFocus'].map(name => {
        const body = source.match(new RegExp(`^function ${name}\\([^\\n]*\\{[\\s\\S]*?^\\}`, 'm'))?.[0];
        assert.ok(body, `Missing Step lifecycle helper ${name}`);
        return body;
    });
    context.clearTimeout ??= () => {}; // The existing fixture timers execute callbacks synchronously.
    context.scheduleManualWordSearch ??= context.searchManualWords ?? (() => {});
    vm.runInContext([...state, ...focus].join('\n'), context);
};
