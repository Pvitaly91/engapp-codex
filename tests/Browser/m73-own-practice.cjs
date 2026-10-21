'use strict';
// M73 authored practice in the unchanged native Alpine factory, NOT browser acceptance.
// Pure Node VM: no app bootstrap, .env, SQL, HTTP, progress, receipts or file writes.
// Default: frozen public package. Draft check: --projection ABSOLUTE_PATH --sha256 SHA256.
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const assert = require('node:assert/strict');
const crypto = require('node:crypto');
const root = path.resolve(__dirname, '../..');
const read = p => fs.readFileSync(p, 'utf8');
const digest = bytes => crypto.createHash('sha256').update(bytes).digest('hex');
const hash = p => digest(fs.readFileSync(p));
const tracked = new Map();
const track = p => { const sha = hash(p); tracked.set(p, sha); return sha; };
const args = process.argv.slice(2);
let mode, master, before, masterSha, beforeSha, projectionSha = null;
if (args.length) {
    assert.equal(args.length, 4, 'draft execution needs an explicit projection path AND raw SHA-256');
    assert.equal(args[0], '--projection'); assert.equal(args[2], '--sha256');
    assert.ok(path.isAbsolute(args[1])); assert.match(args[3], /^[a-f0-9]{64}$/);
    const projectionPath = path.resolve(args[1]);
    projectionSha = track(projectionPath);
    assert.equal(projectionSha, args[3], 'private projection raw-byte binding, never a frozen-package claim');
    const projection = JSON.parse(read(projectionPath));
    assert.equal(projection.status, 'private-draft-not-frozen');
    assert.equal(projection.master.status, 'private-draft-not-frozen');
    assert.equal(projection.own_graded_tasks, 25);
    master = projection.master; before = projection.source_before;
    masterSha = digest(Buffer.from(JSON.stringify(master), 'utf8'));
    beforeSha = digest(Buffer.from(JSON.stringify(before), 'utf8'));
    mode = 'explicit private projection; not frozen and not browser acceptance';
} else {
    const packagePath = path.join(root, 'app/Support/M73GenericIrregularCollective.php');
    track(packagePath);
    const packageSource = read(packagePath);
    const pin = name => {
        const match = packageSource.match(new RegExp("public const " + name + " = '([a-f0-9]{64})';"));
        assert.ok(match, 'frozen package publishes raw SHA-256 for ' + name);
        assert.notEqual(match[1], '0'.repeat(64), 'release must be frozen before default execution');
        return match[1];
    };
    const manifestPath = path.join(root, 'docs/content/m73-source-checksums.v1.json');
    assert.equal(track(manifestPath), pin('CHECKSUMS_SHA'), 'frozen manifest raw binding');
    const manifest = JSON.parse(read(manifestPath));
    assert.equal(manifest.status, 'frozen');
    assert.equal(manifest.package, 'm73-generic-irregular-collective');
    const masterPath = path.join(root, 'docs/content/m73-generic-irregular-collective.v1.0.0.json');
    const beforePath = path.join(root, 'docs/content/m73-before-source.v1.json');
    masterSha = track(masterPath); beforeSha = track(beforePath);
    assert.equal(masterSha, pin('MASTER_SHA')); assert.equal(beforeSha, pin('BEFORE_SHA'));
    for (const relative of ['docs/content/m73-generic-irregular-collective.v1.0.0.json', 'docs/content/m73-before-source.v1.json']) {
        const entry = manifest.artifacts.find(a => a.path === relative);
        assert.ok(entry, 'frozen artifact manifest entry');
        const absolute = path.join(root, relative);
        assert.equal(hash(absolute), entry.sha256, 'manifest raw-byte binding');
        assert.equal(fs.statSync(absolute).size, entry.bytes);
    }
    master = JSON.parse(read(masterPath)); before = JSON.parse(read(beforePath));
    assert.equal(master.status, 'frozen');
    mode = 'frozen public package; not browser acceptance';
}
assert.equal(master.base_sha, 'cb55b329827e52a56cb6d41edb21e70768cf86f4');
assert.equal(before.base_sha, master.base_sha);
assert.equal(before.content_type, 'M73_source_before');
assert.equal(master.lessons.length, 3); assert.equal(before.targets.length, 3);

const sourcePath = path.join(root, 'resources/views/engram/theory/blocks-v3/practice-set.blade.php');
const sourceSha = track(sourcePath);
assert.equal(sourceSha, '05103e6d6261298314508394447c00f133e92c100b03842c1527700920e7bd2e', 'unchanged accepted native factory');
const scripts = [...read(sourcePath).matchAll(/<script\b[^>]*>([\s\S]*?)<\/script>/g)]
    .map(m => m[1]).filter(s => /Alpine\.data\(\s*['"]theoryPracticeSet['"]/.test(s));
assert.equal(scripts.length, 1);
assert.doesNotMatch(scripts[0], /@(?:php|json|js|endphp)\b|\{\{!!?/);
const answerVariantsPath = path.join(root, 'tests/Browser/load-answer-variants.cjs');
const answerVariantsSha = track(answerVariantsPath);
let factory, listener, networkCalls = 0;
const context = vm.createContext({
    window: {EnglishAnswerVariants: require(answerVariantsPath)},
    document: {
        addEventListener(name, callback) { assert.equal(name, 'alpine:init'); listener = callback; },
        querySelector() { return {focus() {}}; },
    },
    Alpine: {data(name, callback) { assert.equal(name, 'theoryPracticeSet'); factory = callback; }},
    fetch() { networkCalls++; throw Error('Network forbidden'); },
    URL,
});
vm.runInContext(scripts[0], context, {filename: sourcePath, timeout: 1000});
assert.equal(typeof listener, 'function'); listener();
assert.equal(typeof factory, 'function');
const make = data => {
    const model = factory({...data, i18n: {empty:'Введи відповідь.', correct:'Правильно.',
        incorrect:'Неправильно.', answer:'Правильна відповідь', score:'Результат: :correct / :total'}});
    model.$nextTick = callback => callback(); model.init(); return model;
};
const groups = ['selects', 'choices', 'inputs', 'rephrase'];
const answerProps = {selects:'selectAnswers', choices:'choiceAnswers', inputs:'inputAnswers', rephrase:'rephraseAnswers'};
// Independent canonical expectations. Do not derive expected answers from the authored master.
const expected = [
    {
        identity:'Database\\Seeders\\Page_V3\\NounsArticlesQuantity\\NounsArticlesQuantityAdvancedArticlesGenericReferenceTheorySeeder',
        continuing:13, slot:11, appended:0, new_tasks:4,
        selects:['Children','The rich','A computer','The Japanese'], choices:['A','B'],
        inputs:['The','The','Без артикля','Digital dictionaries do not replace every printed dictionary.',
            'The Italians in our group have chosen the same topic.'],
        rephrase:[null,'Manuals can be useful.','Ukrainians can apply for this programme.'],
        token_inputs:[3,4],
    },
    {
        identity:'Database\\Seeders\\Page_V3\\NounsArticlesQuantity\\NounsArticlesQuantityIrregularPluralsTheorySeeder',
        continuing:7, slot:7, appended:1, new_tasks:6,
        selects:['children','are'], choices:['A','B'],
        inputs:['These children are not hungry.','Were those women here yesterday?'], rephrase:[], token_inputs:[0,1],
    },
    {
        identity:'Database\\Seeders\\Page_V3\\NounsArticlesQuantity\\NounsArticlesQuantityCollectiveNounsTheorySeeder',
        continuing:6, slot:6, appended:1, new_tasks:6,
        selects:['has','are'], choices:['A','B'],
        inputs:['These families have separate tickets.','The team are packing their bags.'], rephrase:[], token_inputs:[0,1],
    },
];
const beforeDefects = [];
const oldGeneric = JSON.parse(before.targets[0].before.page.blocks[11].body);
assert.deepEqual(oldGeneric.options, ['Перший варіант','Другий варіант']);
{
    const model = make(oldGeneric);
    let visibleCorrect = 0;
    for (let i = 0; i < 4; i++) {
        for (const option of oldGeneric.options) {
            model.selectAnswers[i] = option; model.check('selects');
            visibleCorrect += Number(model.isCorrect('selects', i));
            assert.equal(model.isCorrect('selects', i), false, 'old visible ordinal cannot satisfy the word key');
        }
        model.selectAnswers[i] = oldGeneric.selects[i].answer;
        assert.equal(model.isCorrect('selects', i), true, 'old canonical itself is accepted');
        beforeDefects.push({lesson:0, group:'selects', index:i, defect:'visible ordinal option differs from canonical word key'});
    }
    assert.equal(visibleCorrect, 0);
    assert.equal(oldGeneric.inputs[2].answer, '');
    assert.equal(model.hasAnswer('inputs', 2), false, 'empty authored key is excluded, never a free correct point');
    for (const blank of ['', ' ', '\t', ' \n ']) {
        model.inputAnswers[2] = blank; model.check('inputs');
        assert.equal(model.isCorrect('inputs', 2), false);
        assert.equal(model.scoreText('inputs'), 'Результат: 0 / 2', 'old state is 0/2, not an awarded empty answer');
    }
    beforeDefects.push({lesson:0, group:'inputs', index:2, defect:'empty authored zero key excluded from grading'});
}
for (const li of [1,2]) {
    assert.equal(before.targets[li].before.page.blocks.filter(b => b.type === 'practice-set').length, 0,
        'fresh source has no existing native practice; append without inventing an old block');
}

function plainFeedback(value) {
    assert.equal(typeof value, 'string');
    assert.match(value, /[\u0400-\u04ff]/, 'feedback contains Ukrainian');
    assert.doesNotMatch(value, /<\/?[a-z]|&(?:#[0-9]+|#x[0-9a-f]+|[a-z][a-z0-9]+);/iu, 'x-text feedback must be plain');
}
const unicodeAlias = value => value.replace(/[A-Za-z]/g, c => String.fromCharCode(c.charCodeAt(0) + 0xfee0))
    .replace(/'/g, '\u2019').replace(/ /g, '\u00a0').normalize('NFD');
function assemble(model, index, key, partialProbe = false) {
    const remaining = [...model.inputTokenBank(index)], words = key.split(/\s+/);
    assert.equal(new Set(remaining.map(t => t.id)).size, remaining.length, 'unique token-instance IDs');
    assert.ok(remaining.length > 1);
    for (const [position, word] of words.entries()) {
        const at = remaining.findIndex(token => token.value === word);
        assert.notEqual(at, -1, 'exact case/multiplicity token for ' + word);
        const token = remaining.splice(at, 1)[0];
        model.appendInputToken('inputs', index, token);
        const current = model.inputAnswers[index];
        model.appendInputToken('inputs', index, token);
        assert.equal(model.inputAnswers[index], current, 'used instance cannot be appended twice');
        if (partialProbe && position < words.length - 1) {
            model.check('inputs'); assert.equal(model.isCorrect('inputs', index), false, 'partial sentence is not complete');
        }
    }
    assert.equal(model.inputAnswers[index], key); assert.equal(model.isCorrect('inputs', index), true);
}
let taskCount = 0, optionCount = 0, acceptedCount = 0, demos = 0, retained = 0, addedTasks = 0;
let fullSentenceManualPaths = 0, fullSentenceTokenPaths = 0;
const newIds = new Set(), lessonResults = [], records = [];
for (const [li, e] of expected.entries()) {
    const lesson = master.lessons[li], old = before.targets[li].before;
    assert.equal(before.targets[li].identity, e.identity); assert.equal(old.seeder.class, e.identity);
    assert.equal(lesson.identity, e.identity); assert.equal(lesson.locale, 'uk');
    assert.equal(old.page.blocks.length, e.continuing);
    assert.equal(lesson.blocks.length, e.continuing + e.appended);
    for (const [i, block] of old.page.blocks.entries()) {
        assert.equal(lesson.blocks[i].type, block.type); assert.equal(lesson.blocks[i].column, block.column);
        assert.equal(lesson.blocks[i].existing_source_index, i, 'all old configs keep their source index');
        // Author projection spells an absent source level as null; definition preservation
        // is separately checked against raw BEFORE/AFTER by M73AuthoredReleaseTest.
        assert.equal(lesson.blocks[i].level ?? null, block.level ?? null, 'continuing SQL filter semantics remain exact');
    }
    assert.equal(lesson.blocks[e.slot].type, 'practice-set');
    assert.equal(lesson.blocks.filter(b => b.type === 'practice-set').length, 1);
    assert.equal(lesson.blocks[e.slot].column, 'left');
    assert.equal(lesson.blocks[e.slot].level ?? null, [null,'A2','B1'][li]);
    const data = lesson.blocks[e.slot].data, activeGroups = groups.filter(g => e[g].length > 0);
    for (const g of groups) assert.equal((data[g] || []).length, e[g].length);
    assert.deepEqual(data.inputs.flatMap((item, i) => typeof item.before === 'string' && item.before.includes('/') ? [i] : []), e.token_inputs);
    if (li === 0) {
        for (const [g, count] of [['selects',4],['inputs',3],['rephrase',3]]) {
            for (let i = 0; i < count; i++) {
                assert.equal(Object.hasOwn(data[g][i], 'id'), false, 'retained Generic ID absence');
                assert.equal(Object.hasOwn(data[g][i], 'source_index'), Object.hasOwn(oldGeneric[g][i], 'source_index'));
            }
        }
        assert.ok(data.rephrase[0].example_original); assert.ok(data.rephrase[0].example_target);
        assert.equal(Object.hasOwn(data.rephrase[0], 'answer'), false, 'demo remains ungraded');
    } else {
        const newTasks = [...data.selects, ...data.choices, ...data.inputs];
        assert.deepEqual(newTasks.map(item => item.source_index), [1,2,3,4,5,6]);
        assert.equal(lesson.blocks[e.slot].existing_source_index, null, 'new practice is not an old source row');
    }
    for (const inputIndex of e.token_inputs) {
        const input = data.inputs[inputIndex];
        assert.equal(typeof input.after, 'string'); assert.match(input.after, /[\u0400-\u04ff]/, 'supported visible Ukrainian condition');
        assert.ok(!input.after.includes(input.answer), 'condition does not reveal the whole English answer');
        assert.doesNotMatch(input.answer, /Ø|Без артикля|[\u0400-\u04ff]/, 'full English sentence, not a zero-label token');
    }
    for (const g of groups) {
        const items = data[g] || [], total = e[g].filter(k => k !== null).length;
        assert.equal(make(data).scoreText(g), 'Результат: 0 / ' + total);
        for (const [i, item] of items.entries()) {
            const key = e[g][i], prop = answerProps[g];
            if (key === null) { demos++; assert.equal(make(data).hasAnswer(g, i), false); continue; }
            taskCount++;
            const isRetained = li === 0 && g !== 'choices' && (g !== 'inputs' || i < 3);
            if (isRetained) retained++;
            else { assert.match(item.id, /^m73-[a-z0-9-]+$/); assert.ok(!newIds.has(item.id)); newIds.add(item.id); addedTasks++; }
            assert.equal(item.answer, key, 'independent canonical ' + li + '/' + g + '/' + i);
            assert.ok(Array.isArray(item.accepted) && item.accepted.includes(key));
            assert.ok(item.accepted.every(a => typeof a === 'string' && a.trim() !== ''));
            const model = make(data), record = {lesson:li, group:g, index:i, canonical:key, visible_options:[]};
            for (const blank of ['', ' ', '\t', ' \n ', '\u00a0']) {
                model[prop][i] = blank; model.check(g);
                assert.equal(model.isCorrect(g, i), false); assert.equal(model.feedbackText(g, i), 'Введи відповідь.');
                assert.equal(model.scoreText(g), 'Результат: 0 / ' + total);
            }
            model[prop][i] = 'zzzz wrong'; model.check(g); assert.equal(model.isCorrect(g, i), false);
            const wrongScore = model.scoreText(g); model.check(g); assert.equal(model.scoreText(g), wrongScore);
            plainFeedback(model.feedbackText(g, i));
            for (const alias of item.accepted) {
                assert.equal(typeof item.feedback[alias.trim().toLowerCase()], 'string', 'authored raw alias feedback exists');
                for (const variant of [alias, '  ' + alias.toUpperCase() + '  ', alias.replace(/[.!?]+$/, '') + '!!!', unicodeAlias(alias)]) {
                    model[prop][i] = variant; model.check(g);
                    assert.equal(model.isCorrect(g, i), true, 'accepted/case/punctuation/Unicode alias ' + variant);
                    plainFeedback(model.feedbackText(g, i));
                }
                acceptedCount++;
            }
            for (const feedback of Object.values(item.feedback || {})) plainFeedback(feedback);
            if (g === 'selects' || g === 'choices') {
                const options = item.options || (g === 'selects' ? data.options : data.choice_options) || [];
                assert.ok(options.length >= 2); assert.ok(options.includes(key));
                assert.equal(new Set(options.map(o => model.normalize(o))).size, options.length, 'no normalization collision');
                assert.ok(options.every(o => model.normalize(o) !== ''));
                let correctOptions = 0;
                for (const option of options) {
                    model[prop][i] = option; model.check(g);
                    const correct = item.accepted.some(a => model.normalize(a) === model.normalize(option));
                    assert.equal(model.isCorrect(g, i), correct, 'all actual visible options/distractors');
                    assert.equal(typeof item.feedback[option.trim().toLowerCase()], 'string', 'literal option feedback lookup');
                    plainFeedback(model.feedbackText(g, i));
                    correctOptions += Number(correct); optionCount++; record.visible_options.push({value:option, correct});
                }
                assert.equal(correctOptions, 1);
            }
            if (g === 'inputs' && e.token_inputs.includes(i)) {
                model.resetGroup(g);
                const otherBanks = data.inputs.map((_, n) => n === i ? null : JSON.stringify(model.inputTokenBank(n)));
                assemble(model, i, key, true); fullSentenceTokenPaths++;
                for (let n = 0; n < data.inputs.length; n++) if (n !== i) {
                    assert.equal(JSON.stringify(model.inputTokenBank(n)), otherBanks[n], 'another task bank stays independent');
                    assert.equal(model.inputAnswers[n], undefined, 'another manual field remains untouched');
                }
                assert.equal(model.inputTokenBank(i).filter(t => !t.used).length, 0, 'full authored bank consumed');
                const words = key.split(/\s+/);
                model.inputAnswers[i] = words.slice(0, -1).join(' '); model.syncInputTokenBank(i); model.check(g);
                assert.equal(model.isCorrect(g, i), false);
                const returned = model.inputTokenBank(i).find(t => !t.used && t.value === words.at(-1));
                assert.ok(returned, 'deleted final token returns to bank');
                model.appendInputToken('inputs', i, returned);
                assert.equal(model.inputAnswers[i], key); assert.equal(model.isCorrect(g, i), true);
                const tokenScore = model.scoreText(g); model.check(g); model.check(g); assert.equal(model.scoreText(g), tokenScore);
                model.resetGroup(g); model.inputAnswers[i] = key; model.syncInputTokenBank(i); model.check(g);
                assert.equal(model.isCorrect(g, i), true, 'full sentence manually typed');
                assert.equal(model.inputTokenBank(i).filter(t => !t.used).length, 0, 'manual answer syncs native bank');
                fullSentenceManualPaths++;
            }
            model[prop][i] = key; model.check(g); assert.equal(model.isCorrect(g, i), true);
            const correctScore = model.scoreText(g); model.check(g); assert.equal(model.scoreText(g), correctScore);
            assert.equal(correctScore, 'Результат: 1 / ' + total, 'correct edit counted once');
            model.resetGroup(g); assert.equal(model.scoreText(g), 'Результат: 0 / ' + total);
            model.check(g); assert.equal(model.isCorrect(g, i), false); assert.equal(model.scoreText(g), 'Результат: 0 / ' + total);
            records.push(record);
        }
    }
    const mixed = make(data);
    for (const g of activeGroups) {
        const indices = e[g].flatMap((key, i) => key === null ? [] : [i]);
        mixed[answerProps[g]][indices[0]] = 'zzzz wrong'; mixed[answerProps[g]][indices[1]] = e[g][indices[1]];
        mixed.check(g); assert.equal(mixed.scoreText(g), 'Результат: 1 / ' + indices.length, 'mixed group result');
    }
    mixed.resetGroup('inputs'); mixed.check('inputs');
    assert.equal(mixed.scoreText('inputs'), 'Результат: 0 / ' + e.inputs.length);
    for (const g of activeGroups.filter(g => g !== 'inputs')) {
        assert.equal(mixed.scoreText(g), 'Результат: 1 / ' + e[g].filter(key => key !== null).length);
    }
    const full = make(data);
    for (const g of activeGroups) {
        for (const [i, key] of e[g].entries()) if (key !== null) full[answerProps[g]][i] = key;
        full.check(g); const total = e[g].filter(key => key !== null).length;
        assert.equal(full.scoreText(g), 'Результат: ' + total + ' / ' + total);
    }
    for (const g of activeGroups) {
        const others = activeGroups.filter(x => x !== g).map(x => [x, full.scoreText(x)]);
        full.resetGroup(g); full.check(g);
        assert.equal(full.scoreText(g), 'Результат: 0 / ' + e[g].filter(key => key !== null).length);
        for (const [other, score] of others) assert.equal(full.scoreText(other), score, 'group reset independence');
    }
    lessonResults.push({identity:e.identity, continuing_slots:e.continuing, appended_practices:e.appended,
        practice_source_index:e.slot, own_graded_tasks:e[groups[0]].length + e.choices.length + e.inputs.length + e.rephrase.filter(k => k !== null).length,
        new_tasks:e.new_tasks, full_sentence_token_indices:e.token_inputs});
}

// Explicit zero is a supported nonempty label; blank or a display glyph is not an answer.
{
    const data = master.lessons[0].blocks[11].data, model = make(data);
    assert.equal(data.inputs[2].answer, 'Без артикля'); assert.ok(model.hasAnswer('inputs', 2));
    model.inputAnswers[2] = 'Без артикля'; model.check('inputs'); assert.equal(model.isCorrect('inputs', 2), true);
    for (const value of ['', ' ', '\t', 'Ø', 'Ø (без артикля)']) {
        model.inputAnswers[2] = value; model.check('inputs'); assert.equal(model.isCorrect('inputs', 2), false);
    }
    model.resetGroup('inputs'); model.check('inputs'); assert.equal(model.isCorrect('inputs', 2), false);
    assert.equal(model.scoreText('inputs'), 'Результат: 0 / 5');
}
// These are two explicitly permitted models for THIS task, not a ban on contextual notional agreement.
{
    const data = master.lessons[2].blocks[6].data, item = data.inputs[1], model = make(data);
    assert.equal(item.answer, 'The team are packing their bags.');
    assert.deepEqual(item.accepted, ['The team are packing their bags.','The team is packing its bags.']);
    assert.match(item.after, /are/); assert.match(item.after, /their/); assert.match(item.after, /is/); assert.match(item.after, /its/);
    for (const key of item.accepted) { model.inputAnswers[1] = key; model.check('inputs'); assert.equal(model.isCorrect('inputs', 1), true); }
    model.inputAnswers[1] = 'The team is packing their bags.'; model.check('inputs');
    assert.equal(model.isCorrect('inputs', 1), false, 'mixed model does not satisfy this explicitly bounded task');
    const feedback = model.feedbackText('inputs', 1); plainFeedback(feedback);
    assert.match(feedback, /у цій вправі|у цій|у цьому завданні/iu); assert.match(feedback, /можлив/iu, 'feedback does not claim a universal grammar prohibition');
}
// A reviewed contraction is accepted manually even though the authored bank contains the full form.
{
    const data = master.lessons[1].blocks[7].data, model = make(data);
    assert.ok(data.inputs[0].accepted.includes("These children aren't hungry."));
    model.inputAnswers[0] = "These children aren't hungry."; model.check('inputs');
    assert.equal(model.isCorrect('inputs', 0), true); plainFeedback(model.feedbackText('inputs', 0));
}
// Independent multiplicity regression probe; it is NOT a twenty-sixth authored M73 task.
{
    const data = {inputs:[{before:'a / book / a / I / pen. / and / bought / an',
        answer:'I bought a book and a pen.', accepted:['I bought a book and a pen.']}]};
    const model = make(data), articles = model.inputTokenBank(0).filter(t => t.value === 'a');
    assert.equal(articles.length, 2); assert.notEqual(articles[0].id, articles[1].id);
    assemble(model, 0, data.inputs[0].answer, true);
    assert.equal(model.inputTokenBank(0).filter(t => t.value === 'a' && t.used).length, 2);
    model.inputAnswers[0] = 'I bought a book and pen.'; model.syncInputTokenBank(0);
    assert.equal(model.isCorrect('inputs', 0), false);
    assert.equal(model.inputTokenBank(0).filter(t => t.value === 'a' && !t.used).length, 1);
    model.resetGroup('inputs'); assert.equal(model.inputTokenBank(0).filter(t => t.value === 'a' && !t.used).length, 2);
    model.check('inputs'); assert.equal(model.isCorrect('inputs', 0), false);
}
assert.equal(taskCount, 25); assert.equal(demos, 1); assert.equal(retained, 9); assert.equal(addedTasks, 16);
assert.equal(newIds.size, 16); assert.equal(optionCount, 32); assert.equal(acceptedCount, 27);
assert.equal(fullSentenceManualPaths, 6); assert.equal(fullSentenceTokenPaths, 6); assert.equal(beforeDefects.length, 5);
assert.equal(networkCalls, 0);
for (const [file, sha] of tracked) assert.equal(hash(file), sha, 'all read source bytes remain unchanged: ' + file);
const receipt = {
    schema_version:1, kind:'isolated unchanged native factory; NOT live browser acceptance', mode,
    frozen:args.length === 0, private_projection_sha256:projectionSha,
    master_sha256:masterSha, before_source_sha256:beforeSha, factory_sha256:sourceSha,
    answer_variant_loader_sha256:answerVariantsSha, lesson_results:lessonResults,
    totals:{continuing_slots:26, new_practice_inserts:2, graded_own_tasks:taskCount, retained_generic_functions:retained,
        ungraded_demos:demos, new_own_tasks:addedTasks, visible_options:optionCount, accepted_values:acceptedCount,
        full_sentence_manual_paths:fullSentenceManualPaths, full_sentence_token_paths:fullSentenceTokenPaths,
        separate_synthetic_multiplicity_probes:1, before_defect_records:beforeDefects.length},
    records, before_defects:beforeDefects, deliberate_zero_and_empty_regression:true,
    collective_task_bound_aliases:true, mixed_scores_and_group_reset_independence:true,
    network_calls:networkCalls, db_writes:0, file_writes:0,
    browser_visibility:'not_checked_by_this_script', browser_interaction:'not_checked_by_this_script',
};
console.log(JSON.stringify({mode:receipt.mode, frozen:receipt.frozen, private_projection_sha256:projectionSha,
    master_sha256:masterSha, ...receipt.totals, network_calls:networkCalls, db_writes:0, file_writes:0}));
